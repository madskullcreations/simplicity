<?php
  if($showCallout)
  {
?>
<div class="callout secondary">
  <p><?= __d("simplicity", 'Normally you can leave the layout at it\'s default. If you have defined several layout files you can choose between them here. Explanation of the default layouts:') ?></p>
  <p><?= __d("simplicity", 'Explanation of the default layouts:') ?></p>
  <ul>
    <li><?= __d("simplicity", 'simplicity - The standard layout in Simplicity, based on Zurb Foundation.') ?></li>
    <li><?= __d("simplicity", 'ajax - Used for ajax views; It outputs content with no additional formatting.') ?></li>
  </ul>
  <?php
    if(count($layoutFiles) == 2)
    {
  ?>
  <p><?= __d("simplicity", 'If you add your own layout file it will be available for selection here.') ?></p>
  <?php
    }
  ?>
</div>
<?php
  }
?>
<?php
  if(!isset($label))
  {
    $label = __d("simplicity", 'Layout');
  }
  
  echo $this->Form->input('layout', ['label' => $label, 'default' => $defaultLayout, 'options' => $layoutFiles]);
?>
