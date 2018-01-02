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

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        $kitchenSink = TableRegistry::get('KitchenSink');
        
        // Try get the chosen language as an url param, namely '?lang=SV-se'. 
        AppController::$selectedLanguage = $this->request->query('lang');
        
        // TODO: Try to get from browser cookie, and if no cookie, use the default language of the site.
        
        if(AppController::$selectedLanguage == null)
        {
        	// No language found from url. Fetch default language.
        	// Set default language to your like, but make sure it is present in table languages, i18n.
        	// 
        	AppController::$selectedLanguage = $kitchenSink->Retrieve('SimplicityDefaultLanguage', 'sv_SE');
        }
        
        // Fetch some site-global settings from the kitchen sink.
        AppController::$simplicity_site_title = $kitchenSink->Retrieve('SimplicitySiteTitle', 'Aurora Rizo Lopez');//Simplicity CMS');
        AppController::$simplicity_site_description = $kitchenSink->Retrieve(
        		'SimplicitySiteDescription', 'Master in Business administration');
        AppController::$simplicity_footer_text = $kitchenSink->Retrieve(
        		'SimplicityFooterText', 'Simplicity CMS - Simple. Simple. Simple. | Powered by CakePHP and Zurb Foundation | A Madskull Creations product');
        
        // To make it available from views as well. TODO: Call function from view, as in cakephp2? 
        $this->set('userIsAdmin', AppController::UserIsAdmin());
        	        
        // TESTING
        $languages = TableRegistry::get('Languages');
        // $variants = $languages->GetVariants('en');
        // debug($variants);
        
        $richTextElements = TableRegistry::get('RichTextElements');
        
        $languages = $richTextElements->GetLanguageCodes();
        
        $home = $richTextElements->GetElement('home',null,'sv_SE');
        $languagesForHome = $richTextElements->GetLanguagesFor($home->name, $home->category_id);
//         debug($home);
//         debug($languagesForHome);
        
        $categories = TableRegistry::get('Categories');
        // $children = $categories->GetChildren(4);
                           
        // TESTING END
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
    
    public static function UserIsAdmin()
    {
    	// TODO: Add session logic here.
    	return true;
    }
}
