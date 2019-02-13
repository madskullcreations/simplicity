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
    
    if(count($missingLanguages) > 0)
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