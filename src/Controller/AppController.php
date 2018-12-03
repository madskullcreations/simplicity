<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure; // Ta bort?

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
		// TODO:
		//   <-Vid installationen så sätts alla default-värden efter att databas+tabeller skapats. 
		//     Sedan är de enkla att komma åt från admin, och de läses in i Cache, läses från cache, som vanligt. 
		
		public static $selectedLanguage = '';
		public static $simplicity_site_title = '';
		public static $simplicity_site_description = '';
		public static $simplicity_footer_text = '';
		
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
          
        $this->loadComponent('Security');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Menu');
        $this->loadComponent('Auth', [
            'loginRedirect' => [
                'controller' => 'SimplicitySettings',
                'action' => 'index'
            ],
            'logoutRedirect' => [
              'controller' => 'EditablePages', 
              'action' => 'display', 
              'home'
            ],
            'authorize' => 'Controller', // Let the controller's isAuthorized() decide.
        ]);
        
        $kitchenSink = TableRegistry::get('KitchenSink');
        
        // Try get the chosen language as an url param, namely '?lang=SV-se'. 
        AppController::$selectedLanguage = $this->request->query('lang');
        
        if(AppController::$selectedLanguage != null && AppController::$selectedLanguage != '')
        {
          // Make sure it is a valid language.
          // TODO: Have a DoExist().
          $languages = TableRegistry::get('Languages');
          $variants = $languages->GetVariants(AppController::$selectedLanguage);
          
          // debug(AppController::$selectedLanguage);
          // debug($variants);
          // debug($variants->first());
          
          if($variants->first() == null)
          {
            AppController::$selectedLanguage = null;
          }
        }
        
        
        // TODO: Try to get from browser cookie, and if no cookie, use the default language of the site.
        
        if(AppController::$selectedLanguage == null)
        {
          // No language found from url. Fetch default language.
          // Set default language to your like, but make sure it is present in table languages, i18n.
          // 
          AppController::$selectedLanguage = $kitchenSink->Retrieve('SimplicityDefaultLanguage', 'sv');
        }
        
        // Fetch some site-global settings from the kitchen sink.
        AppController::$simplicity_site_title = $kitchenSink->Retrieve('SimplicitySiteTitle', 'Simplicity CMS');
        AppController::$simplicity_site_description = $kitchenSink->Retrieve(
            'SimplicitySiteDescription', 'Simple yet powerful');
        AppController::$simplicity_footer_text = $kitchenSink->Retrieve(
            'SimplicityFooterText', '<div class="cell auto"><a href="https://github.com/madskullcreations/simplicity" target="_blank">Simplicity CMS</a>&nbsp;- Simple yet powerful</div><div class="cell auto">Powered by&nbsp;<a href="https://cakephp.org/" target="_blank">CakePHP</a>&nbsp;and&nbsp;<a href="https://foundation.zurb.com/" target="_blank">Zurb Foundation</a></div><div class="cell auto">A&nbsp;<a href="https://madskullcreations.com" target="_blank">Madskull Creations</a>&nbsp;product</div>');
        
        // To make it available from views as well. 
        $this->set('userIsLoggedIn', AppController::UserIsLoggedIn());
        $this->set('userIsAdmin', AppController::UserIsAdmin());
        $this->set('userIsAuthor', AppController::UserIsAuthor());
        $this->set('selectedLanguage', AppController::$selectedLanguage);

        if($this->request->getParam('controller') != 'EditablePages' || $this->request->getParam('action') != 'display')
        {
          // Not EditablePagesController::display(), so we should create content for the menus and breadcrumbs following the cake-pattern controller/action.
          // NOTE: EditablePagesController set these on its own, following it's own patterns.
          
          $urlParts = explode('/', $this->request->url);
          // debug($urlParts);
          
          if(count($urlParts) > 1)
          {
            $controllerUrlName = $urlParts[0];
            $actionUrlName = $urlParts[1];
            $url = $controllerUrlName.DS.$actionUrlName;
            
            if(count($urlParts) > 2)
            {
              unset($urlParts[0]);
              unset($urlParts[1]);
              $remainingUrlParts = implode($urlParts);
              
              $url .= DS.$remainingUrlParts;
            }
          }
          else
          {
            $actionUrlName = '';
            $url = '';
          }
          
          $this->SetFakeSimplicityVariables($actionUrlName, $url);
        }
        
        if(AppController::UserIsLoggedIn())
        {
          $sideMenuTreeAdmin = array();
          
          $sub = array();
          $sub[] = $this->Menu->CreateMenuElement(__("Add new language"), 1, 'simplicity_settings/language');
          $sideMenuTreeAdmin[] = $this->Menu->CreateMenuElement(__("Overview"), 0, 'simplicity_settings', 'Categories', $sub);
                    
          $this->set('sideMenuTreeAdmin', $sideMenuTreeAdmin);
        }
        
        // debug($this->request);
        // debug($this->request->getParam('controller'));
        // debug($this->request->url);

        // Language selector dropdown will need available languages.
        $richTextElements = TableRegistry::get('RichTextElements');
        $availableLanguages = $richTextElements->GetLanguageCodes();
        // debug($availableLanguages);
        
        $this->set('availableLanguages', $availableLanguages);
        
        // Default layout is simplicity. To define your own, copy and rename src/Template/Layout/simplicity.ctp 
        // and change here. Each controller can have their own layout, and you can even define a layout per view. 
        $this->viewBuilder()->layout('simplicity');
              
        // TESTING
        if(false)
        {
          // $tLanguages = TableRegistry::get('Languages');
          // $variants = $tLanguages->GetVariants('en');
          // debug($variants);
          
          $home = $richTextElements->GetElement('home',null,'sv_SE');
          $languagesForHome = $richTextElements->GetLanguagesFor($home->name, $home->category_id);
//         debug($home);
//         debug($languagesForHome);
          
          $categories = TableRegistry::get('Categories');
          // $children = $categories->GetChildren(4);
        }
        // TESTING END
    }
    
    /**
     * Some variables need to be set, since simplicity.ctp expect them to exist. When not in EditablePagesController and in some other cases
     * this function comes in handy, since it defines the variables needed.
     */
    protected function SetFakeSimplicityVariables($actionUrlName = '', $url = '')
    {
      $this->set('breadcrumbPath', array());
      $this->set('homeTree', array());
      $this->set('sideMenuTree', array());

      $rte = (object)[
        'id' => -1,
        'url_title' => $actionUrlName, 
        'title' => $actionUrlName, 
        'path' => $url,
        'content' => '',
        'created' => null,
        'modified' => null];
      $this->set('richTextElement', $rte);
    }

    /**
     * Before filter callback.
     * 
     * Docs says:
     *  "Called during the Controller.initialize event which occurs before every action in the controller. 
     *  It’s a handy place to check for an active session or inspect user permissions."
     */
    public function beforeFilter(Event $event)
    {
      // debug($event);
            
      // Note: This allow visitors to access 'display'. Logged in users can access _any_ resource as long as isAuthorized() returns true!
      $this->Auth->allow(['display']);
      
      // Test: Is null if no logged in user are present, otherwise a full user object. 
      // $user = $this->Auth->user();
      // debug($user);
    }
    
    /** 
     * Somewhat complicated: If a certain action are explicitly allowed, fex. $this->Auth->allow(['display']);
     * this function is never called.
     * 
     * This function seem to be called when Auth have no instructions about what to do with a certain request.
     */
    public function isAuthorized($user = null)
    {
      // Base class cannot make any decisions.
      return true;
    }
    
    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
    
