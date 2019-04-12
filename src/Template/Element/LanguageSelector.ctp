<?php
	function ProduceUrlTitles($urlTitlesForCategory)
  {
    // Argh, this is creating an js-object, do something about it!
    $catUrlTitles = 'var catUrlTitles = {';
    
    // debug($urlTitlesForCategory);
    if(isset($urlTitlesForCategory) && count($urlTitlesForCategory) > 0)
    {
      $count = count($urlTitlesForCategory);
      $i = 0;
      foreach($urlTitlesForCategory as $lang => $title)
      {
        $catUrlTitles .= $lang.':"'.$title.'"';
        
        if($i < $count - 1)
          $catUrlTitles .= ',';
        
        $i++;
      }
    }
    $catUrlTitles .= '};';
    
    return $catUrlTitles;
  }
  
  function ProduceUrlPath($urlTitles)
  {
    $urlPath = "/".implode('/', $urlTitles)."/";
    
    return $urlPath;
  }
  
	if($userIsAuthor)
	{
		echo $this->Html->link(
				__d("simplicity", 'Edit page'), 
				[
						'action' => 'edit', 
						$categoryElement->id,
            $selectedLanguage
				],
				[
						'class' => 'button',
						'style' => 'margin-right: 10px;'
				]);
		
		echo $this->Form->postLink(
				__d("simplicity", 'Erase page'), 
				[
						'action' => 'deleteElement', 
						$categoryElement->id,
            $selectedLanguage
				],
				[
						'class' => 'button',
            'style' => 'margin-right: 10px;',
						'type' => 'post',
						'confirm' => __d("simplicity", 'Are you sure?')
				]);
    
    if(count($missingLanguages) > 0)
    {
      echo $this->Html->link(
        __d("simplicity", 'Translate page'),
        [
            'action' => 'add_new_language',
            $categoryElement->id,
            $selectedLanguage
        ],
        [
            'class' => 'button',
        ]);
    }
	}
  
  $urlPath = "";
  if(isset($urlTitles) && count($urlTitles) > 0)
  {
    $urlPath = ProduceUrlPath($urlTitles);
  }
  // debug($urlPath);
  
  $catUrlTitles = ProduceUrlTitles($urlTitlesForCategory);
?>
<script>
  var urlPath = "<?= $urlPath ?>";
  // console.log(urlPath);
  
  <?= $catUrlTitles ?>
  // console.log(catUrlTitles);
  
<?php
if($userIsAuthor)
{
  // An author is redirected to create page if it does not yet exist in the selected language.
  // (this will make sure it keep the page_id.)
?>
  function LanguageSelected()
  {
    var selLang = $("#LanguageSelector option:selected").val();
          
    if(catUrlTitles.hasOwnProperty(selLang))
    {
      // Page exists in the selected language.
      GotoTranslatedPage(selLang);
    }
    else
    {
      // Page does not exist in the selected language.
      var path = '/categories/add_new_language/<?= $categoryElement->id ?>/' + selLang; 
      window.location.replace(path);
    }
  }
<?php
}
else
{
  // Not logged in users are redirected to the given language as normal.
  // TODO: More correct would be to redirect to standard language if page does not exist. 
  //    (Now it shows an empty page, or redirect to home.)
?>
  function LanguageSelected()
  {
    var selLang = $("#LanguageSelector option:selected").val();
    GotoTranslatedPage(selLang);
  }
<?php
}
?>
  function GotoTranslatedPage(selLang)
  {
    var path = urlPath + catUrlTitles[selLang] + "?lang=" + selLang; 
    // var path = window.location.pathname + "?lang=" + selLang;
    // alert("path: " + path + ", href: " + window.location.href + ", pathname: " + window.location.pathname);
        
    window.location.href = "/" + path;
    
    // alert(window.location.href);
    // alert(window.location.pathname);
  }
</script>