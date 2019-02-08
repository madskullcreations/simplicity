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
 
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class LanguageComponent extends Component
{
	public $catLangs;
	
	public function initialize(array $config)
	{
	    $this->catLangs = TableRegistry::get('CatLang');
	}
	
	/* Returns all languages present at the site. 
	 * 
	 */
	public function GetLanguageCodes()
	{
	    return $this->catLangs->GetLanguageCodes();
	}
	
	/* Returns all languages for the given category id.
	 * 
	 */
	public function GetLanguagesFor($categoryId)
	{
	    return $this->catLangs->GetLanguagesFor($categoryId);
	}
	
	/* Returns languages the given category id does not exist in.
	 * 
	 */
	public function GetMissingLanguages($categoryId)
	{
	    return $this->catLangs->GetMissingLanguages($categoryId);
	}
}