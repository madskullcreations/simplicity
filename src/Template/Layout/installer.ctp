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
	</head>
<body>
	<style>
		.site-logo{
			max-width: 100px;
		}
		.top-bar{
			height: 120px;
			padding: 0;
		}
		.menu-background-image{
			background: url('/img/butterflies.jpg') no-repeat center center;
			background-size: cover; 
			position: absolute;
			top: 0px; 
			left: 0px;
			
			height: auto;
			min-height: 120px;
			width: 100%;
		}
		.top-menu-bar{
			position: absolute;
			bottom: 0px;
			right: 0px;
			
			max-width: 70%;
		}
		
		.menu-top-stuff{
			position: relative; 
			width: 30%;
			height: 100%;
			padding: 10px;
			background-color: rgba(255,255,255,0.3);
			
			color: white;
		}
		.site-title-description{
			position: absolute; 
			left: 100px; 
			top: 40px; 
			font-family: "Open Sans";
		}
		.site-title{
			margin-bottom: 0px;
			line-height: 0.95;
			font-family: inherit;
			font-size: 2.5vw;
		}
		.site-description{
			font-family: inherit;
			font-size: 1.5vw;
		}
		
@media (max-width: 600px){
		.top-bar{
			height: auto;
			padding: 0;
		}
		.menu-background-image{
			background: url('/img/butterflies.jpg') no-repeat center center;
			background-size: cover; 
			position: absolute;
			top: 0px; 
			left: 0px;
			
			height: auto;
			min-height: 100%;
			width: 100%;
		}		
		.menu-top-stuff{
			width: 100%;
			padding: 10px;
			background-color: rgba(255,255,255,0.3);
			
			color: white;
		}
		.top-menu-bar{
			position: relative;
			max-width: 100%;
		}
		.site-title{
			margin-bottom: 0px;
			line-height: 0.95;
			font-family: inherit;
			font-size: 4.5vw;
		}
		.site-description{
			font-family: inherit;
			font-size: 3.5vw;
		}		
}
		
		.top-bar ul{
			background-color: rgba(255,255,255,0.3);
		}
		
		.header-subnav {
		  float: none;
		  position: relative;
		  text-align: center;
		  margin-bottom: 0;
		}
		.header-subnav li {
			float: none;
			display: inline-block; 
		}
		.header-subnav li a {
			padding: 0.9rem 1rem 0.75rem;
			font-size: 0.875rem;
			color: #fff;
			display: block;
			font-weight: bold;
			letter-spacing: 1px; 
		}
		.header-subnav li a.is-active {
			background: rgba(250, 250, 250, 0.7);
			color: #333; 
		}
		.header-subnav li a:hover {
			background: rgba(250, 250, 250, 0.7);
			color: #333;
			transition: all .25s ease-in-out; 
		}
		
		#simplicity-content{
			position: relative;
		}
		#simplicity-content:before{
			background: url('/img/momotombo.jpg') no-repeat;
			background-size: cover; 
			
			position: absolute;
			top: 0;
			left: 0;

			content : "";
	    opacity : 0.25;
	    height: 700px;
	    z-index: -1;
    			
			width: 100%;
		}
	</style>
	<div id="simplicity-wrapper">
		<nav class="top-bar" data-topbar role="navigation" style="position: relative;">
		</nav>
		
		<?= $this->Flash->render() ?>
		
		<div id="simplicity-content" class="row">
			<div class="medium-9 columns">
				<?= $this->fetch('content') ?>
			</div>
		</div>
  </div> <!-- simplicity-wrapper -->    
    
<?php // Zurb Foundation js really have to be at the bottom of the html file, otherwise it wont initialize correctly. ?>
<?= $this->Html->script('jquery.min.js') ?>
<?= $this->Html->script('zurb/foundation.js') ?>
<?= $this->Html->script('zurb/npm.js') ?>    
</body>
</html>
