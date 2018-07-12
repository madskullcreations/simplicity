<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class UsersController extends AppController
{
  public function beforeFilter(Event $event)
  {
    parent::beforeFilter($event);
    
    // Allow users to register and logout.
    // You should not add the "login" action to allow list. Doing so would
    // cause problems with normal functioning of AuthComponent.
    $this->Auth->allow(['add', 'logout']);
  }

  /**
   * Upon login, a PHPSESSID cookie are created which will identify the logged in user.
   */
  public function login()
  {
    if ($this->request->is('post')) 
    {
      $user = $this->Auth->identify();
      
      if ($user) 
      {
        $this->Auth->setUser($user);
        return $this->redirect($this->Auth->redirectUrl());
      }
      
      $this->Flash->error(__('Invalid username or password, try again'));
    }
  }

  public function logout()
  {
    return $this->redirect($this->Auth->logout());
  }
    
  public function index()
  {
    $users = $this->Users->find('all', ['fields' => ['Users.id', 'Users.username', 'Users.role']]);
    $this->set('users', $users);
  }

  public function view($id)
  {
    $user = $this->Users->get($id);
    $this->set(compact('user'));
  }

  public function add()
  {
    $user = $this->Users->newEntity();
    
    if ($this->request->is('post')) 
    {
      // Prior to 3.4.0 $this->request->data() was used.
      $data = $this->request->getData();
      // debug($data);
      
      $user = $this->Users->patchEntity($user, $data);

      if ($this->Users->save($user)) 
      {
        $this->Flash->success(__('The user has been created.'));
        return $this->redirect(['action' => 'index']);
      }
      else
      {
        $this->Flash->error(__('Unable to add the user.'));
      }
    }
    
    $this->set('user', $user);
  }
}