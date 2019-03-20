<?php
/**
 * Simplicity (https://github.com/madskullcreations/simplicity)
 * Copyright (c) Madskull Creations (https://madskullcreations.com)
 * 
 * Licensed under the MIT license.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * 
 */

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Form\SetupSimplicityForm;
use Cake\Utility\Security;

use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Auth\DefaultPasswordHasher;

/**
 * Installer controller
 *
 * NOTE: It is not inheriting AppController, which expects a database connection we do not yet have.
 * 
 */
class InstallerController extends Controller
{
	public function initialize()
	{
		parent::initialize();
    
    $this->loadComponent('Flash');
  }
  
  /**
   * Let user setup database connection details and create an admin-user.
   * 
   * User are redirected here when there are no database connection or no admin-user.
   * 
   */
	public function index()
	{
    $showResetTablesSetting = false;
    
    $simplicity_setup_state = Configure::read('simplicity_setup_state');
    // debug($simplicity_setup_state);
    
    $this->set('simplicity_setup_state', $simplicity_setup_state);
    
    // Using a model-less form give easy access to the Validator. (https://book.cakephp.org/3.0/en/core-libraries/form.html)
    $form = new SetupSimplicityForm();
    
		if($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
     
      if($form->execute($this->request->getData())) 
      {
        // Values seem correct. Check if database connection details are correct.
        $data = $this->request->data;

        // TODO: User should be able to select advanced settings: port, encoding, timezone, host, and also other databases than MySql.
        // 
        $connDetails = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            'port' => '3306',
            'username' => $data['db_username'],
            'password' => $data['db_password'],
            'database' => $data['db_database'],
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ];
        
        ConnectionManager::setConfig('knaster', $connDetails);
        $conn = ConnectionManager::get('knaster');
        // debug($conn);
        
        $connected = false;
        try
        {
          $connected = $conn->connect();
          if($connected == false)
          {
            $this->Flash->error(__('Unable to connect to the database. Please check connection details.'));
          }
          
          // The log ends up in /logs/queries.log, useful for finding any problems.
          $conn->logQueries(true);
        }
        catch(\Exception $connectionError) 
        {
          // The \Exception has something to do with namespaces. 
          // Effectively disables cake's default error handling and let me catch the exception here.
          // https://stackoverflow.com/questions/30995594/how-do-i-throw-a-custom-try-catch-exception-on-cakephp
          // 
          $attr = $connectionError->getAttributes();
          // debug($connectionError);
          // debug($attr["reason"]);
          // debug($connectionError->message);
          
          $this->Flash->error(__('Database reported the following error: ').$attr["reason"]);

          // NOTE: Same error message for different errors: it make no difference between wrong database name or wrong database user name.
          // $this->Flash->error(__('It seems like the database user name or password are wrong.'));
          $this->Flash->error(__('Please check the error message and correct any errors.'));
        }
        
        if($connected)
        {
          // Yo. A database connection could be established. 
          $res = array();
          
          if(isset($data['db_recreate_tables']) && $data['db_recreate_tables'] == 1)
          {
            // Simply delete tables.
            $this->dropTables($conn, $res);
          }
          
          // Save the settings, and create a user.
          $this->setupInstallation($data, $conn, $res);
          // debug($res);
          
          $anyErrors = false;
          foreach($res as $msg)
          {
            if(!$msg['result'])
            {
              if(!$anyErrors)
              {
                $this->Flash->error(__('One or more errors happened. Please check them out.'));
              }
              
              $anyErrors = true;
              
              if(strpos($msg['message'], "SQLSTATE[42S01]") !== false)
              {
                $showResetTablesSetting = true;
              }
              
              $this->Flash->error($msg['message']);
            }
          }
          
          if($anyErrors == false)
          {
            // The installation went well. 
            $this->Flash->success(__('Installation setup complete!'));
            
            foreach($res as $msg)
            {
              $this->Flash->success($msg['message']);
            }
                        
            // Funkar. Om du fyller i rätt databasuppgifter så ansluter den.
            // $users = TableRegistry::get('User', ['table' => 'users', 'connection' => $conn]);
            // debug($users);
            
            // $all = $users->find();
            // debug($all);
            
            // Redirect user to the congrats-page.
            return $this->redirect(['controller' => 'installer', 'action' => 'success']);
          }
          else
          {
            // One or more errors happened during setup. Reset the app.php file to enforce system to stay here.
            $rootDir = dirname(dirname(__DIR__));
            $res = array();
            $this->createAppConfig($rootDir, $res, true);
          }
        }
      } 
      else 
      {
        $this->Flash->error(__('There was a problem submitting your form. Please check the error message below each input field.'));
      }
    }
    
    $this->set('showResetTablesSetting', $showResetTablesSetting);
    $this->set('form', $form);
        
