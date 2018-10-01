<?php 

/* Simplicity default layout.
 * 
 */


/* Please feel free to re-define these in your own views. 
 * 
 */
$this->start('simplicity_top_menu');
	echo $this->Menu->GetMenu($homeTree, 'dropdown menu header-subnav', 'simplicity menu');
	//echo $this->Menu->GetMenu($homeTree, 'simplicity dropdown menu', 'simplicity menu');
$this->end();
$this->start('simplicity_side_menu');
	echo '<h4>Menu</h4>';
  
  if($userIsLoggedIn)
  {
?>
  <div style="margin-bottom: 30px;">
    <h6><?= __('Administrator') ?></h6>
    <?= $this->Menu->GetAccordionMenu($sideMenuTreeAdmin); ?>
  </div>
<?php
  }
  
  // Normal users have this menu, or the side-menu are not shown at all.
  if(count($sideMenuTree) > 0)
  {
?>
  <div style="margin-bottom: 30px;">
    <h6><?= __('Local Content') ?></h6>
    <?= $this->Menu->GetAccordionMenu($sideMenuTree); ?>
  </div>
<?php
  }
$this->end();
$this->start('simplicity_breadcrumbs');
	echo $this->Menu->GetBreadCrumb($breadcrumbPath, $richTextElement);
$this->end();
$this->start('simplicity_page_name');
	// A bit odd, but to use a utility, we must give full path. 
	echo Cake\Utility\Inflector::camelize($richTextElement->name);
$this->end();

//debug($richTextElement->identifier);
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
  
  <?= $this->Html->css('zurb/foundation.css') ?>
  <?= $this->Html->css('prism.css') ?>
  <?= $this->Html->css('simplicity.css?version='.rand(0, 10000).'') ?>
  
  <?= $this->fetch('meta') ?>
  <?= $this->fetch('css') ?>
  <?= $this->fetch('script') ?>
  
  <?= $this->Html->script('jquery.min.js') ?>
</head>
<body>
	<style>
		.site-logo{
			max-width: 140px;
		}
		.site-title-description{
			font-family: "Open Sans";
      display: flex;
      align-items: center;  
      justify-content: center;      
		}
		.site-title{
			margin-bottom: 0px;
			line-height: 0.95;
			font-size: 2.5vw;
		}
		.site-description{
			font-size: 1.5vw;
		}
		.top-menu-bar{
    }
		.top-menu-bar ul{
			background-color: rgba(255,255,255,0.3);
		}
		.top-menu-bar .header-subnav {
		  float: none;
		  position: relative;
		  text-align: center;
		  margin-bottom: 0;
		}
		.top-menu-bar .header-subnav li {
			float: none;
			display: inline-block; 
		}
		.top-menu-bar .header-subnav li a {
			padding: 0.9rem 1rem 0.75rem;
			font-size: 0.875rem;
			color: #fff;
			display: block;
			font-weight: bold;
			letter-spacing: 1px; 
		}
		.top-menu-bar .header-subnav li a.is-active {
			background: rgba(250, 250, 250, 0.7);
			color: #333; 
		}
		.top-menu-bar .header-subnav li a:hover {
			background: rgba(250, 250, 250, 0.7);
			color: #333;
			transition: all .25s ease-in-out; 
		}
	</style>
	<div id="simplicity-wrapper">
    <div id="simplicity-inner-wrapper">
      <nav id="simplicity-top-bar" class="top-bar">
        <div class="grid-container">
          <div class="grid-x grid-margin-x site-title-description">
            <div class="cell small-4">
              <?= $this->Html->image('simplicity.png', ['class' => 'site-logo']); ?>
            </div>
            <div class="cell auto">
              <h2 class="site-title"><?= $this->fetch('simplicity_site_title'); ?></h2>
              <h5 class="site-description" ><?= $this->fetch('simplicity_site_description'); ?></h5>          
            </div>            
          </div>
          <div class="grid-x grid-margin-x top-menu-bar">
            <div class="cell">
              <?= $this->fetch('simplicity_top_menu') ?>
            </div>
          </div>
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
            <?php
              if($userIsLoggedIn)
              {
            ?>
            <div class="cell small-3">
              <?= $this->fetch('simplicity_side_menu') ?>
            </div>
            <?php
              }
            ?>
            <div class="cell auto">
              <?= $this->fetch('content') ?>
            </div>
          </div>
        </div>
      </div>
      <footer class="simplicity-footer">
        <div class="grid-container">
          <div class="grid-x grid-margin-x">
            <?= $this->fetch('simplicity_footer_text'); ?>
          </div>
        </div>
      </footer>
    </div>
  </div>
    
<?php // Zurb Foundation js really have to be at the bottom of the html file, otherwise it wont initialize correctly. ?>
  <?= $this->Html->script('zurb/foundation.min.js') ?>
  <script>
    $(document).foundation();
  </script>
  
  <?= $this->Html->script('prism') ?>
</body>
</html>
