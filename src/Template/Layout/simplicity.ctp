<?php 

/* Simplicity default layout.
 * 
 */

?>
<!DOCTYPE html>
<html>
	<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
      <?= $this->fetch('simplicity_site_title').': '.$this->fetch('simplicity_page_name') ?>
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
	</style>
	<div id="simplicity-wrapper">
		<nav class="top-bar" data-topbar role="navigation">
			<!-- This should be a cake block of course. -->
			<div class="menu-background-image"></div>
			<div class="menu-top-stuff" style="">
				<?= $this->Html->image('Mariposa.png', ['class' => 'site-logo']); ?>
				<div class="site-title-description">
					<h2 class="site-title"><?= $this->fetch('simplicity_site_title'); ?></h2>
					<h5 class="site-description" ><?= $this->fetch('simplicity_site_description'); ?></h5>
				</div>
			</div>
			<div class="top-menu-bar">
				<?= $this->fetch('simplicity_top_menu') ?>
			</div>
		</nav>
		<?php 
			// Detta är ju en alternativ position för toppmenyn. Med rätt färgval så blir det fint. Men den skalar inte snyggt!
			// ...det verkar som du får kolla in zurbs css för att ha toppmenyer som skalar fint. 
			//
			// NÄ! Sno css o alltsammans från denna: http://zurb.com/building-blocks/header-subnav
			// 
			// Såg också att man måste kunna sätta en titel på en sida, som blir url-friendly. 
			// ..själva ursprungsidén med att skriva in adressen uppe i urlen o så skapas den är inte så kul i praktiken, man 
			//  undrar fort hur urlen ska se ut, och måste kunna de reglerna! ..bara för att sen sätta korrekt titel på en gång. 
			// 
			// Nå-flaggorna först! De ska hamna uppe till höger, en bit från toppen. Snyggigt. 
		?>
		
    <div id="simplicity-content">
      <?= $this->fetch('simplicity_breadcrumbs') ?>
              
      <?= $this->Flash->render() ?>
      
      <div class="grid-container">
        <div class="grid-x grid-margin-x">
          <div class="cell small-9">
            <?= $this->fetch('content') ?>
          </div>
          <div class="cell small-3" data-sticky-container>
            <div class="sticky" data-sticky data-anchor="content">
              <?= $this->fetch('simplicity_side_menu') ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer>
      <div class="grid-container">
        <div class="grid-x grid-margin-x">
          <div class="cell small-10">
            <?= $this->fetch('simplicity_footer_text'); ?>
          </div>
        </div>
      </div>
    </footer>
  </div>
    
<?php // Zurb Foundation js really have to be at the bottom of the html file, otherwise it wont initialize correctly. ?>
  <?= $this->Html->script('zurb/foundation.min.js') ?>
  <script>
    $(document).foundation();
  </script>
</body>
</html>