    $this->viewBuilder()->layout('installer');
	}

  /**
   * A welcome to the rest of your life page, reassuring the user that the setup are done.
   */
  public function success()
  {
    $kitchenSink = TableRegistry::get('KitchenSink');
    $kitchenSink->Store('SimplicitySetupState', '3');
    
    $this->viewBuilder()->layout('installer');
  }
  
  /**
   * Tries to set folder permissions, store database connection details, create security salt, 
   * create tables necessary for Simplicity, and finally create the administrator account.
   * 
   * @param string $res - the result of the operations. Format: array(array('result','message'),array('result','message'), ..)
   * 
   */
  protected function setupInstallation($data, $connection, &$res)
  {
    // We are currently in src/Controller folder, but need the root folder of the project.
    $rootDir = dirname(dirname(__DIR__));
    
    // Each step might succeed or fail, the total result are stored in $res.
    // Might mean that the database connection are stored, but setting the folder permissions failed. 
    // This is almost always good because we probably end up with a working installation even if some steps fail.
    
    $this->setFolderPermissions($rootDir, $res);
    
    // NOTE: Cake ask for this file during it's primary steps, so we can't create it here. Instead recreate it from app.default.php.
    // 
    // Create app.conf file. (Restore it if it already exist.)
    $this->createAppConfig($rootDir, $res, false);
    
    $this->storeDatabaseConnection($rootDir, $data['db_database'], $data['db_username'], $data['db_password'], $res);
    
    $newKey = hash('sha256', Security::randomBytes(64));
    $this->setSecuritySaltInApp($rootDir, $newKey, $res);
    
    // Create tables necessary for Simplicity.
    $this->createTables($connection, $res);
    
    // Create administrator account.
    $users = TableRegistry::get('Trolls', ['table' => 'users', 'connection' => $connection]);
    $user = $users->newEntity();
    
    $user->username = $data['user_email'];
    $user->password = (new DefaultPasswordHasher)->hash($data['user_password']);
    $user->role = 'admin';
    $user->created = date('Y-m-d H:i:s', time());
    $user->modified = $user->created;

    if($users->save($user) == false) 
    {
      $res[] = array('result' => false, 'message' => __('Could not create the user account.'));
    }
  }
  
  /**
   * Drop Simplicity tables.
   */
  protected function dropTables($connection, &$res)
  {
    try
    {
      $users = TableRegistry::get('Users');
      $users->DropTable($connection);
      
      $categories = TableRegistry::get('Categories');
      $categories->DropTable($connection);

      $catLang = TableRegistry::get('CatLang');
      $catLang->DropTable($connection);
      
      $kitchensink = TableRegistry::get('KitchenSink');
      $kitchensink->DropTable($connection);

      $languages = TableRegistry::get('Languages');
      $languages->DropTable($connection);

      // $rte = TableRegistry::get('RichTextElements');
      // $rte->DropTable($connection);
    }
    catch(\PDOException $ex) 
    {
      $msg = $ex->getMessage();
      
      $res[] = array('result' => false, 'message' => __('Dropping of database tables failed with the following error: ').$msg);
      return;
    }
    
    $res[] = array('result' => true, 'message' => __('Database tables dropped.'));
  }
  
  /**
   * Create necessary tables for Simplicity.
   */
  protected function createTables($connection, &$res)
  {
    try
    {
      // Disabled since we can't use database for session control during installation. 
      // $sessions = TableRegistry::get('SimplicitySessions');
      // $sessions->CreateTable($connection);

      $users = TableRegistry::get('Users');
      $users->CreateTable($connection);
      
      $categories = TableRegistry::get('Categories');
      $categories->CreateTable($connection);

      $catLang = TableRegistry::get('CatLang');
      $catLang->CreateTable($connection);
      
      $kitchensink = TableRegistry::get('KitchenSink');
      $kitchensink->CreateTable($connection);

      $languages = TableRegistry::get('Languages');
      $languages->CreateTable($connection);

      // $rte = TableRegistry::get('RichTextElements');
      // $rte->CreateTable($connection);
    }
    catch(\PDOException $ex) 
    {
      $msg = $ex->getMessage();
      
      $res[] = array('result' => false, 'message' => __('Creation of database tables failed with the following error: ').$msg);
      return;
    }
    
    $res[] = array('result' => true, 'message' => __('Database tables created.'));
  }
  
  /**
   * Store the database connection details in app.php.
   * 
   */
  protected function storeDatabaseConnection($dir, $db_database, $db_username, $db_password, &$res)
  {
    // TODO: Advanced settings in form.
    $db_encoding = 'utf8';
    $db_timezone = 'UTC';
    
    $file = 'app.php';
    $config = $dir . '/config/' . $file;
    $content = file_get_contents($config);

    $content = str_replace('__DB_USERNAME__', $db_username, $content, $count);
    if ($count == 0) 
    {
      $res[] = array('result' => false, 'message' => __('No database username (__DB_USERNAME__) to replace in').' config/' . $file);
      return;
    }
    $content = str_replace('__DB_PASSWORD__', $db_password, $content, $count);
    if ($count == 0) 
    {
      $res[] = array('result' => false, 'message' => __('No database password (__DB_PASSWORD__) to replace in').' config/' . $file);
      return;
    }
    $content = str_replace('__DB_DATABASENAME__', $db_database, $content, $count);
    if ($count == 0) 
    {
      $res[] = array('result' => false, 'message' => __('No database name (__DB_DATABASENAME__) to replace in').' config/' . $file);
      return;
    }
    $content = str_replace('__DB_ENCODING__', $db_encoding, $content, $count);
    if ($count == 0) 
    {
      $res[] = array('result' => false, 'message' => __('No database encoding (__DB_ENCODING__) to replace in').' config/' . $file);
      return;
    }
    $content = str_replace('__DB_TIMEZONE__', $db_timezone, $content, $count);
    if ($count == 0) 
    {
      $res[] = array('result' => false, 'message' => __('No database encoding (__DB_TIMEZONE__) to replace in').' config/' . $file);
      return;
    }
    
    $result = file_put_contents($config, $content);
    if($result)
    {
      $res[] = array('result' => true, 'message' => __('Database connection details stored in').' config/' . $file);
      return;
    }
    
    $res[] = array('result' => false, 'message' => __('Unable to store database connection details in').' config/' . $file);
  }
  
  /**
   * Set the security.salt value in app.php.
   *
   * @param string $dir The application's root directory.
   * @param string $newKey key to set in the file
   * @param string $res 
   * @return void
   */
  protected function setSecuritySaltInApp($dir, $newKey, &$res)
  {
    $file = 'app.php';
    $config = $dir . '/config/' . $file;
    $content = file_get_contents($config);

    $content = str_replace('__SALT__', $newKey, $content, $count);

    if ($count == 0) 
    {
      // ..this usually just means this step has been performed already, so not an error.
      $res[] = array('result' => true, 'message' => __('No Security.salt placeholder to replace in').' config/' . $file);
      return;
    }

    $result = file_put_contents($config, $content);
    if($result)
    {
      $res[] = array('result' => true, 'message' => __('Updated Security.salt value in').' config/' . $file);
      return;
    }
    
    $res[] = array('result' => false, 'message' => __('Unable to update Security.salt value in').' config/' . $file);
  }
  
  /**
   * Set globally writable permissions on the "tmp" and "logs" directory.
   *
   * NOTE: This is not the most secure default, but it gets people up and running quickly.
   *
   * @param string $dir The application's root directory.
   * @param $res The result of the operation. Format: array(array('result','message'),array('result','message'), ..)
   * @return void
   */
  protected function setFolderPermissions($dir, &$res)
  {
    // Change the permissions on a path and output the results.
    $changePerms = function ($path, $perms, &$res)
    {
      // Get permission bits from stat(2) result.
      $currentPerms = fileperms($path) & 0777;
      if (($currentPerms & $perms) == $perms)
      {
        $gnarg = array();
        $gnarg['result'] = true;
        $gnarg['message'] = __('Folder permissions already set for path').' '.$path;
        $res[] = $gnarg;
        return;
      }

      $changed = chmod($path, $currentPerms | $perms);
      if ($changed) 
      {
        $gnarg = array();
        $gnarg['result'] = true;
        $gnarg['message'] = __('Permissions set on').' '.$path;
        $res[] = $gnarg;
      } 
      else 
      {
        $gnarg = array();
        $gnarg['result'] = false;
        $gnarg['message'] = __('Failed to set permissions on').' '.$path;
        $res[] = $gnarg;
      }
    };

    $walker = function ($dir, $perms, $res) use (&$walker, $changePerms) 
    {
      $files = array_diff(scandir($dir), ['.', '..']);
      foreach ($files as $file)
      {
        $path = $dir . '/' . $file;

        if (!is_dir($path))
        {
          continue;
        }

        $changePerms($path, $perms, $res);
        $walker($path, $perms, $res);
      }
    };

    $worldWritable = bindec('0000000111');
    $walker($dir . '/tmp', $worldWritable, $res);
    $changePerms($dir . '/tmp', $worldWritable, $res);
    $changePerms($dir . '/logs', $worldWritable, $res);
  }  
  
  /**
   * Create the config/app.php file by copying config/app.default.php. 
   * If app.php already exists, it will be saved as app.old.php, or any serie of app.old_1.php.
   *
   * @param string $dir The application's root directory.
   * @return void
   */
  protected function createAppConfig($dir, &$res, $stashOld)
  {
    $appConfig = $dir . '/config/app.php';
    $defaultConfig = $dir . '/config/app.default.php';
    
    if(file_exists($appConfig) && $stashOld)
    {
      // Save away any old configuration. Name-pattern: app.old.php, app.old_1.php, etc.
      // This is nice for debugging, and of course to return to a previous working configuration.
      $sub = 'old';
      $count = 1;
      while(true)
      {
        $stash = $dir . '/config/app.'.$sub.'.php';
        
        if(!file_exists($stash))
        {
          break;
        }
        
        $sub = 'old_'.$count;
        $count++;
      }
      
      copy($appConfig, $stash);
    
      $this->appendRes($res, __('Stashed away old app.php file to').' `'.$stash.'`.');
    }

    // Create/restore app.php to original state.
    copy($defaultConfig, $appConfig);

    $this->appendRes($res, __('Created `config/app.php` file'));
  }
    
  protected function appendRes(&$res, $message, $result = true)
  {
    $row = array();
    $row['result'] = $result;
    $row['message'] = $message;
    $res[] = $row;
  }
}
