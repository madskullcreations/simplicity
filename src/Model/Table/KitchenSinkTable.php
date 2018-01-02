<?php

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * KitchenSink Table
 * 
 * Allow you to quickly save away a value globally available for any time. (apart from Session, which works great for any Session-data.)
 * The value is a VARCHAR stored through php's serialize() function. 
 * 
 * The key value is case sensitive, "skåne" is not the same as "Skåne". 
 * All values are stored in the utf8_unicode_ci collation, so pretty much any character should pass.
 * 
 */
class KitchenSinkTable extends Table 
{
	public function Store($key, $value)
	{
		$serialized = serialize($value);
				
		$element = $this->find()->where(['kitchen_key' => $key])->first();
		// debug($element);
		
		if($element == null)
		{
			// New.
			$element = $this->newEntity();
			$element->kitchen_key = $key;
		}
		
		$element->value = $serialized;
		$this->save($element);
	}
	
	/* Returns the value of the given key. If the value does not exist, $default is returned.
	 *  
	 * If $default is not null, and the value of the given key does not yet exist, it is 
	 * stored before it is returned. 
	 * 
	 */
	public function Retrieve($key, $default = null)
	{
		$element = $this->find()->where(['kitchen_key' => $key])->first();
		
		if($element == null)
		{
			if($default != null)
			{
				// Create the default value in database.
				$this->Store($key, $default);
			}
			
			return $default;
		}
		
		return unserialize($element->value);
	}
	
	public function Forget($key)
	{
		$conditions = array(
			'kitchen_key' => $key
		);
				
		$this->deleteAll($conditions);
	}
}

/* 

CREATE TABLE `kitchen_sink` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`kitchen_key` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`value` VARCHAR(4096) NOT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`id`),
	UNIQUE `u_kitchen_key` (`kitchen_key`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

*/

/*

	// Small test-code.
	$kitchenSink = TableRegistry::get('KitchenSink');
	
	$kitchenSink->Store('apples', array('green', 'red' => 12, 'blue' => array('no!!'), 'yellow' => 2));
	$appleInfo = $kitchenSink->Retrieve('apples');
	
	$kitchenSink->Forget('apples');
	$whatApples = $kitchenSink->Retrieve('apples'); // Returns null.
	
	$nullIfNotThere = $kitchenSink->Retrieve('FancyKey');
	
	// This is a nice shortcut to give a default value if there is none in database.
	$alwaysDefined = $kitchenSink->Retrieve('ScreaminglyFancyKey', 'a default value here');

*/

