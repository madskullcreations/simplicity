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
  <p>NOTE! All data will be erased, and it can not be restored!</p>
  <p>However, if you have a database with content but somehow lost the app.php file, you should first of all look for app.old.php or app.old_1.php, and so on. This setup always save away the app.php so chances are good the original settings reside in one of these files. If you find one old version with the settings saved, rename it to app.php and reload this page. Do not post this form again, press the 'Reload page' button below.</p>
  
  <p>If there are no app.old.php, or it don't contain any settings, you must manually edit the app.php file and fill in the database connection details. This is fairly straight forward: </p>
  <ul>
    <li>Copy app.default.php and name it app.php.</li>
    <li>Open app.php and search for __SALT__ and replace with a random string of characters, at least 32 characters.</li>
    <li>Search for __DB_USERNAME__ and replace with the database user name. Replace both occurrences.</li>
    <li>Replace __DB_PASSWORD__ and __DB_DATABASENAME__ with password and database name.</li>
    <li>Replace __DB_ENCODING__ and __DB_TIMEZONE__ with utf8 and UTC.</li>
    <li>Save file. Reload this page by clicking the 'Reload page' button below.</li>
  </ul>
  <div class="button" onclick="window.location.href ='/';">Reload page</div>
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