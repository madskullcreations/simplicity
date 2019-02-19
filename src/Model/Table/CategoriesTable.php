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
 
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Query;
use RuntimeException;

/****
 * A Category is a part of the url, like the 'flowers' in the url mysite.now/flowers/the_rose?lang=EN-en
 * 
 * The Categories can be nested endlessly by the help of the Tree-behaviour, so an url like this: 
 *  mysite.now/space/solar-system/earth/sweden/stockholm-city?lang=SV-se
 *  would create the following tree-structure with 5 elements:
 *    space
 *    	solar-system
 *    		earth
 *    			sweden
 *            stockholm-city
 *    
 * Each Category might contain a page. In this example sweden might describe the country Sweden and link
 * to it's different cities.
 * It is also possible to leave a page completely blank, which simply means it will act as a folder 
 * for it's children elements. The category sweden might in this case be a folder for it's cities.
 *    
 */
 
/****
 * Translating a page
 * 
 * "some_url_path" + "thisuniquepage" + "en_GB" will load the english version of the page "thisuniquepage". 
 * "some_url_path" + "thisuniquepage" + "sv_SE" will load the swedish version.
 * 
 * When you translate a page to another language you can also rename the url-path, the connection between the pages
 * will remain through the CatLang.category_id. 
 * This means a visitor can switch between a pages different language-versions easily, and you as web page manager
 * can keep track of the translated versions easily.
 * 
 * Example: Translate "some_url_path" + "thisuniquepage" into spanish:
 *  "un_camino_url" + "esa_pagina", and it's text of course. 
 * This will create a new row in CatLang, but refer to the same Categories row via CatLang.category_id.
 * So, every page has exactly one row in Categories, but one row for each translated language in CatLang.
 * 
 */
class CategoriesTable extends Table
{
	public function initialize(array $config)
	{
    // A category can have many catlangs.
    $this->hasMany('CatLang');
    
		$this->addBehavior('Timestamp');
		
		// We want the level, or deep, saved along with the category. 
		$this->addBehavior('Tree', ['level' => 'level']);
	}
  	
	/**
	 * Returns the childrens of the given category down to the given deep, 
   * or all root-categories if null is given as categoryId. 
	 * 
   * If $includeNotInMenus is true, all pages are included, otherwise only pages where in_menus are set to 1 
   * will be included in the three.
   * 
	 * This can be used to fetch the immediate sub-menu-items for the currently active menu.
	 * 
	 * 1. Denna Helper/Component ska ha en MainMenu() som helt enkelt tar alla rot-element. 
	 * 2. Den ska också ha SubMenu($categoryId) som kort o gott anropar GetChildren() nedan. 
	 * 3. CrossLevelMenu($level) skapar en meny med categories med den angivna leveln. 
	 * 		<-Här är det viktigt att jag tänker ut ett praktiskt exempel, jag kommer inte på något nu dock.. :)
	 * <-Det stora arbetet denna Helper/Component har är att den också ska ta ut alla sidor. (RichTextElements)
	 *   En meny ska självklart grena ner till enskilda sidor. 
	 *   
	 *  EX: Om jag visar sidan belse/bubbels/sprayflaska, så vill jag i menyn se alla sidor och kategorier under
	 *      belse/bubbels/. Detta är vad som kallas en submeny. 
	 *  EX: Huvudmenyn är fortfarande alla sidor och kategorier utan förälder.
	 *  
	 */
	public function GetTree($categoryId, $deep, $language, $includeNotInMenus)
	{    
		if($categoryId != null)
		{
			$rootElement = $this->find()->where(['id' => $categoryId])->first();
			// debug($rootElement);
			
			$level = $rootElement->level;
					
      $where = ['level <=' => $level + $deep];
      if($includeNotInMenus == false)
      {
        // Visitors only see pages meant for the menu.
        $where['in_menus'] = '1';
      }

			// Limit the selection of children to those whose level is in bounds.
      // Fetch the CatLang for the given language.
			$tree = $this->find('children', ['for' => $categoryId])
        ->contain(['CatLang' => ['conditions' => ['CatLang.i18n' => $language]]])
				->where($where)
				// Get only the fields we want incorporates id and parent_id for 'threaded' to work. 
		    ->find('threaded', ['fields' => ['parent_id','id','level']])
				->toArray();
		}
		else 
		{
      $where = ['parent_id is' => null, 'level <=' => $deep];
      if($includeNotInMenus == false)
      {
        // Visitors only see pages meant for the menu.
        $where['in_menus'] = '1';
      }
      
			// The Tree behaviour don't seem to support getting 'children' where parent_id is null.  
			$rootElements = $this->find()
				->contain(['CatLang' => ['conditions' => ['CatLang.i18n' => $language]]])
        ->where($where)
				->find('all', ['fields' => ['parent_id','id','level']])
				->toArray();
				
			$tree = array();
			foreach($rootElements as &$rootElement)
			{
				// debug($rootElement);
				
				if($deep > 1)
				{
				  $subTree = $this->GetTree($rootElement->id, $deep - 1, $language, $includeNotInMenus);
					// debug($subTree);
					
					$rootElement->children = $subTree;
				}
				else 
				{
					$rootElement->children = array();
				}
				
				$tree[] = $rootElement;
			}
		}
		
		return $tree;
	}
	
