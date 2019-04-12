<?php 
/* Edit form for SimplicitySettings.
 * 
 */
?>
<style>
  .label-after{
    font-style: italic; 
    margin-left: 30px; 
    margin-bottom: 20px;
  }
  .input select, .input input{
    margin: 2px;
  }
</style>
<h3><?= __d("simplicity", 'Various settings') ?></h3>
<?php

echo $this->Form->create();

echo $this->Form->input(
  'default_language',
  [
    'options' => $presentLanguages,
    'label' => __d("simplicity", 'Select default language'),
    'escape' => false,
    'default' => $selectedLanguage
  ]);
    
// Works, but would be nicer with a template for the FormHelper.
echo $this->Form->label('default_language', __d("simplicity", 'When a user visit the site for the first time, this is the language he or she will see.'), ['class' => 'label-after']);

echo $this->Form->input(
  'site_title', 
  [
    'label' => __d("simplicity", 'Set your website\'s title.'),
    'default' => $this->fetch('simplicity_site_title')
  ]);
echo $this->Form->label('site_title', __d("simplicity", 'The title are shown in the webpage header section and in the browsers tab or window title. Do not use any html tags here.'), ['class' => 'label-after']);

echo $this->Form->input(
  'simplicity_site_description', 
  [
    'label' => __d("simplicity", 'Set your website\'s description.'),
    'default' => $this->fetch('simplicity_site_description')
  ]);
echo $this->Form->label('simplicity_site_description', __d("simplicity", 'The description are shown below the site title.'), ['class' => 'label-after']);

echo $this->element(
  'LayoutSelector', 
  [
    'defaultLayout' => $defaultLayout, 
    'layoutFiles' => $layoutFiles,
    'showCallout' => false,
    'label' => __d("simplicity", 'Set default layout')
  ]);
echo $this->Form->label('layout', __d("simplicity", 'This will be the default layout when you create a new page. It will not change the layout for already created pages.'), ['class' => 'label-after']);

// See if you can use this with the contact form zurb validation:
// https://book.cakephp.org/3.0/en/views/helpers/form.html#displaying-errors    
// if ($this->Form->isFieldError('default_language')) 
// {
  // echo $this->Form->error('default_language', 'Completely custom error message!');
// }

echo $this->Form->button(__d("simplicity", 'Save changes'), ['class' => 'button top-margin']);
echo $this->Form->end();
