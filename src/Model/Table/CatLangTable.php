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
use RuntimeException;

/****
 * 
 * 
 */
class CatLangTable extends Table
{
	public function initialize(array $config)
	{
    // A category language belongs to one category. (A category can have many catlangs.)
    $this->belongsTo('Category');
    
		$this->addBehavior('Timestamp');
	}
	
  /**
   * Drop the table from the database.
   */
	public function DropTable($connection)
  {
    $connection->execute("DROP TABLE `cat_lang`;");
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    // <-Och ta bort i18n ur categories!
    
    // uk_category_id_i18n - One translation per language per category.
    // 
    $connection->execute("
      CREATE TABLE `cat_lang` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `category_id` INT(10) NOT NULL,
      `i18n` VARCHAR(12) NOT NULL COLLATE 'utf8_unicode_ci',
      `url_title` VARCHAR(128) NOT NULL COLLATE 'utf8_unicode_ci',
      `title` VARCHAR(128) NOT NULL COLLATE 'utf8_unicode_ci',
      `created` DATETIME NULL,
      `modified` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_category_id_i18n` (`category_id`, `i18n`) 
      )
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB
      ROW_FORMAT=COMPACT;
    ");
  }
}
