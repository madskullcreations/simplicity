<?php 

/* Simplicity installer layout.
 * 
 */

?>
<!DOCTYPE html>
<html>
<head>
  <?= $this->Html->charset() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    Simplicity installation
  </title>
  <?= $this->Html->meta('icon') ?>

  <?= $this->Html->css('base.css') ?>
  <?= $this->Html->css('cake.css') ?>
  <?= $this->Html->css('zurb/foundation.css') ?>
  <?= $this->Html->css('simplicity.css') ?>
  
  <?= $this->fetch('meta') ?>
  <?= $this->fetch('css') ?>
  <?= $this->fetch('script') ?>
  
  <?= $this->Html->script('jquery.min.js') ?>
</head>
<body>
	<div id="simplicity-wrapper">
		<nav class="top-bar" data-topbar role="navigation">
      Simplicity setup
		</nav>
		
		<?php 
      echo $this->Flash->render() 
    ?>
		
		<div id="simplicity-content" class="row">
			<div class="small-12 large-9 columns">
				<?= $this->fetch('content') ?>
			</div>
		</div>
  </div>
    
  <?php // Zurb Foundation js really have to be at the bottom of the html file, otherwise it wont initialize correctly. ?>
  <?= $this->Html->script('zurb/foundation.min.js') ?>
  <script>
    $(document).foundation();
  </script>
</body>
</html>
