<?php
	if($userIsAdmin)
	{
?>
<h1><?= __d("simplicity", 'Page does not exist') ?></h1>
<p><?= __d("simplicity", 'To create this page, click the button.') ?></p>
<?php
    echo $this->Form->create();
    echo $this->Form->hidden('doCreate', ['value' => 'yes']);
    echo $this->Form->button(__d("simplicity", 'Create Page'), ['class' => 'button']);
    echo $this->Form->end();
  }
?>