	/* Returns the given path as an array of category elements, the first element being the root element,
	 * and the last element being the innermost child element.  
	 * 
	 * If lastChildOnly is set, only the innermost element is returned. 
	 */
	public function GetPath(Array $path, $language, $lastChildOnly = true)
	{
		if(count($path) == 0)
			return null;

    // debug($path);
    // debug($language);
    
		$categoryPath = array();
			
		// Find (or create) the elements in the path.
		$lastCategory = null;
		while($url_title = array_shift($path))
		{
			// Look for child of $lastCategory with the given url_title. 
			if($lastCategory == null)
			{
				// Looking for a root category, no parent. 
				$category = $this->find()
          ->contain('CatLang')
          ->innerJoinWith('CatLang')
          ->where([
            'parent_id is' => null,
            'CatLang.url_title' => $url_title,
            'CatLang.i18n' => $language,
          ])
          ->first();
			}
			else 
			{
				// Looking for a child category.
				$category = $this->find()
          ->contain('CatLang')
          ->innerJoinWith('CatLang')
          ->where([
            'parent_id' => $lastCategory->id,
            'CatLang.url_title' => $url_title,
            'CatLang.i18n' => $language,
          ])
          ->first();
			}
			// debug($category);
			
			if($category == null)
			{
				/*if($createIfNotExist)
				{
          // Now lets see if the category exist, but the requested language are missing!
          // This might be the case when creating a path for a new language.
          // We want the category, and add a new CatLang for the new language.
          if($lastCategory == null)
          {
            // Looking for a root category, no parent. 
            $category = $this->find()
              ->contain('CatLang')
              ->innerJoinWith('CatLang')
              ->where([
                'parent_id is' => null,
                'CatLang.url_title' => $url_title
              ])
              ->first();
          }
          else 
          {
            // Looking for a child category.
            $category = $this->find()
              ->contain('CatLang')
              ->innerJoinWith('CatLang')
              ->where([
                'parent_id' => $lastCategory->id,
                'CatLang.url_title' => $url_title
              ])
              ->first();
          }
          // debug($category);
          
          if($category != null)
          {
            // Score! Let's add the new CatLang for the new language.
            $this->_CreateCatLang($category, $category->id, $url_title, $language);
          }
          else
          {
            // Nope, it must be a new path. Create the element.
            if($lastCategory == null)
            {
              $category = $this->_CreateCategory(null, $url_title, $language);
            }
            else
            {
              $category = $this->_CreateCategory($lastCategory->id, $url_title, $language);
            }
            // debug($category);
          }
				}
				else*/
				{
					// Could not find all parts (or none) of the path, and we are not allowed to create it.
					return null;
				}
			}
			
			// Found or created, here it is. 
			$lastCategory = $category;
			
			if($lastChildOnly == false)
			{
				$categoryPath[] = $category;
			}
		}
    // debug($categoryPath);
		
		if($lastChildOnly)
		{
			return $lastCategory;
		}
		else 
		{
			return $categoryPath;
		}
	}

  /**
   * Knowing the id of the category, fetch the page content for the given language.
   * If i18n is left null, all translated (CatLang's) versions of the page are loaded. 
   *
   */
  public function GetElementById($categoryId, $i18n = null)
  {
    // Noteworthy reading: https://book.cakephp.org/3.0/en/orm/retrieving-data-and-resultsets.html#passing-conditions-to-contain
    
    // This becomes two different queries! One fetching the row from the categories, 
    // and one fetching the row(s) from cat_lang.
    // 
    $query = $this->find('all', 
      [
        'fields' => ['Categories.id', 'Categories.parent_id', 'Categories.in_menus', 'Categories.layout']
      ])
      ->contain(['CatLang' => function(Query $q) use($i18n){
        if($i18n != null)
        {
          // Filter out the one language version.
          $q = $q->where(['CatLang.i18n' => $i18n]);
          // debug($q);
          return $q;
        }
        else
        {
          // Fetch all language versions.
          return $q;
        }
      }])
      ->where(['Categories.id' => $categoryId]);
    // debug($query);

    $category = $query->first();
    // debug($category);
    
    return $category;
  }
  
