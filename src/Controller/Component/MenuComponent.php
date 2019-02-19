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

use App\Controller;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use App\Controller\AppController;

class MenuComponent extends Component
{
	public $categories; 
	
	public function initialize(array $config)
	{
		$this->categories = TableRegistry::get('Categories');
	}
	
  /* If you create a menu tree without RichTextElements, you can use this helper.
   * NOTE: The $name is the visible name, while $path is the actual url. This is different from RichTextElements where the elements name 
   *  are part of the url.
   * 
   * To create a menu element with no children (no sub-menu-elements below) simply call:
   *  $elm = CreateMenuElement('Stuff', 0, 'my_controller/do_stuff');
   * To create a  menu element with children elements, make sure $class_name is set to 'Categories', and give 
   * an array of $children elements:
   *  $elm = CreateMenuElement('My Top Menu Element', 0, 'my_controller/index', 'Categories', $children);
   * 
   */
  public function CreateMenuElement($title, $level, $path, $class_name = 'RichTextElements', $children = array())
  {
    $cl = [(object)['title' => $title]];
    return (object)[
      'cat_lang' => $cl,
      'title' => $title,
      'class_name' => $class_name, 
      'level' => $level, 
      'path' => $path, 
      'children' => $children];
  }
  
	/* Set the $richTextElement->path for the given rte as an url-path, like 'trolls/eat/snails' for the page named 'snails'.
	 * 
	 */
	/*public function SetPathFor(&$richTextElement)
	{
	    $richTextElement->path = $this->categories->PathFor($richTextElement->category_id, $richTextElement->i18n).$richTextElement->url_title;
	}*/
	
	/* Returns the given path as an array of category elements, or empty array if not the entire path exists.
	 * 
	 */
	public function GetPath($categoryNames, $language)
	{
		$elements = $this->categories->GetPath($categoryNames, $language, false);
    
		if($elements == null || count($elements) == 0)
			return array();
		
		foreach($elements as &$element)
		{
      // debug($element);
      $element->path = $this->_GetPath($element->id, $element->cat_lang[0]->i18n);
		}
		// debug($elements);
    
		return $elements;
	}
	
	/* Returns the children of the given category. If null is given, the root-nodes are returned. 
	 * If level is greater than 0, it is branched down 'level' childrens down. 
	 * 
	 * Example: A tree three levels deep:
	 * 	fruits
	 * 		apple
	 * 			yellow
	 * 			black
	 * 		pear
	 * 			hairy
	 * 			stiff
	 * 	animals
	 * 		about_animals 
	 * 		cat
	 * 			hungry
	 * 			purring
	 * 		salmon
	 * 			swimming
	 * 			dead
	 * 
	 * Getting the children of the animals category, with level 0, would return
	 * 	cat, salmon, about_animals
	 * 
	 * If level=1 (or greater in this case), the entire sub tree would come: 
	 * 		cat
	 * 			hungry
	 * 			purring
	 * 		salmon
	 * 			swimming
	 * 			dead
	 * 	
	 */
	public function GetTree($parentCategoryId, $level, $language, $includeNotInMenus)
	{
    // Just make it deep enough.
    if($level == 0)
      $level = 1000;
    
    $tree = $this->categories->GetTree($parentCategoryId, $level, $language, $includeNotInMenus);
    // debug($tree);

		// The main difference between all() and toArray() is that all() uses 'lazy loading' while toArray() uses 'eager loading'.
		// We need the result from all() realized into an array right now, so use toArray().
		// (debug($rtes) is internally performing hocus pocus, incorporating toArray().)
		// 
		//$array = array_merge($children->toArray(), $rtes->toArray());

		$names = array();
		
		// Get the RichTextElements whose parents level is one less than the given $level.
		foreach($tree as &$category)
		{
      // If the category is not translated to current language, we skip adding it.
      if(count($category->cat_lang) > 0)
      {
        $names[] = $category->cat_lang[0]->url_title;
        $category->url_title = $category->cat_lang[0]->url_title;
        $this->_MergeContent($category, $level - 1);
        
        $category->class_name = $category->source();
      }
		}
    unset($category);
		// debug($names);
		
		return $tree;
	}
	
	
	/* Returns array with all categories at a given level in the tree.
	 * 
	 *  Example: A tree four levels deep: 
	 *   	cat1		cat2		cat3 		<-Level 0, categories where parent category is null
	 *   	sub1		sub2		sub3		<-Level 1, categories whose parent category is right above them.
	 *   	zub1		zub2		zub3		<-Level 2, same here.
	 * 		cub1		cub2		cub3		<-Level 3, same here. 
	 * 		
	 * Calling GetLevel(2) will return an array with all the zub elements. 
	 * Note that any category can have any number of childrens, so there could be more than 3 zub elements in this example. 
	 * 
	 */
	public function GetLevel($level)
	{
		// TODO:
		
		// TODO: Also get the RichTextElements whose parents level is one less than the given $level.
	}
	

	/* Recursively merge in pages on each level in the tree.
	 *
	 */
	protected function _MergeContent(&$category, $level)
	{
    // debug($category);
    
    // If the category is not translated to current language, we skip adding it.
    if(count($category->cat_lang) == 0)
      return;
    
    $category->path = $this->_GetPath($category->parent_id, $category->cat_lang[0]->i18n);
		$category->path .= $category->cat_lang[0]->url_title;
    
		$names = array();
		foreach($category->children as &$child)
		{
      // BUG: When translating an existing page, the corresponding category is never created!
      if(count($child->cat_lang) > 0)
      {
        $names[] = $child->cat_lang[0]->url_title;
      }
      
      $this->_MergeContent($child, $level);
      $child->class_name = $child->source();
		}
    unset($child);
    // debug($names);
	}
	
	/* Get the url path for the given category_id.
	 *
	 */
	protected function _GetPath($category_id, $language)
	{
      if($category_id === null)
        return '/';
    
		return $this->categories->PathFor($category_id, $language);
	}	
}