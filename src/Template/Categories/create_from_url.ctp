<?php 
/* Create form for Categories.
 * 
 */

echo $this->element('TinyMCE');
?>

<h1><?= __d("simplicity", "Create Page") ?></h1>

<?php
    $urlTitle = array_pop($path);

    echo $this->Form->create();

    if(count($path) > 0)
    {
?>
<div class="callout primary" data-closable>
  <?php
    echo '<p>'.__d("simplicity", 'When creating this page, the following parent pages will be created if they don\'t already exist').':</p>';
    
    echo '<ul>';
    foreach($path as $ut)
    {
      echo '<li>'.$ut.'</li>';
    }
    echo '</ul>';
  ?>
</div>
<?php      
    }
    
    $options = [
            'options' => $availableLanguageCodes, 
            'label' => __d("simplicity", 'The page will be created in the selected language'),
            'empty' => __d("simplicity", 'Select a language..'),
            'value' => $i18n
        ];

    echo $this->Form->input(
        'i18n', 
        $options);
        
    $arr = ['title' => __d("simplicity", 'The url title is visible in the browsers address bar.'), 'value' => $urlTitle];
    if($urlTitle == 'home' && count($path) == 0)
    {
      echo '<p class="callout">'.__d("simplicity", 'You cannot change the Url Title of the starting page. It must always have the name "home". However, you can change the Title, visible in the menus.').'</p>';
      
      $arr['value'] = 'home';
     

      echo $this->Form->hidden('url_title', $arr);
    }
    else
    {
      echo $this->Form->input('url_title', $arr);
    }

    echo $this->Form->input('title', ['title' => __d("simplicity", 'The title is visible in the menus.'), 'value' => ucfirst($urlTitle)]);
    
    echo $this->Form->input('content', ['type' => 'textarea']);

  ?>
  <br>

  <div class="callout">
    <p><?= __d("simplicity", 'If you hide the page, it is not shown in the visitor\'s menu. You can still find the page in the administrators menu.') ?></p>
    <p><?= __d("simplicity", 'Note that hiding/showing a page hide/show the page in every language.') ?></p>
  </div>
  
<?php
  echo $this->Form->input('in_menus', [
    'label' => __d("simplicity", 'Show this page in the menus'),
    'type' => 'checkbox',
    'checked' => true
    ]);

  echo $this->Form->input('sort_by', [
    'label' => __d("simplicity", 'Used in the menus to decide in which order the pages comes. Leave at 1 if the order is not important.'),
    'default' => 1
    ]);
    
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