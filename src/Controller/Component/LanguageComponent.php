<?php 

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
	
	/* Returns all languages for the given page name.
	 * 
	 */
	public function GetLanguagesFor($rteName, $categoryId)
	{
		return $this->richTextElements->GetLanguagesFor($rteName, $categoryId);
	}
	
	/* Returns languages the give page name does not exist in.
	 * 
	 */
	public function GetMissingLanguages($rteName, $categoryId)
	{
		return $this->richTextElements->GetMissingLanguages($rteName, $categoryId);
	}
}