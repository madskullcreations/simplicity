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
use RuntimeException;

// TODO: All queries, more or less, fetch all rows from table, while in some cases only a fraction is needed. 
//   Update, and check the cake debug kits sql query tab.

/****
 * A Category is a part of the url, like the 'flowers' in the url mysite.now/flowers/the_rose?lang=EN-en
 * 
 * The Categories can be nested endlessly by the help of the Tree-behaviour, so an url like this: 
 *  mysite.now/space/solar-system/earth/sweden/stockholm-city?lang=SV-se
 *  would create the following tree-structure with 4 elements:
 *    space
 *    	solar-system
 *    		earth
 *    			sweden
 *    
 *    And at last, there would be a RichTextElement named stockholm-city in the language SV-se, with it's parent
 *    category set to sweden.
 *    
 *    The magic happens in the EditablePagesController, as CategoriesTable merely store and retreive the tree-structure.
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
	 * Returns the immediate childrens of the given category, or all root-categories if null is given. 
	 * 
	 * This can be used to fetch the immediate sub-menu-items for the currently active menu.
	 * 
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
	public function GetTree($categoryId = null, $deep = 1, $language = 'sv_SE')
	{
		if($deep < 1)
			throw new RuntimeException("Parameter deep cannot be less than one.");
		
		if($categoryId != null)
		{
			$rootElement = $this->find()->where(['id' => $categoryId])->first();
// 			debug($rootElement);
			
			$level = $rootElement->level;
// 			debug($level + $deep);
			
			// This nicely give me an array with children down to the level specified.
			// path, children, treeList <-are the 'implementedFinders' for TreeBehaviour.
/*			$query = $this->find('treeList', [
					'keyPath' => 'id',
					'valuePath' => 'name',
					'spacer' => ' '
			])
			->where([
					'level <=' => $level + $deep, 
					'parent_id >=' => $categoryId
			])
			->toArray();
*/
			
			// This is not working as I expect it to; I assume the element to climb up 3 steps in the tree-structure,
			// but nothing at all happens.
			// debug($this->moveUp($rootElement, 3));
			
			// This is a bit experimental, but the idea is to limit the selection of children to those whose level is in bounds.
			$tree = $this->find('children', ['for' => $categoryId])
        ->contain(['CatLang'])
				->where([
						'level <=' => $level + $deep
				])
				// Get only the fields we want incorporates id and parent_id for 'threaded' to work. 
		    ->find('threaded', ['fields' => ['parent_id','id','level']])
				->toArray();
		}
		else 
		{
			// The Tree behaviour don't seem to support getting 'children' where parent_id is null.  
			$rootElements = $this->find()
				->contain(['CatLang'])
        ->where([
						'parent_id is' => null,
						'level <=' => $deep
				])
				->find('all', ['fields' => ['parent_id','id','level']])
				->toArray();
				
			$tree = array();
			foreach($rootElements as &$rootElement)
			{
				// debug($rootElement);
				
				if($deep > 1)
				{
					$subTree = $this->GetTree($rootElement->id, $deep - 1, $language);
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
	 * 
	 * If createIfNotExist is set to true, the path will be constructed if it does not yet exist.
	 */
	public function GetPath(Array $path, $language, $lastChildOnly = true, $createIfNotExist = true)
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
				if($createIfNotExist)
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
				else
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
      // ALTER TABLE `categories` ADD `i18n` VARCHAR(12) NOT NULL DEFAULT 'sv_SE' COLLATE 'utf8_unicode_ci' AFTER `level`;
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
	protected function _CreateCategory($parent_id, $url_title, $language)
	{
		$element = $this->newEntity();
		$element->parent_id = $parent_id;
		    		
    $result = $this->save($element);
		if($result)
		{
			// debug("Saved");
     
      // Create corresponding CatLang-row for the given language.
      $this->_CreateCatLang($element, $result->id, $url_title, $language);
      // $catLang = $this->CatLang->newEntity();
      // $catLang->category_id = $result->id;
      // $catLang->i18n = $language;
      // $catLang->url_title = $url_title;
      // $catLang->title = $url_title;      
      // $this->CatLang->link($element, [$catLang]);

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
  protected function _CreateCatLang($category, $categoryId, $url_title, $language)
  {
    // Create corresponding CatLang-row for the given language.
    $catLang = $this->CatLang->newEntity();
    $catLang->category_id = $categoryId;
    $catLang->i18n = $language;
    $catLang->url_title = $url_title;
    $catLang->title = $url_title;
    
    $this->CatLang->link($category, [$catLang]);
  }
  
	// DONE: Använder Tree, som är en icke-rekursiv funktion för att skapa en trädstruktur i databasen. 
	// 		Den kan med en enda query ta fram alla children för vilken del av trädet som helst. 
	//    Med en annan query kan man lika enkelt ta fram 'path to a node', tex. red-roses ger plants/roses/red-roses.
	//    Medelst en enkel liten loop kan man visuellt återskapa ett träd. 
	// En källa: 
	// http://www.sitepoint.com/hierarchical-data-database-2/
	// TreeBehaviour docs: 
	// http://book.cakephp.org/3.0/en/orm/behaviors/tree.html
	
	//     Språktaggen i18n i RichTextElement medger att jag kan ha samtliga språkversioner av 
	//     RödaRosor, RedRoses, RosasRojos, osv. med samma parent_id. 
	//     Då blir det bajseligt lätt att se på vilka språk en sida finns: GetLanguagesFor($name, $parentId)
	//		  OBS: Hela detta tänket förutsätter att det är ok att en url är flowers/roses/the_red_french_rose,
	//      på alla språk. 
	//       Det finns alltså tre språkversioner av RichTextElement med identifier "the_red_french_rose", och 
	//       parent_id pekar på Category "roses". En kategory är således språklös. 
	//    ..en användare är fortfarande fri att skapa kategorier på olika språk, och ange identifier på varje språk,
	//    men dels förlorar han möjligheten att se om sidan finns på alla språk, dels tror jag att det är svårt att
	//    ens försöka få in franska i en url, och både Category och RichTextElement.identifier är en del av urlen. 
	//    ..så min rekommendation för alla som vill använda mitt system är att ha samma url till samma sida, på
	//    samtliga språk. 
}
