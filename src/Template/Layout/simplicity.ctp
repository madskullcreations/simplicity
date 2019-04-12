<?php 

/* Simplicity default layout.
 * 
 */


/* Please feel free to re-define these in your own views. 
 * 
 */
$this->start('simplicity_top_menu');
	echo $this->Menu->GetMenu($homeTree, 'simplicity_top_menu', 'dropdown menu header-subnav', 'simplicity menu');
$this->end();
$this->start('simplicity_side_menu');
	echo '<h4>Menu</h4>';
  
  if($userIsLoggedIn)
  {
?>
  <div style="margin-bottom: 30px;">
    <h6><?= __d("simplicity", 'Administrator') ?></h6>
    <?= $this->Menu->GetAccordionMenu($sideMenuTreeAdmin); ?>
  </div>
<?php
  }
  
  // Normal users have this menu, or the side-menu are not shown at all.
  if(count($sideMenuTree) > 0)
  {
?>
  <div style="margin-bottom: 30px;">
    <h6><?= __d("simplicity", 'Local Content') ?></h6>
    <?= $this->Menu->GetAccordionMenu($sideMenuTree); ?>
  </div>
<?php
  }
$this->end();
$this->start('simplicity_breadcrumbs');
	echo $this->Menu->GetBreadCrumb($breadcrumbPath);
$this->end();
$this->start('simplicity_page_name');
	// A bit odd, but to use a utility, we must give full path. 
	echo Cake\Utility\Inflector::camelize($categoryElement->cat_lang[0]->title);
$this->end();

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
  </style>
	<div id="simplicity-wrapper">
    <div id="simplicity-inner-wrapper">
      <nav id="simplicity-top-bar" class="">
        <div class="control-box">
          <?php
            if($displayingNormalPage)
            {
              echo '<select class="language-selector" id="LanguageSelector" onchange="LanguageSelected();" title="'.__d("simplicity", "Select your language").'">';
              foreach($availableLanguages as $key => $name)
              {
                $selected = '';
                if($key == $selectedLanguage)
                {
                  $selected = 'selected';
                }
                
                echo '<option value="'.$key.'" '.$selected.'>'.$name.'</option>';
              }
              echo '</select>';
            }
            
            if($userIsLoggedIn)
            {
              echo '<a class="button logout" title="'.__d("simplicity", "Logout").'" href="/users/logout">'.__d("simplicity", "Logout").'</a>';
            }
            else
            {
              echo '<a class="button login" title="'.__d("simplicity", "Login").'" href="/users/login">'.__d("simplicity", "Login").'</a>';
            }            
          ?>
        </div>
        <div class="grid-container fluid">
          <div class="grid-x site-title-description">
            <div class="cell small-4 medium-3 large-2">
              <?php 
                $img = $this->Html->image('simplicity.png', ['class' => 'site-logo']);
                echo $this->Html->link($img, '/', ['escape' => false]); 
              ?>
            </div>
            <div class="cell shrink">
              <h2 class="site-title"><?= $this->fetch('simplicity_site_title'); ?></h2>
              <h5 class="site-description" ><?= $this->fetch('simplicity_site_description'); ?></h5>          
            </div>
            <div class="cell auto">
              &nbsp;
            </div>
          </div>
        </div>
        <div class="grid-container top-menu-bar">
          <div class="grid-x">
            <div class="cell small-12 ">
              <?= $this->fetch('simplicity_top_menu') ?>
            </div>
          </div>
        </div>
      </nav>
      
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
        <div class="grid-container fluid">
          <div class="grid-x grid-margin-x">
          	<div class="cell auto">
          		<p><a href="https://github.com/madskullcreations/simplicity" target="_blank">Simplicity CMS</a>&nbsp;- Simple yet powerful</p>
          	</div>
          	<div class="cell auto">
          		<p>Powered by&nbsp;<a href="https://cakephp.org/" target="_blank">CakePHP</a>&nbsp;and&nbsp;<a href="https://foundation.zurb.com/" target="_blank">Zurb Foundation</a></p>
          	</div>
          	<div class="cell auto">
          		<p>A&nbsp;<a href="https://madskullcreations.com" target="_blank">Madskull Creations</a>&nbsp;product</p>
          	</div>
          </div>
        </div>
      </footer>
    </div>
  </div>
      
<?php // Zurb Foundation js really have to be at the bottom of the html file, otherwise it wont initialize correctly. ?>
  <?= $this->Html->script('zurb/foundation.min.js') ?>
  
  <script>
    // TODO: Extract into a helper.
    
    // Zurb abide validator functions.
    function Validate_MinStringLength(
      $el,      /* jQuery element to validate */
      required, /* is the element required according to the `[required]` attribute */
      parent    /* parent of the jQuery element `$el` */
    )
    {
      if(required == false)
        return true;
      
      var text = $($el).val();
      
      if(text.length < $($el).attr("min_len"))
        return false;
      
      return true;
    }
    
    Foundation.Abide.defaults.validators['min_length'] = Validate_MinStringLength;
    
    $(document).foundation();
    
    $('.site-logo').attr('draggable', false);
  </script>
  
  <?php
    if($displayingNormalPage)
    {
      echo $this->element('LanguageSelector', [
        'userIsAuthor' => $userIsAuthor, 
        'urlTitlesForCategory' => $urlTitlesForCategory,
        'urlTitles' => $urlTitles
        ]);
    }
  ?>
    
  <?= $this->Html->script('prism') ?>
</body>
</html>
