<?php 
/* Edit form for Categories.
 * 
 */

echo $this->element('TinyMCE');
?>

<?php 
  if(count($missingLanguages) > 0)
  {
    echo $this->Html->link(
      __d("simplicity", 'Translate page'),
      [
          'action' => 'add_new_language',
          $categoryElement->id,
          $i18n
      ],
      [
          'class' => 'button float-right top-margin',
      ]);
  }
?>

<h1><?= __d("simplicity", "Edit Page") ?></h1>

<div class="callout primary" data-closable>
  <p><?= __d("simplicity", "The page language is in").' '.$availableLanguageCodes[$i18n] ?>.</p>
  <?php
    if(count($missingLanguages) > 0)
    {
  ?>
  <p><?= __d("simplicity", "To translate the page into another language, click the button").' "'.__d("simplicity", 'Translate page').'".' ?></p>
  <?php
    }
    else
    {
  ?>
  <p><?= __d("simplicity", "This page is translated into all available languages.").' ('.__d("simplicity", "To add another language, go to administrators overview pages.").')'; ?></p>
  <?php
    }
  ?>
  <button class="close-button" aria-label="Dismiss alert" type="button" data-close>
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<?php
  // debug($categoryElement);
  $urlTitle = $categoryElement->cat_lang[0]->url_title;

  echo $this->Form->create($categoryElement->cat_lang[0]);
        
  $arr = ['title' => __d("simplicity", 'The url title is visible in the browsers address bar.')];
  if($urlTitle == 'home' && $categoryElement->parent_id == null)
  {
    echo '<p class="callout">'.__d("simplicity", 'You cannot change the Url Title of the starting page. It must always have the name "home". However, you can change the Title, visible in the menus.').'</p>';
    
    $arr['value'] = 'home';
   

    echo $this->Form->hidden('url_title', $arr);
  }
  else
  {
    echo $this->Form->input('url_title', $arr);
  }
  
  echo $this->Form->hidden('id', ['value' => $categoryElement->id]);
  echo $this->Form->hidden('catlang_id', ['value' => $categoryElement->cat_lang[0]->id]);

  echo $this->Form->input('title', ['label' => __d("simplicity", 'The title is visible in the menus.')]);
  
  echo $this->Form->input('content', ['type' => 'textarea']);
  
  echo "<br>";
  
?>

  <div class="callout">
    <p><?= __d("simplicity", 'If you hide the page, it is not shown in the visitor\'s menu. You can still find the page in the administrators menu.') ?></p>
    <p><?= __d("simplicity", 'Note that hiding/showing a page hide/show the page in every language.') ?></p>
  </div>
  
<?php
  // Checking a checkbox is always ...not easy.
  echo $this->Form->input('in_menus', [
    'label' => __d("simplicity", 'Show this page in the menus'),
    'type' => 'checkbox',
    'checked' => ($categoryElement->in_menus == '1' || $categoryElement->in_menus === null) ? true:false
    ]);
    
  // TODO: For some fantastic reason the value is not auto populated by cake...
  echo $this->Form->input('sort_by', [
    'label' => __d("simplicity", 'Used in the menus to decide in which order the pages comes. Leave at 1 if the order is not important.'),
    'default' => $categoryElement->sort_by 
    ]);
    
  if($categoryElement->layout != null)
    $defaultLayout = $categoryElement->layout;
  
  echo $this->element(
  	'LayoutSelector', 
  	[
  		'defaultLayout' => $defaultLayout, 
  		'layoutFiles' => $layoutFiles,
  		'showCallout' => true
  	]);
  
  echo $this->Form->button(__d("simplicity", 'Save Page'), ['class' => 'button top-margin']);
  echo $this->Form->end();
?>