<?php
	if($userIsAdmin)
	{
?>
<h1><?= __('Page does not exist') ?></h1>
<p><?= __('To create this page, click the button.') ?></p>
<?php
    echo $this->Form->create();
    echo $this->Form->hidden('doCreate', ['value' => 'yes']);
    echo $this->Form->button(__('Create Page'), ['class' => 'button']);
    echo $this->Form->end();
  }
?>