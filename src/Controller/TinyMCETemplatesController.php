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

use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * TinyMCETemplates controller
 * 
 */
class TinyMCETemplatesController extends AppController
{
	public function initialize()
	{
		parent::initialize();
	}
  
  public function beforeFilter(Event $event)
  {
    parent::beforeFilter($event);
    
    $this->Auth->allow(['index']); // Visitors can index all templates.
  }

  public function index()
  {
    $path = func_get_args();
    
    $templateName = array_pop($path);
    
    $file = APP.'Template'.DS.'TinyMCETemplates'.DS.$templateName.'.ctp';
    // debug($file);

    // We want only the html produced by the .ctp file to be rendered, no layouts, no nothing.
    $this->viewBuilder()->setLayout("ajax");
    
    if (file_exists($file)) 
    {
      // debug("File exists");
      $this->render($templateName);
    }
    else 
    {
      $this->render('default');
    }
  }  
}




