<?php 
/* Listing users.
 * 
 */
?>

<div class="users index">
  <?= $this->Flash->render() ?>
  <h3>User listing</h3>
  <?php
    foreach($users as $user)
    {
      // $user contains the hashed password. (Not anymore since I specify which fields I want.)
      
      // ..but toArray will remove it since it Entity User specifies this.
      // $slork = $user->toArray();
      // debug($user);
      // debug($slork);
      
  ?>
      <p><?= $user->username ?></p>
  <?php
    }
  ?>
</div>