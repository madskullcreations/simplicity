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
 
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/* A single user-row-entity. 
 * 
 */
class User extends Entity
{
  // This will ensure toArray() and other functions do not export the password field.
  protected $_hidden = ['password'];
  
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
