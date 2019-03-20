<?php
use App\Controller\CategoriesController;

?>

<div>
	<?= $categoryElement->cat_lang[0]->content ?>
</div>

<?php

// NOTE: default.po are used by default! If you need to use a specific file, like cake.po, you must specify that!
// This also allow you to have a file specific for Simplicity's own texts.
//   <-TODO: This is the way to go. Stuff not yet translated go into simplicity.po, 
//           and you use the _d("simplicity", "simple text to translate.");
// 
echo '<p>'.__("You are not authorized to access that location.").'</p>'; // Looks for default.po.
echo '<p>'.__d("cake", "You are not authorized to access that location.").'</p>'; // Looks for cake.po

// Using zurbs data-abide.
echo $this->Form->create(null, ['id' => 'contactForm', 'data-abide' => '', 'novalidate' => true]);

?>
<div data-abide-error class="sr-only callout large alert" style="display: none;">
  <?= __('There was a problem submitting your form. Please check the error message below each input field.'); ?>
</div>

<?= $this->Form->input('name', ['title' => __d('simplicity', 'Name')]); ?>
<label class="form-error" data-form-error-for="name"><?= __('Please fill in your name'); ?></label>

<?= $this->Form->input('email', ['title' => __('Email')]); ?>
<label class="form-error" data-form-error-for="email"><?= __('This must be a valid email address'); ?></label>

<?= $this->Form->input('message', ['title' => __('Message'), 'type' => 'textarea', 'required' => 'required', 'maxlength' => 512, 'data-validator' => 'min_length', 'min_len' => 15]); ?>
<label class="form-error" data-form-error-for="message"><?= __('The message must be at least 15 characters'); ?></label>

<?php
// TODO: recaptcha här!
// TODO: Översätt fälten till svenska, läs vidare om det!

echo $this->Form->submit(__('Submit'), ['class' => 'button top-margin']);
echo $this->Form->end();
?>

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

<?php
  if(count($errors) > 0)
  {
?>
<script>
  $(function(){
    <?php
      // Hack abide slightly by changing the error message for the form elements and show it.
      foreach($errors as $key => $messages)
      {
        $message = reset($messages);
        
        if(strlen($message) > 0)
        {
          // Replace default error message and show it.
    ?>
    // console.log($("label[data-form-error-for='<?= $key ?>']"));
    $("label[data-form-error-for='<?= $key ?>']").text("<?= $message ?>").addClass("is-visible");

    <?php
        }
        else
        {
          // No message, just show the default error message.
    ?>
    $("label[data-form-error-for='<?= $key ?>']").addClass("is-visible");

    <?php
        }
      }
    ?>
  });
</script>
<?php
  }
?>