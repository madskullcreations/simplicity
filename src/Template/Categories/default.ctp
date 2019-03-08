<?php
use App\Controller\CategoriesController;
?>

<div>
  <?php 
    // debug($breadcrumbPath);
    // debug($categoryElement);
    // debug($categoryElement->cat_lang[0]->content);
  ?>
	<?= $categoryElement->cat_lang[0]->content ?>
</div>
<div>
	<?php 
    // echo $categoryElement->created;
  ?>
</div>
<div>
	<?php 
    // echo $categoryElement->modified;
  ?>
</div>

<?php
	if($userIsAuthor)
	{
    echo $this->element('AuthorControl', [
      'userIsAuthor' => $userIsAuthor, 
      'categoryElementId' => $categoryElement->id,
      'selectedLanguage' => $selectedLanguage, 
      'missingLanguages' => $missingLanguages
      ]);
  }
?>
