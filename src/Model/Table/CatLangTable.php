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

/****
 * 
 * 
 */
class CatLangTable extends Table
{
	public function initialize(array $config)
	{
    // A category language belongs to one category. (A category can have many catlangs.)
    $this->belongsTo('Categories');
    
		$this->addBehavior('Timestamp');
	}
	
  /**
   * Returns the CatLang element for the given category and language.
   * 
   */
  public function GetElement($categoryId, $i18n)
  {
    $element = $this->find()
    ->where(['category_id' => $categoryId, 'i18n' => $i18n])
    ->first();
    
    return $element;
  }
  
  /**
   * Returns nr of translations for the given category id.
   * 
   */
  public function CatLangCount($categoryId)
  {
    $count = $this->find()
    ->where(['category_id' => $categoryId])
    ->count();
    
    return $count;
  }
  
  /**
   * Returns array of url_titles for the given category, i.e. all translated versions of the given page's url title.
   * Keyed with i18n.
   * 
   */
  public function GetUrlTitlesFor($categoryId)
  {
		$urlTitles = $this->
				find('list', ['keyField' => 'i18n', 'valueField' => 'url_title'])->
				select(['CatLang.i18n','CatLang.url_title'])->
				where(['category_id' => $categoryId])->
				toArray();
		// debug($urlTitles);

		return $urlTitles;
  }  
  
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
 						 innerJoin(['Language' => 'languages'], ['Language.i18n = CatLang.i18n'])->
						 group('CatLang.i18n')->
						 order(['CatLang.i18n']);
				
		$all = $query->toArray();
		// debug($query);
		// debug($all);
				
		return $all;
	}
    
	/* Return array of language codes the given category id exists in.
	 *  
	 * This is useful for the administrator of a multi-language site, so he can see in 
	 * which languages the current page exists.
	 * 
	 * To get which languages the page does not exists, subtract the two arrays, 
	 * from GetLanguageCodes() and GetLanguagesFor(). 
	 *  
	 */
	public function GetLanguagesFor($categoryId)
	{
    // NOTE: In the keyField and valueField, do not put 'CatLang.i18n', put only 'i18n', otherwise null values are created..
		$query = $this->
				find('list', ['keyField' => 'i18n', 'valueField' => 'Language.long_name'])->
				select(['CatLang.i18n','Language.long_name'])->
				innerJoin(['Language' => 'languages'], ['Language.i18n = CatLang.i18n'])->
				where(['CatLang.category_id' => $categoryId]);
		
    // debug($query);
    
    $languages = $query->toArray();
    // debug($categoryId);		
		// debug($languages);
		
		return $languages;
	}
  
  /* Returns array of language codes for which the given url title is missing. (not translated to)
   */
	public function GetMissingLanguages($pageId)
	{
		$presentLanguages = $this->GetLanguagesFor($pageId);
		$allLanguages = $this->GetLanguageCodes();
		
		$res = array_diff($allLanguages, $presentLanguages);
		// debug($res);
		
		return $res;
	}
  
  /**
   * Drop the table from the database.
   */
	public function DropTable($connection)
  {
    $connection->execute("DROP TABLE `cat_lang`;");
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    // Some explanations:
    //  The page wheels in hamsters/runs/in/wheels are available in english and swedish.
    //  The url_title in english are "wheels" and "hjul" in swedish.
    //  So, to identify the page's available translations, the category_id comes in handy. 
    //  category_id is the same for the two pages.
    // 
    // uk_category_id_url_title_i18n - Makes sure that no two pages has the same url_title and the same ancestor 
    //   in the same language. Both the swedish and english pages might have the same url_title "wheels", since they
    //   have different languages. But it is impossible to create two "wheels" pages with the 
    //   ancestor hamsters/runs/in/ in the same language.
    //   (you can still create hamsters/runs/on/wheels, it has another ancestor)
    //
    // uk_category_id_i18n - One translation per language per category.
    // 
    $connection->execute("
      CREATE TABLE `cat_lang` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `category_id` INT(10) NOT NULL,
      `i18n` VARCHAR(12) NOT NULL,
      `url_title` VARCHAR(128) NOT NULL,
      `title` VARCHAR(128) NOT NULL,
      `content` MEDIUMTEXT NULL,
      `created` DATETIME NULL,
      `modified` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_category_id_i18n` (`category_id`, `i18n`),
      UNIQUE KEY `uk_category_id_url_title_i18n` (`category_id`, `url_title`, `i18n`)
      )
      CHARACTER SET utf8
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB
      ROW_FORMAT=COMPACT;
    ");
  }
}
