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
				'action' => 'language',
		],
		[
				'class' => 'button float-right top-margin',
		]);
?>

<h1><?= __("Edit Page") ?></h1>

<p>
	<?php 
		echo __('The page\'s current language is').': "'.$availableLanguageCodes[$element->i18n].'"'; 
	?>
	<br>
  <?php
    if(in_array($element->i18n, $implementedLanguageCodes))
    {
      unset($implementedLanguageCodes[$element->i18n]);
    }
    
    if(count($implementedLanguageCodes) > 0)
    {
      echo __('This page is available in the following languages').': ['.implode(',', $implementedLanguageCodes).']';
    }
  ?>
</p>
<?php
    echo $this->Form->create($element);

    if(count($missingLanguages) > 0)
    {
	    echo $this->Form->label(
	    		'i18n', 
	    		__('This page is missing in the following languages').' ['.implode(',', $missingLanguages).']', 
	    		[
	    				'title' => __('To create the page for a new language; Select a language below, edit and save. This will be saved as a new page.')
	    		]);
          
      $options = [
	    				'options' => $missingLanguages, 
	    				'label' => false,
	    				'empty' => __('Select to create a new page in the choosen language...'),
	    		];
          
	    echo $this->Form->input(
	    		'i18n', 
          $options);
    }
    else
    {
    	echo '<p>'.__('This page is available in every language for this site. To add a new language to the site, please click "Add new language" above.').'</p>';
    }
    
    $arr = ['title' => __('The url title is visible in the browsers address bar.')];
    if($element->url_title == 'home' && $element->category_id === null)
    {
      echo '<p class="callout">'.__('You cannot change the Url Title of the starting page. It must always have the name "home". You can always change the Title, which is the part visible in menus.').'</p>';
      
      $arr['disabled'] = 'disabled';
    }

    echo $this->Form->input('url_title', $arr);    
    echo $this->Form->input('title', ['title' => __('The title is visible in the menus.')]);
    
    echo $this->Form->input('content');
    echo $this->Form->button(__('Save Page'), ['class' => 'button top-margin']);
    echo $this->Form->end();
?>