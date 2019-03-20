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

use Cake\Event\Event;
use Cake\ORM\TableRegistry;

// Adaptations for contact form.
use App\Form\ContactForm;
use Cake\Validation\Validator;
use Cake\Mailer\Email;

/**
 * Category controller
 *
 * This controller will render views from Template/Categories/ with content from CatLang table.
 * 
 */
class CategoriesController extends AppController
{
  public $categories;
  public $catlangs;
  
	public function initialize()
	{
		parent::initialize();
		$this->loadComponent('Menu');
		$this->loadComponent('Language');
		
		$this->categories = TableRegistry::get('Categories');
    $this->catlangs = TableRegistry::get('CatLang');
    
    // The homeTree must contain the entire site.
    $homeTree = $this->Menu->GetTree(null, 0, AppController::$selectedLanguage, $this->UserIsAuthor());
    // debug($homeTree);
    $this->set(compact('homeTree'));
	}
  
  public function beforeFilter(Event $event)
  {
    parent::beforeFilter($event);
    
    $this->Auth->allow(['display']); // Visitors can display all pages.
  }
  
  /** 
   * Auth defaults to allow any type of user any type of action, so we must bypass it's defaults and specify here.
   */
  public function isAuthorized($user = null)
  {
    if(parent::isAuthorized($user) == false)
      return false;
   
    if(AppController::UserIsAdmin() == true)
    {
      // Allow all actions, admin is king.
      return true;
    }
    else if(AppController::UserIsAuthor() == true)
    {
      $action = $this->request->getParam('action');
      
      if($action == 'display' || $action == 'create' || $action == 'edit' || $action == 'delete_element')
      {
        return true;
      }
    }

    // Allow nothing. (Except 'display' which are explicitly allowed in beforeFilter().)
    return false;
  }
  /**
   * display() call this function when the page 'contact' are viewed.
   * 
   */
  protected function view_contact($categoryElement)
  {
    $errors = [];
    
		if($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
      
      // The client side (the browser) check the fields as well, with the zurb abide form validation.
      // This is the server side validation, to assure no one tries to hack us.
      $validator = new Validator();
      $validator->add('name', 'length', [
        'rule' => ['minLength', 1],
        // 'message' => _('Please fill in your name')
      ])
      ->add('email', 'format', [
        'rule' => 'email',
        // 'message' => __('This must be a valid email address')
      ])
      ->add('message', 'length', [
        'rule' => ['minLength', 15],
        'message' => __('The message must be at least 15 characters')
      ]);
     
      $errors = $validator->errors($this->request->getData());
      // debug($errors);
      
      if(count($errors) == 0)
      {
        // Values seem correct. Push away an email to support!
        $data = $this->request->data;
        
        // https://book.cakephp.org/3.0/en/core-libraries/email.html
        $email = new Email('default');
        $email->setTo('support@madskullcreations.com')
            ->setFrom($data['email'], $data['name'])
            ->setSubject('Message via madskull contact form'.' - '.$data['name'])
            ->send($data['message']);
            
        // debug($email);
        $this->Flash->success(__('Thank you for contacting us, we will get in touch soon!'));
      }
      else 
      {
        $this->Flash->error(__('There was a problem submitting your form. Please check the error message below each input field.'));
      }
    }
    
    $this->set('errors', $errors);
  }
  