  /**
   * Knowing the parent category (or null) looks for the given url_title, ignoring the language.
   * Returns element id(s), or null if it does not exist.
   * 
   */
  public function GetElementByUrlTitle($parentCategoryId, $urlTitle)
  {
    $where = [
      'CatLang.url_title' => $urlTitle,
    ];
    
    if($parentCategoryId == null)
    {
      $where['parent_id is'] = null;
    }
    else
    {
      $where['parent_id'] = $parentCategoryId;
    }
    
    $category = $this->find('all',
        [
          'fields' => ['id', 'parent_id', 'layout']
        ])
          // Small note: Docs says it is 'easy' to select which fields to include. Hardly easy. :)
      ->contain(['CatLang' => 
        [
          'fields' => [
            'CatLang.category_id'
          ]
        ]])
      ->innerJoinWith('CatLang')
      ->where($where)
      ->all()
      ->toList();
      
    // debug($category);
    // debug($parentCategoryId);
    // debug($urlTitle);
    
    if(count($category) == 0)
    {
      return null;
    }
    else
    {
      return $category;
    }
  }
  
  /**
   * Returns the category with the given url_title, language and parent category id.
   */
  public function GetElement($parentCategoryId, $urlTitle, $i18n)
  {
    $where = [];
    $where['url_title'] = $urlTitle;
    $where['i18n'] = $i18n;
    
    $query = $this->find('all')
            // NOTE: Can't find a way to select all fields from CatLang and some fields from Categories.
            ->select(['Categories.id', 'Categories.parent_id', 'Categories.layout', 'CatLang.id', 'CatLang.category_id', 'CatLang.url_title', 'CatLang.i18n', 'CatLang.title', 'CatLang.content', 'CatLang.created', 'CatLang.modified'])
            // ->select($this->CatLang, $this->CatLang->Categories)
            // ->contain('CatLang')
            ->join([
                'CatLang' => [
                    'table' => 'cat_lang',
                    'type' => 'inner',
                    'conditions' => 'Categories.id = CatLang.category_id',
                ]
            ])
            ->where($where);
    // debug($query);

    $category = $query->first();
    // debug($category);
    
    if($category != null)
    {
      // Making an inner join creates an array for some reason. Convert into an object.
      $category->cat_lang = [];
      $category->cat_lang[] = (object)$category->CatLang;
      unset($category->CatLang);
      // debug($category);
    }
    
    return $category;
    
    // matching() would work if it was not because it was so buggy.
    /*if($parentCategoryId == null)
    {
      $where['parent_id is'] = null;
    }
    else
    {
      $where['parent_id'] = $parentCategoryId;
    }
    
    $query = $this->find('all', 
      [
        'fields' => ['Categories.id', 'Categories.parent_id']
      ])
      ->matching('CatLang', function(Query $q) use($i18n, $urlTitle){
        $q = $q->where(['CatLang.i18n' => $i18n, 'CatLang.url_title' => $urlTitle]);
        
        // BUG: Without this debug() enabled, the result will be empty.
        // debug($q);
        
        return $q;
      })
      ->where($where);
    // debug($query);
    
    $category = $query->all();
    debug($category);
    
    // Since we are using matching() the format are a bit different. Rename.
    $catLang = $category->_matchingData['CatLang'];
    // debug($catLang);
    $category->cat_lang = [$catLang];
    unset($category->_matchingData);
    
    // debug($category);
    return $category;*/
    
    
    
    // contain() would return all rows where parent_id matches, then the cat_langs matching each of them.
    /*$where = [];
    
    // null is null but it is not null.
    if($parentCategoryId == null)
    {
      $where['parent_id is'] = null;
    }
    else
    {
      $where['parent_id'] = $parentCategoryId;
    }
        
    $query = $this->find('all', 
      [
        'fields' => ['Categories.id', 'Categories.parent_id']
      ])
      ->contain(['CatLang' => function(Query $q) use($i18n, $urlTitle){
        $q = $q->where(['CatLang.i18n' => $i18n, 'CatLang.url_title' => $urlTitle]);
        debug($q);
        return $q;
      }])
      ->where($where);
    debug($query);

    // Since we might get several rows with Categories with the same parent_id.
    // contain() is not an inner join, so the 'inner' query for the CatLang gets performed for each resulting row in Categories.
    // ...so contain() is not working for this case.
    $category = $query->all();
    return $category;*/
      

    // join() kinda works, and is also the solution I settled for at the end.
    /*
    $where = [];
    $where['url_title'] = $urlTitle;
    $where['i18n'] = $i18n;
    
    // NOTE: Can't get it to work with contain() or innerJoinWith(). (Complains about CatLang is not connected..)
    // NOTE: This kinda works, but the joined CatLang becomes an array instead of an object. 
    $query = $this->find('all')
            // NOTE: Can't find a way to select all fields from CatLang and some fields from Categories.
            ->select(['Categories.id', 'Categories.parent_id', 'CatLang.id', 'CatLang.category_id', 'CatLang.url_title', 'CatLang.i18n', 'CatLang.title', 'CatLang.content', 'CatLang.created', 'CatLang.modified'])
            // ->select($this->CatLang, $this->CatLang->Categories)
            // ->contain('CatLang')
            ->join([
                'CatLang' => [
                    'table' => 'cat_lang',
                    'type' => 'inner',
                    'conditions' => 'Categories.id = CatLang.category_id',
                ]
            ])
            ->where($where);
    // debug($query);

    $category = $query->first();
    // debug($category);
    
    return $category;*/
  }
    
