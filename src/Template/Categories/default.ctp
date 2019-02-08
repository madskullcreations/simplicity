<?php
use App\Controller\CategoriesController;
?>

<div>
  <?php 
    // debug($breadcrumbPath);
    // debug($categoryElement);
    // debug($categoryElement->cat_lang[0]->content);
  ?>
	<?= $categoryElement->cat_lang[0]->content ?>
</div>
<div>
	<?php 
    // echo $categoryElement->created;
  ?>
</div>
<div>
	<?php 
    // echo $categoryElement->modified;
  ?>
</div>

<?php
	if($userIsAuthor)
	{
		echo $this->Html->link(
				__('Edit page'), 
				[
						'action' => 'edit', 
						$categoryElement->id,
            $selectedLanguage
				],
				[
						'class' => 'button',
						'style' => 'margin-right: 10px;'
				]);
		
		echo $this->Form->postLink(
				__('Erase page'), 
				[
						'action' => 'deleteElement', 
						$categoryElement->id,
            $selectedLanguage
				],
				[
						'class' => 'button',
            'style' => 'margin-right: 10px;',
						'type' => 'post',
						'confirm' => __('Are you sure?')
				]);
    
    if(isset($missingLanguages) && count($missingLanguages) > 0)
    {
      echo $this->Html->link(
        __('Translate page'),
        [
            'action' => 'add_new_language',
            $categoryElement->id,
            $selectedLanguage
        ],
        [
            'class' => 'button',
        ]);
    }
	}
?>