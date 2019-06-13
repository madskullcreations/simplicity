<?php 
/* Overview page for SimplicitySettings.
 * 
 */
?>
<h3><?= __d("simplicity", 'Simplicity Settings') ?></h3>
<div class="stacked-for-small button-group">
<?php 
	echo $this->Html->link(
		__d("simplicity", 'Add new language'),
		[
				'controller' => 'SimplicitySettings',
				'action' => 'language',
		],
		[
				'class' => 'button top-margin',
		]);
	echo $this->Html->link(
		__d("simplicity", 'Various settings'),
		[
				'controller' => 'SimplicitySettings',
				'action' => 'various',
		],
		[
				'class' => 'button top-margin',
		]);    
	echo $this->Html->link(
		__d("simplicity", 'Users'),
		[
				'controller' => 'Users',
				'action' => 'index',
		],
		[
				'class' => 'button top-margin',
		]);
?>
</div>
