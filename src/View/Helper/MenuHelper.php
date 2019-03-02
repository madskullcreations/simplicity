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
	public function GetBreadCrumb($path, $ulClass = 'simplicity breadcrumbs', $liClass = 'crumb')
	{
    // debug($path);
    
		$html = '';
		
		$html .= '<ul class="'.$ulClass.'">';
		
		for($i=0; $i<count($path); $i++)
		{
			$element = &$path[$i];
			
      $class = '';
      if($i+1 == count($path))
        $class = $liClass.' current';
      
			$html .= '<li class="'.$class.'">';
			$html .= $this->Html->link($element->cat_lang[0]->title, $element->path);
			$html .= '</li>';
		}
		
		// $html .= '<li class="'.$liClass.' current">';
		// $html .= $this->Html->link($richTextElement->title, $richTextElement->path);
		// $html .= '</li>';
    
		$html .= '</ul>';
		
		return $html;
	}
	
	/* Accordion - Fancy word for a top-down menu with collapsible sub-menus. 
	 * 
	 */
	public function GetAccordionMenu($menuTree, $ulClass = 'simplicity-accordion-menu', $subUlClass = 'menu vertical nested', $liClass = 'simplicity')
	{
		$html = '';
		
		$html .= '<ul class="'.$ulClass.' accordion vertical menu root level_1" aria-autoclose="false" data-accordion-menu>';
		$first = 'first';
		foreach($menuTree as &$element)
		{
      if($element->class_name == 'Categories')
        $element->title = '∘ '.$element->cat_lang[0]->title;
    
			$html .= $this->_GetMenu($element, $subUlClass, $liClass, $first, 1);
			$first = '';
		}
		$html .= '</ul>';

    $html .= '
      <script>
        $(".'.$ulClass.'").find("a").click(function(){
          $(this).unbind("click");
          $(this).addClass("fancy-link");
        });
      </script>';
		
		return $html;
	}
	
	/* Render the html for the given menu tree, with some zurb responsiveness for the menu 
   * so it collapses into a nice button on small devices.
	 * 
	 */
	public function GetMenu($menuTree, $menuId, $ulClass = 'simplicity', $subUlClass = 'menu', $liClass = 'simplicity')
	{
		$html = '';
				
    $html .= '
      <div class="title-bar" data-responsive-toggle="'.$menuId.'" data-hide-for="medium" style="display: none;">
        <button class="menu-icon" type="button" data-toggle="'.$menuId.'"></button>
        <div class="title-bar-title">Menu</div>
      </div>    
    ';
        
		$html .= '<ul id="'.$menuId.'" class="'.$ulClass.' root level_1" aria-autoclose="false" data-dropdown-menu>';
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
	public function GetSimpleMenu($menuTree, $menuId, $ulClass = 'simplicity', $subUlClass = 'menu', $liClass = 'simplicity')
	{
		$html = '';

		$html .= '<ul id="'.$menuId.'" class="'.$ulClass.' root level_1" data-dropdown-menu>';
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
   * $element must be an object with the following properties:
   *   string name, 
   *   string class_name, // Should be 'Categories' to be a tree-element with children elements.
   *   string level, 
   *   string path, 
   *   array  children.
	 */
	protected function _GetMenu(&$element, $ulClass, $liClass, $first, $level)
	{
		$html = '<li class="'.$liClass.' '.$first.' level_'.$level.'">';
		
		if($element->class_name == 'Categories')
		{
			$html .= $this->Html->link($element->cat_lang[0]->title, $element->path);
			
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
			$html .= $this->Html->link($element->title, $element->path, ['class' => 'fancy-link']);
		}
		
		$html .= '</li>';
	
		return $html;
	}
}
