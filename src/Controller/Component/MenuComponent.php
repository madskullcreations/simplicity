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
	public $richTextElements;
	
	public function initialize(array $config)
	{
		$this->categories = TableRegistry::get('Categories');
		$this->richTextElements = TableRegistry::get('RichTextElements');
	}
	
	/* Set the $richTextElement->path for the given rte as an url-path, like 'trolls/eat/' for the page named 'snails'.
	 * 
	 */
	public function SetPathFor(&$richTextElement)
	{
		$richTextElement->path = $this->categories->PathFor($richTextElement->category_id);
	}
	
	/* Returns the given path as an array of category elements, or empty array if not the entire path exists.
	 * 
	 */
	public function GetPath($categoryNames, $language)
	{
		$elements = $this->categories->GetPath($categoryNames, $language, false, false);
    
		if($elements == null || count($elements) == 0)
			return array();
		
		foreach($elements as &$element)
		{
			$element->path = $this->_GetPath($element->id);
		}
		
		return $elements;
	}
	
	/* Returns the children of the given category, including RichTextElements. If null is given, the root-nodes are returned. 
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
	 * 		about_animals   <-This is a RichTextElement, i.e an actual page. 
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
	public function GetTree($parentCategoryId = null, $level = 0, $language = 'sv_SE')
	{
		$tree = $this->categories->GetTree($parentCategoryId, $level, $language);

		// The main difference between all() and toArray() is that all() uses 'lazy loading' while toArray() uses 'eager loading'.
		// We need the result from all() realized into an array right now, so use toArray().
		// (debug($rtes) is internally performing hocus pocus, incorporating toArray().)
		// 
		//$array = array_merge($children->toArray(), $rtes->toArray());

		$names = array();
		
		// Get the RichTextElements whose parents level is one less than the given $level.
		foreach($tree as &$category)
		{
			$names[] = $category->name;
			$this->_MergeContent($category, $level - 1);
		}
		// debug($names);
		
		// Get the RTEs for the root node.
		$rtes = $this->richTextElements->ElementsForCategory($parentCategoryId, AppController::$selectedLanguage, true);
		$rtes = $rtes->toArray();
		
		// Remove RTEs with same name as an existing category.
		foreach($rtes as $id => &$rte)
		{
			if(in_array($rte->name, $names))
			{
				unset($rtes[$id]);
			}
		}
		unset($rte);
		
		foreach($rtes as &$rte)
		{
			$rte->path = $this->_GetPath($rte->category_id);
		}
		unset($rte);
		
		$tree = array_merge($tree, $rtes);
		
		// debug($tree);

		// Get the name of the model for an object:
// 		$repository = $element->source();
// 		debug($repository);
		
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
		$category->path = $this->_GetPath($category->parent_id);
		
		$names = array();
		foreach($category->children as &$child)
		{
			$names[] = $child->name;
			$this->_MergeContent($child, $level);
		}
	
		if($category->level < $level)
		{
			$rtes = $this->richTextElements->ElementsForCategory($category->id, AppController::$selectedLanguage, true);
			$rtes = $rtes->toArray();
				
			// Remove RTEs with same name as an existing category.
			foreach($rtes as $id => &$rte)
			{
				if(in_array($rte->name, $names))
				{
					unset($rtes[$id]);
				}
			}
			unset($rte);
				
			foreach($rtes as &$rte)
			{
				$rte->path = $this->_GetPath($rte->category_id);
			}
			unset($rte);
			
			$category->children = array_merge($category->children, $rtes);
		}
	}
	
	/* Get the url path for the given category_id.
	 *
	 */
	protected function _GetPath($category_id)
	{
		return $this->categories->PathFor($category_id);
	}	
}