  /**
   * Returns true if the given category exists, in the given language.
   * 
   */
// TODO: Testa.   
  public function CategoryExists($parentCategoryId, $urlTitle, $i18n = null)
  {
    $params = [
      'parent_id' => $parentCategoryId,
      'CatLang.url_title' => $urlTitle
    ];
    
    if($i18n != null)
    {
      $params['CatLang.i18n'] = $i18n;
    }
    
    $count = $this->find('all')
      ->contain(['CatLang'])
      ->innerJoinWith('CatLang')
      ->where($params)
      ->count();
      
    if($count > 0)
      return true;
    
    return false;
  }
  
// not tested	
  /* Returns true if the requested language exists for the given category id.
   */
  public function CatLangExists($category_id, $language)
  {
    $category = $this->find()
      ->contain('CatLang')
      ->innerJoinWith('CatLang')
      ->where([
        'Categories.id' => $category_id,
        'CatLang.i18n' => $language
      ])
      ->first();
      
    debug($language);
    debug($category);
  }
  
  /**
   * Create or update a category. Add a CatLang with the given language and url_title.
   * Returns the created/updated category.
   * 
   */
  public function CreateCategory($parentCategoryId, $requestData)
  {
    $i18n = $requestData['i18n'];
    $urlTitle = $requestData['url_title'];
    $title = $requestData['title'];
    $content = $requestData['content'];
    $layout = $requestData['layout'];
    $inMenus = $requestData['in_menus'];

    return $this->_CreateCategory($parentCategoryId, $urlTitle, $i18n, $title, $content, $inMenus, $layout);
    
    // if($this->CategoryExists($parentCategoryId, $urlTitle, $i18n) == false)
    // {
      // // Create the category.
      
      // $category = $this->GetElementById($categoryId, $i18n);
    // }
    // else
    // {
      // $category = $this->GetElement($parentCategoryId, $urlTitle, $i18n);
    // }
  }
  
  /* Creates a new url-title for the existing category id.
   * If it already exists, nothing happens.
   * 
   */
  public function CreateCatLangForCategory($category_id, $url_title, $language, $title, $content)
  {
    $category = $this->find()
      ->where([
        'Categories.id' => $category_id,
      ])
      ->first();
    
    if($category == null)
    {
      return;
    }

    // Let's add the new CatLang for the new language.
    $this->_CreateCatLang($category, $category->id, $url_title, $language, $title, $content);
  }
  
	/* Returns the path down to root from the given category in the form of "fancy/path/to/", where "to" is the $category_id.
	 * 
	 */
	public function PathFor($category_id, $language)
	{
    // debug($category_id);
    // debug($language);
    
		if($category_id === null)
			return "/";
		
		// This is a nice shortcut for getting all parents down to the root. 
		$crumbs = $this->find('path', ['for' => $category_id]);
      // ->contain(['CatLang'])
      // ->innerJoinWith('CatLang')
      // ->where(['CatLang.i18n' => $language]);
    // debug($crumbs);

		$path = "/";
		foreach($crumbs as $crumb)
		{
      // debug($crumb->id);
      
      $catLang = $this->CatLang->find()
        ->where([
          'CatLang.category_id' => $crumb->id, 
          'CatLang.i18n' => $language
        ])
        ->select(['url_title'])
        ->first();
      // debug($catLang);
      
			$path .= $catLang->url_title."/"; 
		}
    // debug($path);
				
		return $path;
	}

