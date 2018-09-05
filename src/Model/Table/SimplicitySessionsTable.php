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

/* Sessions are stored in this table, and handled by the DatabaseSession save handler.
 * 
 * Simple explanation: The value in the id-field are exactly the same value as the SIMPLICITY-session-cookie has.
 * 
 * NOTE: This works, but you will need to change accordingly in app.php. Make sure you change AFTER you have run the installation procedure, 
 *  during installation there are no database connection. 
 */
class SimplicitySessions extends Table
{
  /**
   * Drop the table from the database.
   */
	public function DropTable($connection)
  {
    $connection->execute("DROP TABLE `simplicity_sessions`;");
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    $connection->execute("
      CREATE TABLE `simplicity_sessions` (
        `id` char(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        `created` datetime DEFAULT CURRENT_TIMESTAMP, -- optional, requires MySQL 5.6.5+
        `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- optional, requires MySQL 5.6.5+
        `data` blob DEFAULT NULL, -- for PostgreSQL use bytea instead of blob
        `expires` int(10) unsigned DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) 
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB
      ROW_FORMAT=COMPACT;
    ");
  }
}