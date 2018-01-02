<?php 
/* Edit form for EditablePages.
 * 
 */

echo $this->TinyMCE->GetScript();
?>

<?php 
	echo $this->Html->link(
		__('Add new language'),
		[
				'controller' => 'SimplicitySettings',
				'action' => 'edit',
		],
		[
				'class' => 'button float-right top-margin',
		]);
?>

<h1><?= __("Edit Page") ?></h1>

<p>
	<?php 
		echo __('The page\'s current language is: ').'"'.$availableLanguageCodes[$element->i18n].'"'; 
	?>
	<br>
	This page is available in the following languages: TODO: Ett gäng flaggor som länkar till resp. sidas edit sida. 
</p>
<?php
    echo $this->Form->create($element);

    if(count($missingLanguages) > 0)
    {
	    echo $this->Form->label(
	    		'i18n', 
	    		__('This page is missing in the following languages').' [?]', 
	    		[
	    				'title' => __('To create the page for a new language; Select a language below, edit and save. This will be saved as a new page.')
	    		]);
	    echo $this->Form->input(
	    		'i18n', 
	    		[
	    				'options' => $missingLanguages, 
	    				'label' => false,
	    				'empty' => __('Select to create a new page in the choosen language...'),
	    		]);
    }
    else
    {
    	echo '<p>'.__('This page is available in every language for this site. To add a new language to the site, please click "Add new language" above.').'</p>';
    }
    
    echo $this->Form->input('content');
    echo $this->Form->button(__('Save Page'), ['class' => 'button top-margin']);
    echo $this->Form->end();
?>