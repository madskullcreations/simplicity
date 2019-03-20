<?php
  if($showCallout)
  {
?>
<div class="callout secondary">
  <p><?= __('Normally you can leave the layout at it\'s default. If you have defined several layout files you can choose between them here. Explanation of the default layouts:') ?></p>
  <p><?= __('Explanation of the default layouts:') ?></p>
  <ul>
    <li><?= __('simplicity - The standard layout in Simplicity, based on Zurb Foundation.') ?></li>
    <li><?= __('ajax - Used for ajax views; It outputs content with no additional formatting.') ?></li>
  </ul>
  <?php
    if(count($layoutFiles) == 2)
    {
  ?>
  <p><?= __('If you add your own layout file it will be available for selection here.') ?></p>
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
    $label = __('Layout');
  }
  
  echo $this->Form->input('layout', ['label' => $label, 'default' => $defaultLayout, 'options' => $layoutFiles]);
?>
