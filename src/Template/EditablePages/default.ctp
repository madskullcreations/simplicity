<?php

use App\Controller\EditablePagesController;

$this->start('simplicity_top_menu');
	echo $this->Menu->GetMenu($homeTree, 'dropdown menu header-subnav', 'simplicity menu');
	//echo $this->Menu->GetMenu($homeTree, 'simplicity dropdown menu', 'simplicity menu');
$this->end();
$this->start('simplicity_side_menu');
	echo '<h4>Context menu</h4>';
	echo $this->Menu->GetAccordionMenu($tree); 
$this->end();
$this->start('simplicity_breadcrumbs');
	echo $this->Menu->GetBreadCrumb($breadcrumbPath, $element);
$this->end();
$this->start('simplicity_page_name');
	// A bit odd, but to use a utility, we must give full path. 
	echo Cake\Utility\Inflector::camelize($element->name);
$this->end();

//debug($element->identifier);
?>

<div>
  <?php 
    // debug($breadcrumbPath);
    // debug($element);
    // debug($this->Menu->GetBreadCrumb($breadcrumbPath, $element)); 
  ?>
	<?= $element->content ?>
</div>
<div>
	<?php $element->created ?>
</div>
<div>
	<?php $element->modified ?>
</div>

<?php
	// TODO: Nu ska RichTextElementsHelper komma till nytta, för den ska ha en funktion
	// för att rendera en edit-knapp. Resten sköter ju vyn om, renderingen etc.

	if($userIsAuthor)
	{
		echo $this->Html->link(
				__('Edit page'), 
				[
						'action' => 'edit', 
						$element->id,
						'?' => ['korvar' => '42']
				],
				[
						'class' => 'button',
						'style' => 'margin-right: 10px;'
				]);
		
		// A postlink does not seem to be able to have "?lang=smurfiska".
		echo $this->Form->postLink(
				__('Erase page'), 
				[
						'action' => 'delete', 
						$element->id,
						'?' => ['franken' => 'stein']
				],
				[
						'class' => 'button',
						'type' => 'post',
						'confirm' => __('Are you sure?')
				]);
	}
?>