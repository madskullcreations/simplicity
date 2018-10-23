<h3>Simplicity installation page</h3>
<div class="callout primary">
  <h5>All done!</h5>
  <p>Click the login-link below to start the journey.</p>
  <?php
    echo $this->Html->link(
        'Login to create your first page!',
        ['controller' => 'Users', 'action' => 'login']
    );
  ?>
</div>
