<?php 
/* Edit form for SimplicitySettings.
 * 
 */
?>
<h3><?= __('Add new language') ?></h3>
<p>
What actually happens when you add a new language, is that the page visit the (non-existing) start page in the selected language, thereby creating it.<br>
After that, the language will be available for selection whenever you edit a page.
</p>
<?php

if(count($presentLanguages) > 0)
{
?>
<h5><?= __('The website already has pages in the following languages') ?></h5>
<ul>
<?php
  foreach($presentLanguages as $i18n => $lang)
  {
    echo "<li>".$lang." <i>(".$i18n.")</i></li>";
  }
?>
</ul>
<?php
}

echo $this->Form->create();

echo $this->Form->input(
		'i18n',
		[
				'options' => $allLanguages,
				'label' => false,
				'escape' => false,
				'empty' => __('Select to add'),
		]);

echo $this->Form->button(__('Add language'), ['class' => 'button top-margin']);
echo $this->Form->end();
