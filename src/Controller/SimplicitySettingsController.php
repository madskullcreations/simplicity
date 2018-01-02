<?php
/**
 * Simplicity (https://github.com/Snorvarg/simplicity)
 * Copyright (c) Jon Lennryd (http://jonlennryd.madskullcreations.com)
 *
 * Licensed under The MIT License
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
	public function edit()
	{
		if(AppController::UserIsAdmin() == false)
		{
			$this->Flash->error(__('You do not have access permission.'));
			return $this->redirect('/');
		}
		
		if ($this->request->is(['post', 'put']))
		{
			// debug($this->request->data);
			
			$i18n = $this->request->data['i18n'];
			
			return $this->redirect('/?lang='.$i18n);
		}
		
		// Get every single language available.
		$languages = TableRegistry::get('Languages');
		$allLanguages = $languages->GetVariants('', true, true);
		// debug($allLanguages);
		
		// Get already present languages.
		$richTextElements = TableRegistry::get('RichTextElements');
		$presentLanguages = $richTextElements->GetLanguageCodes();
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
