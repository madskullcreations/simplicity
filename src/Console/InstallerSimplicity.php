<?php
/**
 * Simplicity composer installation script.
 * 
 */
namespace App\Console;

if (!defined('STDIN')) {
    define('STDIN', fopen('php://stdin', 'r'));
}

use Cake\Utility\Security;
use Composer\Script\Event;
use Exception;

/**
 * 
 */
class InstallerSimplicity
{
  /**
   * An array of directories to be made writable
   */
  const WRITABLE_DIRS = [
      'logs',
      'tmp',
      'tmp/cache',
      'tmp/cache/models',
      'tmp/cache/persistent',
      'tmp/cache/views',
      'tmp/sessions',
      'tmp/tests'
  ];
  
  /**
   * Move the zurb foundation files into place.
   *
   * @param \Composer\Script\Event $event The composer event object.
   * @throws \Exception Exception raised by validator.
   * @return void
   */
  public static function postInstall(Event $event)
  {
    // Root: \vendor\zurb\foundation
    //  Ignore:
    //    assets
    //    _vendor
    //    customizer
    //    docs
    //    gulp
    //    js
    //    scss
    //    test
    //    ...also ignore files in root. 
    // 
    // https://getcomposer.org/doc/articles/scripts.md

    $io = $event->getIO();

    // We are currently in src/Console folder, but need the root folder of the project.
    $rootDir = dirname(dirname(__DIR__));

    // $io->write('__DIR__: '.__DIR__);
    // $io->write('rootDir: '.$rootDir);
    
    // Copy the zurb css and js folder to where it belongs.
    $source = $rootDir."/vendor/zurb/foundation/dist/css/";
    $dest = $rootDir."/webroot/css/zurb/";
    static::xcopy($source, $dest);
    $io->write('Copied Zurb css files from "'.$source.'" to "'.$dest.'".');

    $source = $rootDir."/vendor/zurb/foundation/dist/js/";
    $dest = $rootDir."/webroot/js/zurb/";
    static::xcopy($source, $dest);
    $io->write('Copied Zurb js files from "'.$source.'" to "'.$dest.'".');
    
    // Copy the jquery files.
    $source = $rootDir."/vendor/components/jquery/jquery.min.js";
    $dest = $rootDir."/webroot/js/jquery.min.js";
    static::xcopy($source, $dest);
    $io->write('Copied jQuery js file from "'.$source.'" to "'.$dest.'".');
    
    $source = $rootDir."/vendor/components/jquery/jquery.js";
    $dest = $rootDir."/webroot/js/jquery.js";
    static::xcopy($source, $dest);
    $io->write('Copied jQuery js file from "'.$source.'" to "'.$dest.'".');

    $io->write('Finished Simplicity file copying.');
  }

  /**
   * Copy a file, or recursively copy a folder and its contents
   * @author      Aidan Lister <aidan@php.net>
   * @version     1.0.1
   * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
   * @param       string   $source    Source path
   * @param       string   $dest      Destination path
   * @param       int      $permissions New folder creation permissions
   * @return      bool     Returns true on success, false on failure
   */
  public static function xcopy($source, $dest, $permissions = 0755)
  {
    // Check for symlinks
    if (is_link($source)) {
      return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
      return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
      mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
      // Skip pointers
      if ($entry == '.' || $entry == '..') {
        continue;
      }

      // Deep copy directories
      static::xcopy("$source/$entry", "$dest/$entry", $permissions);
    }

    // Clean up
    $dir->close();
    return true;
  }
}