// TODO: How does it handle several url params?!
    /* Adds the current language url parameter on any redirect.
     * 
     */
    public function redirect($url, $status = 302)
    {
    	if(!is_array($url))
    	{
    		if(strpos($url,'?lang=') === false)
    		{
    			$url .= '?lang='.AppController::$selectedLanguage;
    		}
    	}
    	else if(!isset($url['?'])) 
    	{
    		// TODO: Not tested.
    		$url['?'] = '?lang='.AppController::$selectedLanguage;
    	}
    	
    	return parent::redirect($url, $status);
    }
    
    /**
     * A logged in user can see user listing and settings side menu.
     * (It is not a role, just simpler to use than checking the role of the user)
     */
    public function UserIsLoggedIn()
    {
      $user = $this->Auth->user();
      
      if($user != null)
      {
        return true;
      }
      
      return false;
    }
    
    /**
     * An 'admin' has system-wide full access.
     */
    public function UserIsAdmin()
    {
    	$user = $this->Auth->user();
      // debug($user);
      
      if($user != null)
      {
        if($user['role'] == 'admin')
        {
          return true;
        }
      }
      
    	return false;
    }
    
    /**
     * An 'author' can edit pages, but not create new.
     */
    public function UserIsAuthor()
    {
    	$user = $this->Auth->user();
      
      if($user != null)
      {
        if($user['role'] == 'author' || $user['role'] == 'admin')
        {
          // admin are always also author.
          return true;
        }
      }
      
    	return false;
    }
}