  /**
   * Using the path as an identifier, it loads the content from database and tries to render a view 
   * with the same name. If there is no view file (.ctp) with the given identifier, it renders the 
   * default.ctp view file instead. 
   * 
   */
  public function display()
  {
    $path = func_get_args();
    // debug($path);

    $count = count($path);
    if(!$count) 
    {
      // Missing a path to use as identifier, just redirect home.
      return $this->redirect('/');
    }
    
    // The last element in path is the page we want to show. The others are part of the tree.
    $urlTitles = $path;
    $urlTitle = array_pop($urlTitles);
    
    $i18n = AppController::$selectedLanguage;

    // Fetch the parent category id if the url contains more parts. Fex. "/road/to/heaven", "heaven" is current page, "to" would be parent category.
    if(count($urlTitles) > 0)
    {
      // Get the path, or null if it does not exist.
      $lastCategory = $this->categories->GetPath($urlTitles, $i18n, true, false);
      // debug($lastCategory);
      
      if($lastCategory == null)
      {
        // The path does not exist. Redirect logged in user to create page.
        $this->redirectFromNonExistantPage($path);
      }
      
      $parentCategoryId = $lastCategory->id;
    }
    else 
    {
      // This page is a root page, it has no parent category.
      $parentCategoryId = null;
    }

    // debug($parentCategoryId);
    // debug($urlTitle);
    // debug($i18n);
    
    // A loaded category, the current page.
    // 
    $categoryElement = $this->categories->GetElement($parentCategoryId, $urlTitle, $i18n);
    // debug($categoryElement);

    // Test to see we get same result and format.
    // $skumrask = $this->categories->GetElementById($categoryElement->id, $i18n);
    // debug($skumrask);
    
    if($categoryElement == null)
    {
      // The page does not exist in the requested language. Try loading default the same page in default language.
      $categoryElement = $this->categories->GetElement($parentCategoryId, $urlTitle, AppController::$defaultLanguage);
      
      if($categoryElement == null)
      {
        // The page does not exist. Redirect logged in user to create page.
        return $this->redirectFromNonExistantPage($path);
      }
      
      $this->Flash->success(__('The page is shown in the default language.'));
    }
    // debug($categoryElement);
    
    if($categoryElement->layout != null)
    {
      // Default layout are set in AppController::initialize().
      $this->viewBuilder()->layout($categoryElement->layout);
    }
    
    $breadcrumbPath = $this->Menu->GetPath($path, $i18n);
    // debug($breadcrumbPath);
    
    // TODO:
    $pageName = "slork";
    
    // TODO:
    $level = 5;
    
    $urlTitlesForCategory = $this->catlangs->GetUrlTitlesFor($categoryElement->id);
    // debug($urlTitlesForCategory);
        
    // Get the menu tree with the root elements and their immediate children.
    $sideMenuTree = $this->Menu->GetTree($parentCategoryId, $level, $i18n, $this->UserIsAuthor());
    // debug($sideMenuTree);
    
    if(AppController::UserIsAuthor() == true)
    {
      // Show add new language button if the page has missing translations.
      $missingLanguages = $this->Language->GetMissingLanguages($categoryElement->id);
      $this->set('missingLanguages', $missingLanguages);
    }
    
    $this->set(compact(
      'urlTitles', 'urlTitlesForCategory', 'pageName', 'i18n', 'categoryElement', 'breadcrumbPath', 'sideMenuTree'));
    
    // Tries to call specific function in CategoriesController. (this class)
    // 
    // Example:
    //  You have a path to your blog, like this:
    //    /fancy/path/to/my/blog
    //  It will look for a function named like so:
    //    view_fancy_path_to_my_blog()
    // 
    $functionName = 'view_'.implode('_', $path);
    if(method_exists($this, $functionName))
    {
      call_user_func_array(array($this, $functionName), array($categoryElement));
    }
    
    // Tries to render specific .ctp file. If it does not exist, fall back to the default .ctp file.
    // Using DS as we will check for a file's existence on the server.
    // 
    // Example:
    //  You have a path to your blog, like this:
    //    /fancy/path/to/my/blog
    //  If you want to create your own design for this page, the .ctp file 
    //  must then in this folder structure on the server:
    //    /src/Template/Categories/fancy/path/to/my/blog.ctp
    //  To create a .ctp file for the fancy-page, put it here:
    //    /src/Template/Categories/fancy.ctp
    // 
    $file = APP.'Template'.DS.'Categories'.DS.implode(DS, $path).'.ctp';
    // debug($file);
      
    if (file_exists($file)) 
    {
      // debug("File exists");
      $this->render(implode('/', $path));
    }
    else 
    {
      $this->render('default');
    }
  }
  
  protected function redirectFromNonExistantPage($path)
  {
    // The page does not exist. Redirect logged in user to create page.
    if(AppController::UserIsAuthor() == true)
    {
      return $this->redirect(['action' => 'create_from_url', implode('/', $path)]);
    }
    else
    {
      if(count($path) == 1 && $path[0] == 'home')
      {
        // The home page are gone. Redirect to login page and tell user the home page must exist.
        $this->Flash->error(__('The page "home" does not exist for the selected language! Please login and create the home page.'));
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
      }
      
      $this->Flash->error(__('Page does not exist.'));
      return $this->redirect('/');
    }
  }
  
