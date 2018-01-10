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

/**
 * Installer controller
 *
 * NOTE: It is not inheriting AppController, as it expect a database connection.
 * 
 */
class InstallerController extends Controller
{
  /**
   * Let user setup database connection details and create an admin-user.
   * 
   * User are redirected here when there are no database connection or no admin-user.
   * 
   */
	public function index()
	{
    $simplicity_setup_state = Configure::read('simplicity_setup_state');
    // debug($simplicity_setup_state);
    
    $this->set('simplicity_setup_state', $simplicity_setup_state);
    
    // TODO: Fetch posts and create and populate fields.
    // TODO: Test database settings.
    // TODO: Save database settings in config/app.php
    // TODO: Create tables. Create salt.
    // TODO: Create the user with choosen password.
        
    $this->viewBuilder()->layout('installer');
	}
}
