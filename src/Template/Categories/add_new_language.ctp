<?php 
/* Add new language form for Categories.
 * 
 */

echo $this->element('TinyMCE');
?>

<h1><?= __d("simplicity", "Translate Page") ?></h1>

<div class="callout primary" data-closable>
  <?php
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
    echo $this->Form->create();
    
    $options = [
            'options' => $missingLanguages, 
            'label' => __d("simplicity", 'The page will be created in the selected language'),
            'empty' => __d("simplicity", 'Select a language..'),
            'value' => $i18n
        ];

    echo $this->Form->input(
        'i18n', 
        $options);
        
    $arr = ['title' => __d("simplicity", 'The url title is visible in the browsers address bar.')];
    $urlTitle = $categoryElement->cat_lang[0]->url_title;
    
    if($urlTitle == 'home' && $categoryElement->parent_id == null)
    {
      echo '<p class="callout">'.__d("simplicity", 'You cannot change the Url Title of the starting page. It must always have the name "home". However, you can change the Title, visible in the menus.').'</p>';
      
      $arr['value'] = 'home';
     

      echo $this->Form->hidden('url_title', $arr);
    }
    else
    {
      $arr['value'] = $urlTitle;
      echo $this->Form->input('url_title', $arr);
    }      

    echo $this->Form->hidden('id', ['value' => $categoryElement->id]);
    
    echo $this->Form->input('title', [  
      'title' => __d("simplicity", 'The title is visible in the menus.'),
      'value' => $categoryElement->cat_lang[0]->title
      ]);
    
    echo $this->Form->input('content', [
      'type' => 'textarea',
      'value' => $categoryElement->cat_lang[0]->content
      ]);
    
    echo $this->Form->button(__d("simplicity", 'Save Page'), ['class' => 'button top-margin']);
    echo $this->Form->end();
?>