<?php
	if($userIsAuthor)
	{
		echo $this->Html->link(
				__d("simplicity", 'Edit page'), 
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
				__d("simplicity", 'Erase page'), 
				[
						'action' => 'deleteElement', 
						$categoryElement->id,
            $selectedLanguage
				],
				[
						'class' => 'button',
            'style' => 'margin-right: 10px;',
						'type' => 'post',
						'confirm' => __d("simplicity", 'Are you sure?')
				]);
    
    if(count($missingLanguages) > 0)
    {
      echo $this->Html->link(
        __d("simplicity", 'Translate page'),
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