  /**
   * Creates all non-existant parts of the url up to the requested page, in the given language.
   * 
   */
  public function createFromUrl()
  {
    // $path contains the requested url "the/pretty/flower". Create them all.
    $path = func_get_args();
    // debug($path);
    
    $count = count($path);
    if(!$count) 
    {
      // Missing a path to use as identifier, just redirect home.
      $this->Flash->error(__('Missing parameters.'));
      return $this->redirect('/');
    }
     
		if ($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
      
      $i18n = $this->request->data['i18n'];
      
      // TODO: Path! Create all category elements in the url path.
      $parentCategoryId = null;
      
      // Create the category with it's title and content.
      $categoryElement = $this->categories->CreateCategory($parentCategoryId, $this->request->data);
      // debug($categoryElement);
      
      // Redirect to the new page.
      $path = $this->categories->PathFor($categoryElement->id, $i18n);
      // debug($path);
      
      $this->Flash->success(__('The page was created.'));
      return $this->redirect($path);
    }

    // Check to see if the category exists, just not in the requested language.
    $urlTitles = $path;
    $urlTitle = array_pop($urlTitles);
    
    $parentCategoryId = null;
    
    $elms = $this->categories->GetElementByUrlTitle($parentCategoryId, $urlTitle);
    // debug($elms);
    
    if($elms != null)
    {
      // TODO: There are one or more translated versions. Lets redirect to add_new_language.
      $categoryId = $elms[0]->id;
    }

    $i18n = AppController::$selectedLanguage;
    $availableLanguageCodes = $this->Language->GetLanguageCodes();
    
    // If user are coming from SimplicitySettings, wanting to add a new language currently not present.
    $kitchenSink = TableRegistry::get('KitchenSink');
    $languageToAdd = $kitchenSink->Retrieve('LanguageToAdd');
    // debug($languageToAdd);
    
    if($languageToAdd != null)
    {
      $kitchenSink->Forget('LanguageToAdd');
      
      $languages = TableRegistry::get('Languages');
      $lang = $languages->
        find()->
        select(['Languages.i18n','Languages.long_name'])->
        where(['i18n' => $languageToAdd])->
        first();
      // debug($lang);
      
      $availableLanguageCodes[$lang->i18n] = $lang->long_name;
      
      // Preselect.
      $i18n = $lang->i18n;
    }
    
    if(count($availableLanguageCodes) == 0)
    {
      // There are no languages present yet! (No pages at all actually) Redirect user to settings page.
      $this->Flash->default(__('Please add a language for your web page.'));
      return $this->redirect(['controller' => 'SimplicitySettings', 'action' => 'language']);
    }

    // Fetch available layout files.
    $layoutFiles = $this->FetchLayoutFiles();
    
    $this->set(compact('path','i18n','availableLanguageCodes','layoutFiles','categoryElement'));
    
    // In admin pages the simplicity layout are always used.
    $this->viewBuilder()->layout('simplicity');
  }
  
  /**
   * Add new translation of an existing page.
   * 
   */
  public function addNewLanguage($categoryId = null, $i18n = null)
  {
    // 0. Category must already exist.
    // 1. Create new catlang. (language version)
    // 2. Redirect to edit().
    
    if($categoryId == null || $i18n == null)
    {
      $this->Flash->error(__('Missing parameters.'));
      return $this->redirect('/');
    }

		if ($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
      
      $categoryId = $this->request->data['id'];
      $i18n = $this->request->data['i18n'];
      $urlTitle = $this->request->data['url_title'];
      $title = $this->request->data['title'];
      $content = $this->request->data['content'];
      
      // Add the new CatLang.
      $this->categories->CreateCatLangForCategory($categoryId, $urlTitle, $i18n, $title, $content);
      
      $path = $this->categories->PathFor($categoryId, $i18n);
      // debug($path);

      // Add language in url. (Otherwise default language will be added and that is probably not the same language as user has translated to.)
      // $path = mb_substr($path, 0, mb_strlen($path) - 1);
      $path .= '?lang='.$i18n;
      
      // Redirect to the new page.
      $this->Flash->success(__('The page was created.'));
      return $this->redirect($path);
    }
    
    // Fetch default language version.
    $categoryElement = $this->categories->GetElementById($categoryId, AppController::$defaultLanguage);
    // debug($categoryElement);
    
    if($categoryElement == null)
    {
      // Page does not exist in default language, fetch any version.
      $categoryElement = $this->categories->GetElementById($categoryId);
      
      if($categoryElement == null)
      {
        // Odd, page does not exist.
        $this->Flash->error(__('Page does not exist.'));
        return $this->redirect('/');        
      }
    }
    
    $availableLanguageCodes = $this->Language->GetLanguageCodes();
		$implementedLanguageCodes = $this->Language->GetLanguagesFor($categoryId);
		$missingLanguages = $this->Language->GetMissingLanguages($categoryId);
    
    // Fetch available layout files.
    $layoutFiles = $this->FetchLayoutFiles();
    
    $this->set(compact('i18n','availableLanguageCodes','implementedLanguageCodes','missingLanguages', 'layoutFiles','categoryElement'));
    
    // In admin pages the simplicity layout are always used.
    $this->viewBuilder()->layout('simplicity');
  }
      
