<?php
$class = '';
if(!empty($params['class']))
{
  $class .= ' ' . $params['class'];
}
if(!isset($params['escape']) || $params['escape'] !== false)
{
  $message = h($message);
}
?>
<div class="callout <?= h($class) ?>" data-closable>
  <div class="text-center">
    <?= $message ?>
  </div>
  
  <button class="close-button" aria-label="Dismiss alert" type="button" data-close>
    <span aria-hidden="true">&times;</span>
  </button>  
</div>