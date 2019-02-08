<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Plugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;
use Cake\Core\Configure;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
Router::defaultRouteClass(DashedRoute::class);

$simplicity_setup_state = Configure::read('simplicity_setup_state');
// debug($simplicity_setup_state);
 
if($simplicity_setup_state > 1)
{
  Router::scope('/', function (RouteBuilder $routes) {
      /**
       * Here, we are connecting '/' (base path) to a controller called 'Categories',
       * its action called 'display', and we pass a param to select what page to view. 
       */
      $routes->connect('/', ['controller' => 'Categories', 'action' => 'display', 'home']);

      /**
       * The /installer/success is always ok.
       */
      $routes->connect('/installer/success', ['controller' => 'Installer', 'action' => 'success']);

      /**
       * The /users/add is always ok.
       */
      $routes->connect('/users/add', ['controller' => 'Users', 'action' => 'add']);
      $routes->connect('/users/login', ['controller' => 'Users', 'action' => 'login']);
      $routes->connect('/users/logout', ['controller' => 'Users', 'action' => 'logout']);
      $routes->connect('/users/index', ['controller' => 'Users', 'action' => 'index']);
      $routes->connect('/users', ['controller' => 'Users', 'action' => 'index']);
      
      /**
       * The /categories/edit is reserved for editing pages.
       */
      $routes->connect('/categories/edit/*', ['controller' => 'Categories', 'action' => 'edit']);
      $routes->connect('/categories/create_from_url/*', ['controller' => 'Categories', 'action' => 'create_from_url']);
      $routes->connect('/categories/add_new_language/*', ['controller' => 'Categories', 'action' => 'add_new_language']);

      /**
       * ..the /pages/deleteElement is reserved for deleting pages.
       */
      $routes->connect('/categories/deleteElement/*', ['controller' => 'Categories', 'action' => 'deleteElement']);

      /**
       * ..the simplicity_settings/* is reserved as well.
       */
      $routes->connect('/simplicity_settings', ['controller' => 'SimplicitySettings']);
      $routes->connect('/simplicity_settings/:action', ['controller' => 'SimplicitySettings']);
      
      /**
       * ..and connect all other pages to the 'Categories' controller. 
       */
      $routes->connect('/*', ['controller' => 'Categories', 'action' => 'display']);
      
      /**
       * Connect catchall routes for all controllers.
       *
       * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
       *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
       *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
       *
       * Any route class can be used with this method, such as:
       * - DashedRoute
       * - InflectedRoute
       * - Route
       * - Or your own route class
       *
       * You can remove these routes once you've connected the
       * routes you want in your application.
       */
      $routes->fallbacks(DashedRoute::class);
  });

  /**
   * Load all plugin routes. See the Plugin documentation on
   * how to customize the loading of plugin routes.
   */
  Plugin::routes();
}
else
{
  Router::scope('/', function (RouteBuilder $routes) {
    
    /**
     * The /installer/success is always ok.
     */
    $routes->connect('/installer/success', ['controller' => 'Installer', 'action' => 'success']);
    
    // During installation, user are always redirected to the installer.
    $routes->connect('/*', ['controller' => 'Installer', 'action' => 'index']);
  });
}
