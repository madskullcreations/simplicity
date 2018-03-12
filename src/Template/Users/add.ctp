<?php 
/* Add form for Users.
 * 
 */
?>

<h1><?= __("Add new user") ?></h1>

<?php
    echo $this->Form->create($user);
?>
<legend><?= __("Add new user") ?></legend>
<?php
    echo $this->Form->control('username');
    echo $this->Form->control('password');
    
    echo $this->Form->control(
      'role', 
      [
        'options' => [
          'admin' => __('Administrator'), 
          'author' => __('Author')]
      ]
    );
    
    echo $this->Form->button(__('Create user'), ['class' => 'button top-margin']);
    echo $this->Form->end();
?>