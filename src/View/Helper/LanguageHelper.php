<?php
namespace App\View\Helper;

use Cake\View\Helper;

class LanguageHelper extends Helper
{
	public function GetFlags($languageArray) 
	{
		// TODO: Produce a link-collection with flags or language name from the given array.
		// <-Den ska använda i första hand hela i18n fältet för att hitta flaggan, och om den inte finns, använd de första två tecknen.
		//   Om inget finns, använd hela namnet istället.
	}
}
