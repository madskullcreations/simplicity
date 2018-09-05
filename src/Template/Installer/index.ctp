<h3>Simplicity installation page</h3>
<div class="callout primary">
  <h5>Welcome to the Simplicity one-page installation page!</h5>
  <p>Please fill in the connection details for connecting to a database, and select an administrator user name for your web page.</p>
  <ul>
    <li>For the administrator - Please give a valid email to enable the ability to retrieve a lost password</li>
  </ul>
</div>
<?php
echo $this->Form->create($form);

if($showResetTablesSetting)
{
?>
<div class="callout warning">
  <p>Setup has detected the database tables already exists, but some step seem to be missing. If you know the database tables does not contain any important data (any data you have created, like pages) you can let setup destroy and recreate the tables for you.</p>
  <p>NOTE! All data will be erased, and there are no undo!</p>
</div>
<?php

  echo $this->Form->controls(
    [
    'db_recreate_tables' => ['label' => 'Destroy and recreate Simplicity database tables', 'type' => 'checkbox'],
    ],
    ['legend' => 'Troubleshooting']
    );
}

echo $this->Form->controls(
  [
  'db_database' => ['label' => 'Database Name', 'title' => 'The name of the database'],
  'db_username' => ['label' => 'Database Username', 'title' => 'This should be the user name with full access to the database'],
  'db_password' => ['label' => 'Database Password', 'title' => 'The password for the given user', 'type' => 'password'],
  ],
  ['legend' => 'Database connection details']
  );

echo $this->Form->controls(
  [
  'user_email' => ['label' => 'Email', 'title' => 'Your email. You will use it to login. If you ever forget the password it can be sent to this email address'],
  'user_password' => ['label' => 'Password', 'title' => 'Please choose a long password which are difficult to guess', 'type' => 'password'],
  ],
  ['legend' => 'Administrator user']
  );

echo $this->Form->submit('Submit', ['class' => 'button top-margin']);
echo $this->Form->end();
?>
<script>
/*if(typeof jQuery != 'undefined')
{
  // jQuery is loaded => print the version
  alert(jQuery.fn.jquery);
}
$(function(){
  alert("Hello from jQuery!");
});*/
</script>