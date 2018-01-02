<?php
namespace App\View\Helper;

use Cake\View\Helper;

class MenuHelper extends Helper
{
	// Lathund för cake3.
	// http://cake3.codaxis.com/#html-helper

	public $helpers = ['Html'];
	
	/* Render the html for the bread crumb url path.
	 *  
	 */
	public function GetBreadCrumb($path, $richTextElement, $ulClass = 'simplicity breadcrumbs', $liClass = 'crumb')
	{
		$html = '';
		
		$html .= '<ul class="'.$ulClass.'">';
		
		for($i=0; $i<count($path); $i++)
		{
			$element = &$path[$i];
			
			$html .= '<li class="">';
			$html .= $this->Html->link($element->name, $element->path);
			$html .= '</li>';
		}
		
		$html .= '<li class="'.$liClass.' current">';
		$html .= $this->Html->link($richTextElement->name, $richTextElement->path.$richTextElement->name);
		$html .= '</li>';
		$html .= '</ul>';
		
		return $html;
	}
	
	/* Accordion - Fancy word for a top-down menu with collapsible sub-menus. 
	 * 
	 */
	public function GetAccordionMenu($menuTree, $ulClass = 'simplicity accordion', $subUlClass = 'menu vertical nested', $liClass = 'simplicity')
	{
		$html = '';
		
		$html .= '<ul class="'.$ulClass.' vertical menu root level_1" aria-autoclose="false" data-accordion-menu>';
		$first = 'first';
		foreach($menuTree as &$element)
		{
			$html .= $this->_GetMenu($element, $subUlClass, $liClass, $first, 1);
			$first = '';
		}
		$html .= '</ul>';
		
		return $html;
	}
	
	/* Render the html for the given menu tree. 
	 * 
	 */
	public function GetMenu($menuTree, $ulClass = 'simplicity', $subUlClass = 'menu', $liClass = 'simplicity')
	{
		$html = '';
				
		$html .= '<ul class="'.$ulClass.' root level_1" aria-autoclose="false" data-dropdown-menu>';
		$first = 'first';
		foreach($menuTree as &$element)
		{
			$html .= $this->_GetMenu($element, $subUlClass, $liClass, $first, 1);
			$first = '';
		}
		$html .= '</ul>';
		
		return $html;
	}
	
	/* Recursively build the menu out of <ul> and <li> elements.
	 * 
	 */
	protected function _GetMenu(&$element, $ulClass, $liClass, $first, $level)
	{
		$html = '<li class="'.$liClass.' '.$first.' level_'.$level.'">';
		
		$repository = $element->source();
		
// TODO: css 'active_page' for the page currently active. 

		if($repository == 'Categories')
		{
// TODO: Det kan ju finnas varianter på denna funktion: (och GetMenu() så klart) 
//  En som lägger in ett 'kryss' framför, så man kan stänga en kategori som har barn i sig.
//  En som funkar som denna gör nu. (Denna är bra för att bygga css för en left-to-right meny högst opp på sidan)
// OBS: Kryss-grejen måste du så klart kolla upp om det inte finns en härlig js/css plugin som du kan använda. 
//   <-Vägra bygga saker som redan finns. 

			$html .= $this->Html->link($element->name.' - '.$element->level, $element->path.$element->name);
			
			if(count($element->children) > 0)
			{
				$html .= '<ul class="'.$ulClass.' child level_'.($level + 1).'">';
				$first = 'first';
				foreach($element->children as &$child)
				{
					$html .= $this->_GetMenu($child, $ulClass, $liClass, $first, $level + 1);
					$first = '';
				}
				$html .= '</ul>';
			}
		}
		else // RichTextElements 
		{
			$html .= $this->Html->link($element->name, $element->path.$element->name);
		}
		
		$html .= '</li>';
	
		return $html;
	}
}
