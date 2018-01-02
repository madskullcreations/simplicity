<?php
namespace App\View\Helper;

use App\Controller\AppController;
use Cake\View\Helper;
use Cake\View\Helper\HtmlHelper;

/* See AppView where it is used in place of the original HtmlHelper.
 * 
 */
class SimplicityHtmlHelper extends HtmlHelper
{
	/* Simply add the selected language to the link as an url parameter.
	 * 
	 */
	public function link($title, $url = null, array $options = [])
	{
// 		debug($title);
// 		debug($url);
// 		debug($options);
		
		if($url == null)
		{
			$url = array();
		}

		// Url is sometimes a string, as in '#' when posting. 
		if(is_array($url))
		{
			if(!isset($url['?']))
			{
				$url['?'] = array();
			}
			if(!isset($url['?']['lang']))
			{
				// selectedLanguage is always set.
				$url['?']['lang'] = AppController::$selectedLanguage;
			}
		}
		else 
		{
			$url .= '?lang='.AppController::$selectedLanguage;
		}
// 		debug($url);
		
		return parent::link($title,$url,$options);
	}
}