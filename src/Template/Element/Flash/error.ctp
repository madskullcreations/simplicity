<?php
if (!isset($params['escape']) || $params['escape'] !== false)
{
  $message = h($message);
}
?>
<div class="callout large alert" data-closable>
  <div class="text-center">
    <?= $message ?>
  </div>
  
  <button class="close-button" aria-label="Dismiss alert" type="button" data-close>
    <span aria-hidden="true">&times;</span>
  </button>  
</div>