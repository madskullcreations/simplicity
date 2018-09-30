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
    $categoryNames = $path;
    $pageName = array_pop($categoryNames);			
    
    //debug($categoryNames);
    //debug($pageName);
    //debug(AppController::$selectedLanguage);

    $language = AppController::$selectedLanguage;
          
    $createIfNotExist = false;
    if($this->UserIsAdmin())
    {
      $createIfNotExist = true;
    }

    // If there are more parts of the url, lets make a category-tree out of it. 
    if(count($categoryNames) > 0)
    {
      // Get the path, or null if it does not exist and is not allowed to create it. 
      $lastCategory = $this->categories->GetPath($categoryNames, $language, true, $createIfNotExist);
      // debug($lastCategory);
      
      if($lastCategory == null)
      {
        // The path does not exist, redirect home.
        $this->Flash->error(__('Path does not exist.'));
        return $this->redirect('/');
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
      
    $richTextElement = $this->richTextElements->GetElement(
        $pageName, $categoryId, $language, $createIfNotExist);
    // debug($richTextElement);
    
    if($richTextElement == null)
    {
      // Element did not exist and visitor was not allowed to create a page.
      $this->Flash->error(__('Page does not exist.'));
      return $this->redirect('/');
    }
    
    // Set the path so the Menu helper can use it to create the breadcrumb path correctly.
    $this->Menu->SetPathFor($richTextElement);
    // debug($richTextElement->path);
    
    $breadcrumbPath = $this->Menu->GetPath($categoryNames, $language);
    // debug($breadcrumbPath);
    
    // Get the menu tree with the root elements and their immediate children.
    $tree = $this->Menu->GetTree($parentCategoryId, $level, $language);
    //	$tree = $this->Menu->GetTree(null, 20);
    // debug($tree);
    
    $homeTree = $this->Menu->GetTree(null, 5, $language); 			
    
    $this->set(compact('categoryNames', 'pageName', 'language', 'richTextElement', 'breadcrumbPath', 'tree', 'homeTree'));

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

	public function edit($id = null)
	{
    // Handled by isAuthorized().
    /*if($this->UserIsAuthor() == false)
		{
			$this->Flash->error(__('You are not allowed to edit content of this page.'));
			return $this->redirect('/');
		}*/
				
		if($this->richTextElements->exists(['id' => $id]) == false)
		{
			$this->Flash->error(__('The page could not be found.'));
			return $this->redirect('/');
		}

		$element = $this->richTextElements->get($id);
		// debug($element);
		
		$availableLanguageCodes = $this->Language->GetLanguageCodes();
		$implementedLanguageCodes = $this->Language->GetLanguagesFor($element->name, $element->category_id);
		$missingLanguages = $this->Language->GetMissingLanguages($element->name, $element->category_id);
		// debug($availableLanguageCodes);
					
		if ($this->request->is(['post', 'put'])) 
		{
			// debug($this->request->data);
			// debug($element);
			
			if(isset($this->request->data['i18n']) && $this->request->data['i18n'] != $element->i18n && $this->request->data['i18n'] != '')
			{
				// Save as new page in the new language. 
				$element = $this->richTextElements->GetElement(
						$element->name, 
						$element->category_id, 
						$this->request->data['i18n'], 
						true);

				// Set the new content. 
				$this->richTextElements->patchEntity($element, $this->request->data);
				
				if ($this->richTextElements->save($element))
				{
					$this->Flash->success(__('Your page has been created in the new language.'));
					
					// Get path for the page.
					$path = $this->categories->PathFor($element->category_id);
					$path .= $element->name;
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
					$path = $this->categories->PathFor($element->category_id);
					$path .= $element->name;
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
		$this->set(compact('element','availableLanguageCodes','implementedLanguageCodes','missingLanguages'));
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
