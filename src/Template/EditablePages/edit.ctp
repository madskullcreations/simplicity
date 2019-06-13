<?php 
/* Edit form for EditablePages.
 * 
 */

echo $this->element('TinyMCE');
?>

<?php 
	echo $this->Html->link(
		__d("simplicity", 'Add new language'),
		[
				'controller' => 'SimplicitySettings',
				'action' => 'language',
		],
		[
				'class' => 'button float-right top-margin',
		]);
?>

<h1><?= __d("simplicity", "Edit Page") ?></h1>

<h4>
<?php
  if($preselectedLanguage != null)
  {
    echo __d("simplicity", 'Please translate this page from').' "'.$availableLanguageCodes[$element->i18n].'" to "'.$availableLanguageCodes[$preselectedLanguage].'"';
  }
  else
  {
    echo __d("simplicity", 'The page\'s current language is').': "'.$availableLanguageCodes[$element->i18n].'"'; 
  }
?>  
</h4>

<?php
  if($preselectedLanguage != null)
  {
?>
<div class="callout primary" data-closable>
  <?php
      echo __d("simplicity", 'This page does not yet exist in').' "'.$availableLanguageCodes[$preselectedLanguage].'", you must translate this text and save it. It will be saved as a new page.';
  ?>
</div>
<?php
  }
  else
  {
?>
<div class="callout primary" data-closable>
  <p>
  <?php
    if(in_array($element->i18n, $implementedLanguageCodes))
    {
      unset($implementedLanguageCodes[$element->i18n]);
    }
    
    if(count($implementedLanguageCodes) > 0)
    {
      echo __d("simplicity", 'This page is available in the following languages').': ['.implode(',', $implementedLanguageCodes).']';
    }
  ?>
  </p>
  <?php    
    if(count($missingLanguages) > 0)
    {
  ?>
  <p><?= __d("simplicity", 'This page is missing in the following languages:').' ['.implode(',', $missingLanguages).']'; ?></p>
  <p><?= __d("simplicity", 'To create the page for a new language; Select a language below, edit and save. This will be saved as a new page.'); ?></p>
  <?php
    }
  ?>
  <button class="close-button" aria-label="Dismiss alert" type="button" data-close>
    <span aria-hidden="true">&times;</span>
  </button>  
</div>
<?php
  }
?>

<?php
    echo $this->Form->create($element);

    if(count($missingLanguages) > 0)
    {
      $options = [
	    				'options' => $missingLanguages, 
	    				'label' => false,
	    				'empty' => __d("simplicity", 'Select to create a new page in the choosen language...'),
	    		];
          
      if($preselectedLanguage != null)
      {
        $options['value'] = $preselectedLanguage;
      }
          
	    echo $this->Form->input(
	    		'i18n', 
          $options);
    }
    else
    {
    	echo '<p>'.__d("simplicity", 'This page is available in every language for this site. To add a new language to the site, please click "Add new language" above.').'</p>';
    }
    
    $arr = ['title' => __d("simplicity", 'The url title is visible in the browsers address bar.')];
    if($element->url_title == 'home' && $element->category_id === null)
    {
      echo '<p class="callout">'.__d("simplicity", 'You cannot change the Url Title of the starting page. It must always have the name "home". You can always change the Title, which is the part visible in menus.').'</p>';
      
      $arr['disabled'] = 'disabled';
    }

    echo $this->Form->input('url_title', $arr);    
    echo $this->Form->input('title', ['title' => __d("simplicity", 'The title is visible in the menus.')]);
    
    echo $this->Form->input('content');
    echo $this->Form->button(__d("simplicity", 'Save Page'), ['class' => 'button top-margin']);
    echo $this->Form->end();
?>