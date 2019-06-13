<?php
  $elmId = "microsoft_store_badge";
  
  // Replace with your apps store id.
  $storeId = "9P9WSPB7JZQR";
?>

<div id="<?= $elmId ?>" class="<?= $storeId ?>"></div>
<script src="https://storebadge.azureedge.net/src/badge-1.8.3.js"></script>
<script>
  mspb('<?= $storeId ?>', function(badge) {
    document.getElementById('<?= $elmId ?>').innerHTML = badge;
  });
</script>