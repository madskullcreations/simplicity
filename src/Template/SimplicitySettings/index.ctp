<?php 
/* Overview page for SimplicitySettings.
 * 
 */
?>
<h3><?= __('Simplicity Settings') ?></h3>
<?php 
	echo $this->Html->link(
		__('Add new language'),
		[
				'controller' => 'SimplicitySettings',
				'action' => 'language',
		],
		[
				'class' => 'button top-margin',
		]);
?>
