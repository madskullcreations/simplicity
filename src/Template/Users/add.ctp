<?php 
/* Add form for Users.
 * 
 */
?>

<h1><?= __d("simplicity", "Add new user") ?></h1>

<?php
    echo $this->Form->create($user);
?>
<legend><?= __d("simplicity", "Add new user") ?></legend>
<?php
    echo $this->Form->control('username', ['label' => __d("simplicity", "Username")]);
    echo $this->Form->control('password', ['label' => __d("simplicity", "Password")]);
    
    echo $this->Form->control(
      'role', 
      [
        'label' => __d("simplicity", "Role"),
        'options' => [
          'admin' => __d("simplicity", 'Administrator'), 
          'author' => __d("simplicity", 'Author')]
      ]
    );
    
    echo $this->Form->button(__d("simplicity", 'Create user'), ['class' => 'button top-margin']);
    echo $this->Form->end();
?>