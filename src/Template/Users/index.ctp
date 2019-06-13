<?php 
/* Listing users.
 * 
 */
?>

<div class="users index">
  <?= $this->Html->link(
		__d("simplicity", 'Add user account'),
		[
				'controller' => 'Users',
				'action' => 'add',
		],
		[
				'class' => 'button float-right top-margin',
		]);
  ?>
  <?= $this->Flash->render() ?>
  <h3><?= __d("simplicity", "User listing") ?></h3>
  <?php
    foreach($users as $user)
    {
      // debug($user);
      // debug($slork);
      
  ?>
      <p><?= $user->username ?></p>
  <?php
    }
  ?>
</div>