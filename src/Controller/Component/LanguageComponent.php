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
	public $richTextElements;
	
	public function initialize(array $config)
	{
		$this->richTextElements = TableRegistry::get('RichTextElements');
	}
	
	/* Returns all languages present at the site. 
	 * 
	 */
	public function GetLanguageCodes()
	{
		return $this->richTextElements->GetLanguageCodes();
	}
	
	/* Returns all languages for the given page id.
	 * 
	 */
	public function GetLanguagesFor($pageId)
	{
		return $this->richTextElements->GetLanguagesFor($pageId);
	}
	
	/* Returns languages the given page id does not exist in.
	 * 
	 */
	public function GetMissingLanguages($pageId)
	{
		return $this->richTextElements->GetMissingLanguages($pageId);
	}
}