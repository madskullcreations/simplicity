<?php
use App\Controller\EditablePagesController;
?>

<div>
  <?php 
    // debug($breadcrumbPath);
    // debug($richTextElement);
    // debug($this->Menu->GetBreadCrumb($breadcrumbPath, $richTextElement)); 
  ?>
	<?= $richTextElement->content ?>
</div>
<div>
	<?php $richTextElement->created ?>
</div>
<div>
	<?php $richTextElement->modified ?>
</div>

<?php
	// TODO: Nu ska RichTextElementsHelper komma till nytta, för den ska ha en funktion
	// för att rendera en edit-knapp. Resten sköter ju vyn om, renderingen etc.

	if($userIsAuthor)
	{
		echo $this->Html->link(
				__d("simplicity", 'Edit page'), 
				[
						'action' => 'edit', 
						$richTextElement->id
				],
				[
						'class' => 'button',
						'style' => 'margin-right: 10px;'
				]);
		
		// A postlink does not seem to be able to have "?lang=smurfiska".
		echo $this->Form->postLink(
				__d("simplicity", 'Erase page'), 
				[
						'action' => 'delete', 
						$richTextElement->id,
						'?' => ['franken' => 'stein']
				],
				[
						'class' => 'button',
						'type' => 'post',
						'confirm' => __d("simplicity", 'Are you sure?')
				]);
	}
?>