  /**
   * Edit the page with the given id and language.
   * 
   * Use case: Editor user changes language in drop down and the current page does not exist in that language.
   * 
   */
	public function edit($categoryId = null, $i18n = null)
	{
    if($categoryId == null || $i18n == null)
    {
      $this->Flash->error(__('Missing parameters.'));
      return $this->redirect('/');
    }
     
    // Fetch all language versions.
    // $categoryElement = $this->categories->GetElementById($categoryId);
    // debug($categoryElement);
          
		if($this->categories->exists(['id' => $categoryId]) == false)
		{
			$this->Flash->error(__('The page could not be found.'));
			return $this->redirect('/');
		}
    
		if ($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
      
      // Update category element.
      $id = $this->request->data['id'];
      $categoryElement = $this->categories->get($id);
      // debug($categoryElement);
      
      $categoryElement->layout = $this->request->data['layout'];
      $categoryElement->in_menus = $this->request->data['in_menus'];
      $this->categories->save($categoryElement);
      // debug($categoryElement);      
      
      // Update catlang element.
      $catLangId = $this->request->data['catlang_id'];
      $catLang = $this->catlangs->get($catLangId);
      // debug($catLang);
      
      $catLang->url_title = $this->request->data['url_title'];
      $catLang->title = $this->request->data['title'];
      $catLang->content = $this->request->data['content'];
      
      $this->catlangs->save($catLang);
      
      // Redirect to the new page.
      $path = $this->categories->PathFor($catLang->category_id, $catLang->i18n);
      
      $this->Flash->success(__('Changes saved.'));
      return $this->redirect($path);
    }
    
    $availableLanguageCodes = $this->Language->GetLanguageCodes();
    $missingLanguages = $this->Language->GetMissingLanguages($categoryId);
    $categoryElement = $this->categories->GetElementById($categoryId, $i18n);
    
    // Fetch available layout files.
    $layoutFiles = $this->FetchLayoutFiles();
    
    // debug($categoryId);
    // debug($i18n);
    // debug($categoryElement);
    
    $this->set(compact('i18n','availableLanguageCodes','missingLanguages', 'layoutFiles','categoryElement'));
    
    // In admin pages the simplicity layout are always used.
    $this->viewBuilder()->layout('simplicity');
  }
  
  /**
   * Delete the page with the given id and language.
   * If all languages (CatLang´s) for the category is deleted, also the category gets deleted.
   *
   * Acces to this page is handled by isAuthorized().
   */
	public function deleteElement($categoryId = null, $i18n = null)
	{
    if($categoryId == null || $i18n == null)
    {
      $this->Flash->error(__('Missing parameters.'));
      return $this->redirect('/');
    }
        
		// Make sure only post and delete are allowed. Trying to load this page normally will yield an exception.
		// It is a safety-precaution as web crawlers could accidentally delete all content while exploring all links.
    // (If it would be logged in, that is.)
    // 
		$this->request->allowMethod(['post', 'delete']);
					
    $element = $this->catlangs->GetElement($categoryId, $i18n);
        
    // debug($categoryId);
    // debug($i18n);
    // debug($element);
        
		if($this->catlangs->delete($element))
		{
      // Remove the Category as well if all CatLang´s are deleted.
      $count = $this->catlangs->CatLangCount($categoryId);
      
      if($count == 0)
      {
        $this->categories->DeleteElement($categoryId);
        
        $this->Flash->success(__('The page was deleted.'));
      }
      else
      {
        $this->Flash->success(__('The page was deleted.').' '.__('It still exist in').' '.$count.' '.__('more languages.'));
      }
		}
    else
    {
      $this->Flash->error(__('The page could not be deleted.'));
    }
		
		return $this->redirect('/');
	}
}




