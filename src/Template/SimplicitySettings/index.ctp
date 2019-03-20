<?php 
/* Overview page for SimplicitySettings.
 * 
 */
?>
<h3><?= __('Simplicity Settings') ?></h3>
<div class="stacked-for-small button-group">
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
	echo $this->Html->link(
		__('Various settings'),
		[
				'controller' => 'SimplicitySettings',
				'action' => 'various',
		],
		[
				'class' => 'button top-margin',
		]);
?>
</div>
