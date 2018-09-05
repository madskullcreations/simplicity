<?php
/**
 * Simplicity (https://github.com/madskullcreations/simplicity)
 * Copyright (c) Madskull Creations (https://madskullcreations.com)
 * 
 * Licensed under the MIT license.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * 
 */
 
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
				
		$element = $this->find()->where(['KitchenSink.kitchen_key' => $key])->first();
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
		$element = $this->find()->where(['KitchenSink.kitchen_key' => $key])->first();
		
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
			'KitchenSink.kitchen_key' => $key
		);
				
		$this->deleteAll($conditions);
	}
  
  /**
   * Drop the table from the database.
   */
	public function DropTable($connection)
  {
    $connection->execute("DROP TABLE `kitchen_sink`;");
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    $connection->execute("
      CREATE TABLE `kitchen_sink` (
        `id` INT(10) NOT NULL AUTO_INCREMENT,
        `kitchen_key` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
        `value` VARCHAR(4096) NOT NULL COLLATE 'utf8_unicode_ci',
        PRIMARY KEY (`id`),
        UNIQUE `u_kitchen_key` (`kitchen_key`)
      )
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB;
    ");
  }
}

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

