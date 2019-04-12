<?php 
/* Edit form for SimplicitySettings.
 * 
 */
?>
<h3><?= __d("simplicity", 'Add new language') ?></h3>
<p>
<?= __d("simplicity", 'When you add a new language, you will be redirected to the home page with the chosen language selected, where you will be asked to translate it.'); ?><br>
<?= __d("simplicity", 'After you translate and save the page, the language will be available for selection whenever you edit a page.'); ?>
</p>
<?php

if(count($presentLanguages) > 0)
{
?>
<h5><?= __d("simplicity", 'The website already has pages in the following languages') ?></h5>
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
				'empty' => __d("simplicity", 'Select to add'),
		]);

echo $this->Form->button(__d("simplicity", 'Add language'), ['class' => 'button top-margin']);
echo $this->Form->end();
