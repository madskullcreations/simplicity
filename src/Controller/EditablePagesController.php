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

/**
 * Editable content controller
 *
 * This controller will render views from Template/EditablePages/ with content from EditablePages table.
 * 
 */
class EditablePagesController extends AppController
{
	public $categories;
	public $richTextElements;
	
	public function initialize()
	{
		parent::initialize();
		$this->loadComponent('Menu');
		$this->loadComponent('Language');
		
		$this->categories = TableRegistry::get('Categories');
		$this->richTextElements = TableRegistry::get('RichTextElements');
    
    $homeTree = $this->Menu->GetTree(null, 5, AppController::$selectedLanguage);
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
      
      if($action == 'display' || $action == 'edit' || $action == 'delete')
      {
        return true;
      }
    }

    // Allow nothing. (Except 'display' which are explicitly allowed in beforeFilter().)
    return false;
  }
   
  /**
   * Using the path as an identifier, it loads the content from database and tries to render a view 
   * with the same name. If there is no view file (.ctp) with the given identifier, it renders the 
   * default.cpt view file instead. 
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
          
    // The last element in path is always the page, all others are categories.
    $categoryUrlTitles = $path;
    $pageName = array_pop($categoryUrlTitles);
    
    $this->set(compact('categoryUrlTitles'));
    
    // debug($categoryUrlTitles);
    //debug($pageName);
    //debug(AppController::$selectedLanguage);

    $language = AppController::$selectedLanguage;
          
    $createIfNotExist = false;
    $askToCreate = false;
    if($this->UserIsAdmin())
    {
      $askToCreate = true;
      
      if($this->request->query('doCreate') == true)
      {
        // Coming from SimplicitySettings::language(), we want to create the page in the current language.
        $createIfNotExist = true;
      }
      
      if ($this->request->is(['post', 'put'])) 
      {
        // debug($this->request->data);
        
        if(isset($this->request->data['doCreate']) && $this->request->data['doCreate'] == 'yes')
        {
          // Admin says: create the page and be quiet!
          $createIfNotExist = true;
        }
      }
    }
    
    // If there are more parts of the url, lets make a category-tree out of it. 
    if(count($categoryUrlTitles) > 0)
    {
      // Get the path, or null if it does not exist and is not allowed to create it. 
      $lastCategory = $this->categories->GetPath($categoryUrlTitles, $language, true, $createIfNotExist);
      // debug($lastCategory);
      
      if($lastCategory == null)
      {
        // The path does not exist.
        
        if($askToCreate)
        {
          // Ask admin if the path and page should be created.
          $this->SetFakeSimplicityVariables($pageName, $path);
          $this->set(compact('path', 'language'));
          return $this->render('ask_to_create');
        }
        else
        {
          // Redirect visitors home.
          $this->Flash->error(__('Path does not exist.'));
          return $this->redirect('/');
        }
      }
      
      $categoryId = $lastCategory->id;
      $parentCategoryId = $lastCategory->parent_id;
      $level = $lastCategory->level + 3;
    }
    else 
    {
      // This page is a root page, it has no parent category.
      $categoryId = null;
      $parentCategoryId = null;
      $level = 2;
    }
    
    // Load the content of the current page.
    $this->richTextElements = TableRegistry::get('RichTextElements');
      
    // We are not translating the page, so pass null as page_id. If page gets created, it comes with a new page_id.
    $richTextElement = $this->richTextElements->GetElement(
        $pageName, $categoryId, $language, null, $createIfNotExist);
    // debug($richTextElement);
    
    if($richTextElement == null)
    {
      if($askToCreate)
      {
        // Ask admin if the path and page should be created.
        $this->SetFakeSimplicityVariables($pageName, $path);
        $this->set(compact('path', 'language'));
        return $this->render('ask_to_create');
      }
      else
      {
        // Element did not exist and visitor was not allowed to create a page.
        $this->Flash->error(__('Page does not exist.'));
        
        if($pageName == 'home' && count($categoryUrlTitles) == 0)
        {
          // The home page are deleted or never created! Fake it. User must login to create it.
          $this->SetFakeSimplicityVariables($pageName, $path);
          $this->set(compact('path', 'language'));
          $this->render('default');
          
          return;
        }
        
        return $this->redirect('/');
      }
    }
    
    // Fetch the various url-titles for the current page's every language.
    // debug($richTextElement->page_id);
    $urlTitles = $this->richTextElements->GetUrlTitlesFor($richTextElement);
    // debug($urlTitles);
    
    // Set the path so the Menu helper can use it to create the breadcrumb path correctly.
    $this->Menu->SetPathFor($richTextElement);
    // debug($richTextElement->path);
    
    $breadcrumbPath = $this->Menu->GetPath($categoryUrlTitles, $language);
    // debug($breadcrumbPath);
    
    // Get the menu tree with the root elements and their immediate children.
    $sideMenuTree = $this->Menu->GetTree($parentCategoryId, $level, $language);
    //	$sideMenuTree = $this->Menu->GetTree(null, 20);
    // debug($sideMenuTree);
        
    $this->set(compact('urlTitles', 'pageName', 'language', 'richTextElement', 'breadcrumbPath', 'sideMenuTree'));

    // Tries to render specific .ctp file. If it does not exist, fall back to the default .ctp file.
    // Using DS as we will check for a file's existence on the server.
    // 
    // Example:
    //  You have a path to your blog, like this:
    //    /fancy/path/to/my/blog
    //  If you want to create your own design for this page, the .ctp file 
    //  must then in this folder structure on the server:
    //    /src/Template/EditablePages/fancy/path/to/my/blog.ctp
    //  To create a .ctp file for the fancy-page, put it here:
    //    /src/Template/EditablePages/fancy.ctp
    // 
    $file = APP.'Template'.DS.'EditablePages'.DS.implode(DS, $path).'.ctp';
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

  /**
   * Edit the page with the given id. 
   * If preselectedLanguage is set, the language selector is populated with the value.
   */
	public function edit($id = null, $preselectedLanguage = null)
	{
		if($this->richTextElements->exists(['id' => $id]) == false)
		{
			$this->Flash->error(__('The page could not be found.'));
			return $this->redirect('/');
		}

		$element = $this->richTextElements->get($id);
		// debug($element);
		
		$availableLanguageCodes = $this->Language->GetLanguageCodes();
		$implementedLanguageCodes = $this->Language->GetLanguagesFor($element->page_id);
		$missingLanguages = $this->Language->GetMissingLanguages($element->page_id);
		// debug($availableLanguageCodes);
    // debug($implementedLanguageCodes);
    // debug($missingLanguages);

    // Coming from SimplicitySettings, wanting to add a new language currently not present.
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

      // It might return more than one, but we want the exact
      // $lang = reset($langs);
      
      $availableLanguageCodes[$lang->i18n] = $lang->long_name;
      $missingLanguages[$lang->i18n] = $lang->long_name;
      // debug($missingLanguages);
    }
    
		if ($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
			// debug($element);
			
			if(isset($this->request->data['i18n']) && $this->request->data['i18n'] != $element->i18n && $this->request->data['i18n'] != '')
			{
        // User is translating the page into a new language.
        $oldLanguageCode = $element->i18n;
        $pageId = $element->page_id;
        
				// Save as new page in the new language. 
				$element = $this->richTextElements->GetElement(
            $element->url_title, 
						$element->category_id, 
						$this->request->data['i18n'],
				    $pageId,
						true);

        // To keep the connection between them we use the same page_id.
        $this->request->data['page_id'] = $element->page_id;
                   
				// Set the new content. 
				$this->richTextElements->patchEntity($element, $this->request->data);
				
				if ($this->richTextElements->save($element))
				{
					$this->Flash->success(__('Your page has been created in the new language.'));

					// Get path as string for the page in the old language.
					$path = $this->categories->PathFor($element->category_id, $oldLanguageCode);
          $categoryUrlTitles = explode('/', $path);
          foreach($categoryUrlTitles as $id => $name)
          {
            if($name == '')
              unset($categoryUrlTitles[$id]);
          }
          // debug($categoryUrlTitles);
          
          // Make sure the path exists in the new language.
          $createIfNotExist = true;
          $lastCategory = $this->categories->GetPath($categoryUrlTitles, $this->request->data['i18n'], true, $createIfNotExist);
          // debug($lastCategory);

					// Get path as string for the page.
					$path = $this->categories->PathFor($element->category_id, $this->request->data['i18n']);
					$path .= $element->url_title;
					// debug($path);
						
					return $this->redirect($path.'?lang='.$element->i18n);
				}
				else
				{
					$this->Flash->error(__('The page could not be saved.'));
				}
			}
			else 
			{
				// When updating an existing page, the language cannot be changed. 
				unset($this->request->data['i18n']);
				
				// Copy values into the element while also validating the fields.
				$this->richTextElements->patchEntity($element, $this->request->data);
				
				// Now a 'dirty' flag is set for the 'content', hinting it has been modified but not yet saved.
				// The 'modified' flag is not yet updated as it happens right before saving. 
				// 
				// debug($element);
				
				// Save the element with it's changes.
				if ($this->richTextElements->save($element)) 
				{
					$this->Flash->success(__('Your page has been updated.'));
					
					// Get path for the page.
					$path = $this->categories->PathFor($element->category_id, $element->i18n);
					$path .= $element->url_title;
					// debug($path);
					
					return $this->redirect($path);
				}
				else
				{
					$this->Flash->error(__('The page could not be saved.'));
				}
			}
		}
		
		$this->viewBuilder()->layout('simplicity');
		$this->set(compact('element','availableLanguageCodes','implementedLanguageCodes','missingLanguages','preselectedLanguage'));
	}

	public function delete($id = null)
	{
    // Handled by isAuthorized().
    /*if($this->UserIsAuthor() == false)
		{
			$this->Flash->error(__('You do not have permission to delete this page.'));
			return $this->redirect('/');
		}*/
		
		// Make sure only post and delete are allowed. Trying to load this page normally will yield an exception.
		// It is a safety-precaution as web crawlers could accidentally delete all content while exploring all links.
		$this->request->allowMethod(['post', 'delete']);
					
		$element = $this->richTextElements->get($id);
					
		if($this->richTextElements->delete($element))
		{
			$this->Flash->success(__('The page was deleted.'));
			return $this->redirect('/');
		}
		
		$this->Flash->error(__('The page could not be deleted.'));
		return $this->redirect('/');
	}		
}
