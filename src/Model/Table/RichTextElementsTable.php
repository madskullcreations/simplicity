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

class RichTextElementsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
	}
	
	/* 
	 * "some_url_path" + "thisuniquepage" + "en_GB" will load the english version of the page "thisuniquepage". 
	 * "some_url_path" + "thisuniquepage" + "sv_SE" will load the swedish version.
	 * 
	 * What if you want different url for the same page in Spanish? 
	 * Simple: "some_url_path" + "paginaunico" + "es_ES" will be unique. 
	 * 
	 * Of course, if you have the same page name but a different url, it will be another page:  
	 * "another_url_path" + "thisuniquepage" + "en_GB"
	 * 
	 * NOTE: What is lost here is the connection between different language-versions of a page.. 
	 *    ..I guess that is a trade-off between simplicity and total control. It is a no-fix at the moment at least, but in the future
	 *    it can be acheived by a manual connection between different pages, like a 'group' field in the table.
	 * 
	 */

	/* Returns all the languages present on the site.
	 * Format: array keyed on language code, value: long name.
	 * 
	 */
	public function GetLanguageCodes()
	{
		// The select() is necessary, otherwise the find() gets confused and leave long_name to null. 
		// Solution: http://stackoverflow.com/questions/34326611/innerjoin-in-cakephp-3-returns-no-rows
		$query = $this->
						 find('list', ['keyField' => 'i18n', 'valueField' => 'Language.long_name'])->
						 select(['i18n','Language.long_name'])->
 						 innerJoin(['Language' => 'languages'], ['Language.i18n = RichTextElements.i18n'])->
						 group('RichTextElements.i18n')->
						 order(['RichTextElements.i18n']);
				
		$all = $query->toArray();
		// debug($query);
		// debug($all);
				
		return $all;
	}	
	
	/* Return array of language codes the given page name exists in.
	 *  
	 * This is useful for the administrator of a multi-language site, so he can see in 
	 * which languages the current page exists.
	 * 
	 * To get which languages the page does not exists, subtract the two arrays, 
	 * from GetLanguageCodes() and GetLanguagesFor(). 
	 *  
	 */
	public function GetLanguagesFor($name, $categoryId = null)
	{
		if($categoryId == null)
		{
			$where = ['name' => $name, 'category_id is' => null];
		}
		else 
		{
			$where = ['name' => $name, 'category_id' => $categoryId];
		}
		
		$languages = $this->
				find('list', ['keyField' => 'i18n', 'valueField' => 'Language.long_name'])->
				select(['i18n','Language.long_name'])->
				innerJoin(['Language' => 'languages'], ['Language.i18n = RichTextElements.i18n'])->
				where($where)->
				toArray();
				
		// debug($languages);
		
		return $languages;
	}
	
	public function GetMissingLanguages($name, $categoryId = null)
	{
		$presentLanguages = $this->GetLanguagesFor($name, $categoryId);
		$allLanguages = $this->GetLanguageCodes();
		
		// TODO: subtrahera från allLanguages, använd i edit().
		$res = array_diff($allLanguages, $presentLanguages);
		debug($res);
		
		return $res;
	}
	
	/* Get all elements in the given language, regardless of parent.
	 * 
	 */
	public function ElementsForLanguage($i18n)
	{
		$conditions = array();
		$conditions['i18n'] = $i18n;
		
		// TODO: Query not updated, fix sometimes. :)
		$query = $this->
						find('list', ['valueField' => 'name', 'groupField' => 'i18n', 'conditions' => $conditions])->
						order(['i18n','name']);
								
		$all = $query->toArray();

		debug($all);
		
		return $all;
	}
  
	/* Returns the elements with the given parent category and language.
	 * 
	 */
	public function ElementsForCategory($categoryId, $i18n = null, $compact = false)
	{
		if($compact)
		{
			$fields = ['name','id','category_id'];
		}
		else 
		{
			// null means fetch all fields. 
			$fields = null;
		}
		
		if($categoryId != null)
		{
			$where = ['category_id' => $categoryId];
		}
		else
		{
			$where = ['category_id is' => null];
		}
		
		if($i18n != null)
		{
			$where['i18n'] = $i18n;
		}
		
		$elements = $this->find('all', ['fields' => $fields])
			->where($where)
			->all();
		
		return $elements;
	}
	
	/* The default way of identifying a rich text element is by it's url. 
	 * Routing is setup to reroute "a/path/to/thisuniquepage?lang=sv-SE" 
	 * into "editable_pages/display/a/path/to/thisuniquepage?lang=sv-SE". 
	 * So the name of this page would be "thisuniquepage".
	 * 
	 * $categoryId in the same example would point to the "to" category.  
	 * 
	 * If $i18n is set, it should follow the i18n standards, like 'en_GB' for British english.
	 * In the same example the url parameter 'lang' is extracted, which is 'sv_SE'
	 * 
	 * The three parts, categoryId + name + i18n forms a unique id.
	 * In the same example it would be "to" + "thisuniquepage" + "sv_SE".
	 * It means that "thisuniquepage" can exist in several languages.
	 * It also means that the name "thisuniquepage" can exist on different paths, 
	 * like "some/other/path/to/thisuniquepage", or "/thisuniquepage".  
	 * 
	 * If $createIfNotExist is true, an empty element will be created if it does not already exists.
	 *  
	 */
	public function GetElement($name, $categoryId = null, $i18n = '', $createIfNotExist = true)
  {
		$element = $this->_Get($name, $categoryId, $i18n);
		    
    if($element == null && $createIfNotExist)
    {
      // First time visit indeed, let's create an empty text element and return it.
      $element = $this->newEntity();
      $element->category_id = $categoryId;
      $element->name = $name;
      $element->i18n = $i18n;
      $element->content = '';
      
      if($this->save($element))
      {
      	// debug("Saved");
      }
      else
      {
      	// debug("Not saved");
      }
          	      
      // Once created, lets read it back in.
      $element = $this->_Get($name, $categoryId, $i18n);
    }
    
    // debug($element);
    
    return $element;
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    $connection->execute("
CREATE TABLE `rich_text_elements` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL COLLATE 'utf8_unicode_ci',
  `category_id` INT(10) NULL,
  `i18n` VARCHAR(12) NOT NULL COLLATE 'utf8_unicode_ci',
  `content` MEDIUMTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`created` DATETIME NULL,
	`modified` DATETIME NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_category_id_name_i18n` (`category_id`, `name`,`i18n`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
ROW_FORMAT=COMPACT;
    ");
  }
  
  /* Load and returns the element if it exists, otherwise returns null.
   * 
   */
  protected function _Get($name, $categoryId, $i18n)
  {
  	// Learning as we go:
  	//  The find() returns a $query object, which can go through any number of permutations by calling
  	//  different functions.
  	//  The actual database query is not executed until calling first() or find().
  	  
  	if($categoryId != null)
  	{
  		$element = $this->find()
  		->where(['category_id' => $categoryId, 'name' => $name, 'i18n' => $i18n])
  		->first();
  	}
  	else
  	{
  		// null is null, but null != null.
  		$element = $this->find()
  		->where(['category_id is' => null, 'name' => $name, 'i18n' => $i18n])
  		->first();
  	}
  	
  	return $element;
  }
}  
