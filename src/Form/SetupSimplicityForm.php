<?php
namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class SetupSimplicityForm extends Form
{
  protected function _buildSchema(Schema $schema)
  {
    return $schema
      ->addField('db_database', 'string')
      ->addField('db_username', ['type' => 'string'])
      ->addField('db_password', 'password');
  }

  protected function _buildValidator(Validator $validator)
  {
    return $validator
      ->add('db_database', 'length', [
        'rule' => ['minLength', 1],
        'message' => 'You need to specify a database name'
      ])
      ->add('db_username', 'length', [
        'rule' => ['minLength', 1],
        'message' => 'You need to specify a database user name'
      ])
      ->add('user_email', 'format', [
        'rule' => 'email',
        'message' => 'This must be a valid email address'
      ])
      ->add('user_password', 'length', [
        'rule' => ['minLength', 5],
        'message' => 'Choose a password with at least 5 characters'
      ]);
  }

  protected function _execute(array $data)
  {
    // I guess the further validation could be done here, but it happens inside the InstallerController instead.
    return true;
  }
}