  /**
   * Delete the category, with any connected CatLang´s.
   */
  public function DeleteElement($category_id)
  {
    $category = $this->get($category_id);
    $this->delete($category);
  }
  
  /**
   * Drop the table from the database.
   */
	public function DropTable($connection)
  {
    $connection->execute("DROP TABLE `categories`;");
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    $connection->execute("
      CREATE TABLE `categories` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `parent_id` INT(10) NULL,
      `lft` INT(10) NOT NULL,
      `rght` INT(10) NOT NULL,
      `level` INT(10) NOT NULL,
      `layout` VARCHAR(32) NULL,
      `in_menus` TINYINT(1) NOT NULL DEFAULT '1',
      `created` DATETIME NULL,
      `modified` DATETIME NULL,
      PRIMARY KEY (`id`)
      )
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB
      ROW_FORMAT=COMPACT;
    ");
    
    // TODO: UpdateTable() ? With some version parameter, so it can perform changes between versions.
    // If you ever need to update an old database, this one works.
    // $connection->execute("
      // ALTER TABLE `categories` ADD `layout` VARCHAR(32) NULL AFTER `level`;
      // ALTER TABLE `categories` ADD `in_menus` TINYINT(1) NOT NULL DEFAULT '1' AFTER `layout`;
    // ");
  }

	/* Tries to find the given category by its url_title and category.
	 * Returns null if not found.
	 * 
	 */
	protected function _FindCategory($parent_id, $url_title, $language)
	{
		if($parent_id == null)
		{
			// null is so damn special in sql... (almost like infinity and infinity + 1, they are not equal, but both are infinite. Well infinity never equals.)
			$element = $this->find()
      ->contain(['CatLang'])
      ->innerJoinWith('CatLang')
			->where([
        'parent_id is ' => null,
        'CatLang.url_title' => $url_title, 
        'CatLang.i18n' => $language
      ])
      ->first();
		}
		else
		{
			$element = $this->find()
      ->contain(['CatLang'])
      ->innerJoinWith('CatLang')
			->where([
        'parent_id' => $parent_id,
        'CatLang.url_title' => $url_title, 
        'CatLang.i18n' => $language
      ])
			->first();
		}
		    
		return $element;
	}
	
	/**
	 * Create a category with the given parent. It must not exist when calling this function.
	 * 
	 */
	protected function _CreateCategory($parent_id, $url_title, $language, $title, $content, $inMenus, $layout)
	{
		$element = $this->newEntity();
		$element->parent_id = $parent_id;
    $element->layout = $layout;
    $element->in_menus = $inMenus;
        		
    $result = $this->save($element);
		if($result)
		{
      // Create corresponding CatLang-row for the given language.
      $this->_CreateCatLang($element, $result->id, $url_title, $language, $title, $content);

      // Once created, lets read it back in.
      $element = $this->_FindCategory($parent_id, $url_title, $language);
      // debug($element);
      
      return $element;
		}
		else
		{
			// debug("Not saved");
      
      return null;
		}
	}

  /**
   * Add a new language to the given category. 
   */
  protected function _CreateCatLang($category, $categoryId, $url_title, $language, $title = null, $content = null)
  {
    // Create corresponding CatLang-row for the given language.
    $catLang = $this->CatLang->newEntity();
    $catLang->category_id = $categoryId;
    $catLang->i18n = $language;
    $catLang->url_title = $url_title;
    $catLang->title = $url_title;
    $catLang->content = $content;

    if($title != null)
    {
      $catLang->title = $title;
    }
    
    $this->CatLang->link($category, [$catLang]);
  }
  
	// DONE: Använder Tree, som är en icke-rekursiv funktion för att skapa en trädstruktur i databasen. 
	// 		Den kan med en enda query ta fram alla children för vilken del av trädet som helst. 
	//    Med en annan query kan man lika enkelt ta fram 'path to a node', tex. red-roses ger plants/roses/red-roses.
	//    Medelst en enkel liten loop kan man visuellt återskapa ett träd. 
	// One source: 
	// http://www.sitepoint.com/hierarchical-data-database-2/
	// TreeBehaviour docs: 
	// http://book.cakephp.org/3.0/en/orm/behaviors/tree.html
}
