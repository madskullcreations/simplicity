<h3>Simplicity installation page</h3>
<div class="callout primary">
  <h5>Welcome to the Simplicity one-page installation page!</h5>
  <p>Please fill in the connection details for connecting to a database, and select an administrator user name for your web page.</p>
  <ul>
    <li>You need </li>
  </ul>
</div>
<?php
echo $this->Form->create();

echo $this->Form->controls(
  [
  'Database.database' => ['label' => 'Database Name', 'title' => 'The name of the database'],
  'Database.username' => ['label' => 'Database Username', 'title' => 'This should be the user name with full access to the database'],
  'Database.password' => ['label' => 'Database Password', 'title' => 'The password for the given user'],
  ],
  ['legend' => 'Database connection details']
  );

echo $this->Form->controls(
  [
  'User.email' => ['label' => 'Email', 'title' => 'Your email. If you ever forget the password it can be sent to this email address'],
  'User.username' => ['label' => 'Username', 'title' => 'The user name of your web page administrator account'],
  'User.password' => ['label' => 'Password', 'title' => 'Please choose a long password which are difficult to guess'],
  ],
  ['legend' => 'Administrator user']
  );

echo $this->Form->submit('Submit', ['class' => 'button top-margin']);
echo $this->Form->end();
