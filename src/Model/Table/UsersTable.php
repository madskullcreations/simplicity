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

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/* 
 * 
 */
class UsersTable extends Table
{
  public function validationDefault(Validator $validator)
  {
    return $validator
      ->notEmpty('username', __('A username is required'))
      ->notEmpty('password', __('A password is required'))
      ->notEmpty('role', __('A role is required'))
      ->add('role', 'inList', [
        'rule' => ['inList', ['admin', 'author']],
        'message' => __('Please enter a valid role')
      ]);
  }

  /**
   * Drop the table from the database.
   */
	public function DropTable($connection)
  {
    $connection->execute("DROP TABLE `users`;");
  }
  
  /**
   * Create the table in the database.
   */
	public function CreateTable($connection)
  {
    $connection->execute("
      CREATE TABLE users (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
          password VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
          role VARCHAR(20) NOT NULL COLLATE 'utf8_unicode_ci',
          created DATETIME DEFAULT NULL,
          modified DATETIME DEFAULT NULL
      )
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB
      ROW_FORMAT=COMPACT;
    ");
  }
  
  /**
   * Cake will find this function and, when field password are set, it will be hashed.
   */
  protected function _setPassword($password)
  {
    if (strlen($password) > 0) 
    {
      return (new DefaultPasswordHasher)->hash($password);
    }
  }
}
