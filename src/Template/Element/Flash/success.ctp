<?php
if (!isset($params['escape']) || $params['escape'] !== false)
{
  $message = h($message);
}
?>
<div class="callout success" data-closable>
  <div class="text-center">
    <?= $message ?>
  </div>
  
  <button class="close-button" aria-label="Dismiss alert" type="button" data-close>
    <span aria-hidden="true">&times;</span>
  </button>  
</div>