<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = 'CakePHP: the rapid development php framework';

?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <?= $this->Html->css('zurb-foundation-6/foundation.css') ?>    
    <?= $this->Html->css('zurb-foundation-6/app.css') ?>
    <?= $this->Html->css('simplicity.css') ?>
		
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-bar expanded" data-topbar role="navigation">
        <ul class="title-area large-3 medium-4 columns">
            <li class="name">
                <h1><a href=""><?= $this->fetch('title') ?></a></h1>
            </li>
        </ul>

				<div class="top-bar-left">
        	<?= $this->fetch('simplicity_top_menu') ?>
				</div>
				
        <section class="top-bar-section">
            <ul class="right">
                <li><a target="_blank" href="https://book.cakephp.org/3.0/">Documentation</a></li>
                <li><a target="_blank" href="https://api.cakephp.org/3.0/">API</a></li>
            </ul>
        </section>
    </nav>
    <?= $this->Flash->render() ?>
    <section class="container clearfix">
        <?= $this->fetch('content') ?>
    </section>
    
<button class="button" type="button" data-toggle="example-dropdown">Toggle Dropdown</button>
<div class="dropdown-pane" id="example-dropdown" data-dropdown data-auto-focus="true">
  Example form in a dropdown.
  <form>
    <div class="row">
      <div class="medium-6 columns">
        <label>Name
          <input type="text" placeholder="Kirk, James T.">
        </label>
      </div>
      <div class="medium-6 columns">
        <label>Rank
          <input type="text" placeholder="Captain">
        </label>
      </div>
    </div>
  </form>
</div>    
    <div class="row">
    	<div class="large-12 columns">
    		<h1>Hello there.</h1>
    	</div>
    </div>
    <div class="row">
    	<div class="large-12 columns">
    		<p>-</p>
    		<div class="row">
    			<div class="large-4 medium-4 columns">
    				<p>Here goes menu</p>
    			</div>
    			<div class="large-8 medium-8 columns">
    				<p>and some content.</p>
    			</div>
    		</div>
    	</div>
    </div>
    <div style="margin-top: 50px;"></div>
    
    <footer>
    	a fancy footer
    </footer>
    
    
    <?php // Zurb Foundation js really have to be at the bottom of the html file, otherwise it wont initialize correctly. ?>
		<?= $this->Html->script('jquery.min.js') ?>
		<?= $this->Html->script('zurb-foundation-6/vendor/what-input.min.js') ?>
		<?= $this->Html->script('zurb-foundation-6/foundation.js') ?>
		<?= $this->Html->script('zurb-foundation-6/app.js') ?>    
</body>
</html>
