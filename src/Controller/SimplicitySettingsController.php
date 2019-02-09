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

use Cake\Controller;
use Cake\ORM\TableRegistry;

/**
 * Settings controller
 *
 * 
 */
class SimplicitySettingsController extends AppController
{
  public function index()
  {
  }
  
	public function language()
	{
		if ($this->request->is(['post', 'put']))
		{
			// debug($this->request->data);
			
			$i18n = $this->request->data['i18n'];
			
	  // A minor trick for the create_from_url page of Categories: Make it show the new language.
      $kitchenSink = TableRegistry::get('KitchenSink');
      $kitchenSink->Store('LanguageToAdd', $i18n);

      // Try fetch home page, in the first language created.
      $this->categories = TableRegistry::get('Categories');
      $categoryElement = $this->categories->GetElement(null, 'home', $i18n);
      // debug($i18n);
      // debug($categoryElement);
      
      if($categoryElement == null)
      {
        // User has either deleted home page, or has just installed Simplicity. Create an empty page.
        return $this->redirect(['controller' => 'Categories', 'action' => 'create_from_url', 'home']);
      }
      else
      {
        // Edit page, asking user to translate to the newly selected language.
        return $this->redirect(['controller' => 'Categories', 'action' => 'edit', $categoryElement->id, $i18n]);
      }
		}
		
		// Get every single language available.
		$languages = TableRegistry::get('Languages');
		$allLanguages = $languages->GetVariants('', true, true);
		// debug($allLanguages);
		
		// Get already present languages.
		$catLangs = TableRegistry::get('CatLang');
		$presentLanguages = $catLangs->GetLanguageCodes();
		// debug($presentLanguages);
		
		// Remove present languages from the all list. 
		$allLanguages = array_diff_key($allLanguages, $presentLanguages);
		// debug($allLanguages);
		
		// Add the i18n code to the value, so user can write 'se' to quickly get to the entries for Sweden. 
		foreach($allLanguages as $i18n => &$lang)
		{
			// TODO: Looks terrible, a better idea? 
			$pad = 10 - strlen($i18n);
			for($i=0;$i<$pad;$i++)
			{
				$i18n .= '&nbsp;';
			}

			$lang = $i18n.'&nbsp;'.$lang;
		}
		
		$this->set(compact('allLanguages','presentLanguages'));
	}
}
