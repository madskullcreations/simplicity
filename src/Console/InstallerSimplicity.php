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
   * Move various files into place: 
   *  Zurb foundation files
   *  Tinymce files
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
    // Root: \vendor\tinymce\tinymce
    //  Ignore: 
    //   nothing at the moment.
    // 
    // https://getcomposer.org/doc/articles/scripts.md

    $io = $event->getIO();

    // We are currently in src/Console folder, but need the root folder of the project.
    $rootDir = dirname(dirname(__DIR__));

    // $io->write('__DIR__: '.__DIR__);
    // $io->write('rootDir: '.$rootDir);
    
    {
      // If app.php does not exist, copy from app.default.php. 
      // This happen in InstallerController as well, but the file must exist for CakePHPs normal page load routine.
      static::createAppConfig($rootDir, $io);
      static::createWritableDirectories($rootDir, $io);
      
      // ask if the permissions should be changed
      if ($io->isInteractive()) 
      {
        $validator = function ($arg) {
          if (in_array($arg, ['Y', 'y', 'N', 'n'])) 
          {
            return $arg;
          }
          throw new Exception('This is not a valid answer. Please choose Y or n.');
        };
        
        $setFolderPermissions = $io->askAndValidate(
            '<info>Set Folder Permissions ? (Default to Y)</info> [<comment>Y,n</comment>]? ',
            $validator,
            10,
            'Y'
        );

        if (in_array($setFolderPermissions, ['Y', 'y'])) 
        {
          static::setFolderPermissions($rootDir, $io);
        }
      } 
      else 
      {
        static::setFolderPermissions($rootDir, $io);
      }
    }
        
    {
      // Copy the zurb css and js folder to where it belongs.
      $source = $rootDir."/vendor/zurb/foundation/dist/css/";
      $dest = $rootDir."/webroot/css/zurb/";
      static::xcopy($source, $dest);
      $io->write('Copied Zurb css files from "'.$source.'" to "'.$dest.'".');

      $source = $rootDir."/vendor/zurb/foundation/dist/js/";
      $dest = $rootDir."/webroot/js/zurb/";
      static::xcopy($source, $dest);
      $io->write('Copied Zurb js files from "'.$source.'" to "'.$dest.'".');
    }
    
    { 
      // Copy the jquery files.
      $source = $rootDir."/vendor/components/jquery/jquery.min.js";
      $dest = $rootDir."/webroot/js/jquery.min.js";
      static::xcopy($source, $dest);
      $io->write('Copied jQuery js file from "'.$source.'" to "'.$dest.'".');
      
      $source = $rootDir."/vendor/components/jquery/jquery.js";
      $dest = $rootDir."/webroot/js/jquery.js";
      static::xcopy($source, $dest);
      $io->write('Copied jQuery js file from "'.$source.'" to "'.$dest.'".');
    }
    
    {
      // Copy the tinymce folder to the project js folder.
      $source = $rootDir."/vendor/tinymce/tinymce/";
      $dest = $rootDir."/webroot/js/tinymce/";
      static::xcopy($source, $dest);
      $io->write('Copied Tinymce files from "'.$source.'" to "'.$dest.'".');
    }
    
    {
      // Copy the cakephp/localized files to the Locale folder.
      $source = $rootDir."/vendor/cakephp/localized/src/Locale/";
      $dest = $rootDir."/src/Locale/";
      static::xcopy($source, $dest);
      $io->write('Copied Locale translation files from "'.$source.'" to "'.$dest.'".');
    }
    
    $io->write('Finished Simplicity file copying.');
  }

  /**
   * Create the config/app.php file if it does not exist.
   *
   * @param string $dir The application's root directory.
   * @param \Composer\IO\IOInterface $io IO interface to write to console.
   * @return void
   */
  public static function createAppConfig($dir, $io)
  {
    $appConfig = $dir . '/config/app.php';
    $defaultConfig = $dir . '/config/app.default.php';
    if (!file_exists($appConfig)) {
      copy($defaultConfig, $appConfig);
      $io->write('Created `config/app.php` file');
    }
  }
  
  /**
   * Create the `logs` and `tmp` directories.
   *
   * @param string $dir The application's root directory.
   * @param \Composer\IO\IOInterface $io IO interface to write to console.
   * @return void
   */
  public static function createWritableDirectories($dir, $io)
  {
    foreach (static::WRITABLE_DIRS as $path) {
      $path = $dir . '/' . $path;
      if (!file_exists($path)) {
        mkdir($path);
        $io->write('Created `' . $path . '` directory');
      }
    }
  }
  
  /**
   * Set globally writable permissions on the "tmp" and "logs" directory.
   *
   * This is not the most secure default, but it gets people up and running quickly.
   *
   * @param string $dir The application's root directory.
   * @param \Composer\IO\IOInterface $io IO interface to write to console.
   * @return void
   */
  public static function setFolderPermissions($dir, $io)
  {
    // Change the permissions on a path and output the results.
    $changePerms = function ($path, $perms, $io) {
        // Get permission bits from stat(2) result.
        $currentPerms = fileperms($path) & 0777;
        if (($currentPerms & $perms) == $perms) {
            return;
        }

        $res = chmod($path, $currentPerms | $perms);
        if ($res) {
            $io->write('Permissions set on ' . $path);
        } else {
            $io->write('Failed to set permissions on ' . $path);
        }
    };

    $walker = function ($dir, $perms, $io) use (&$walker, $changePerms) {
      $files = array_diff(scandir($dir), ['.', '..']);
      foreach ($files as $file) {
        $path = $dir . '/' . $file;

        if (!is_dir($path)) {
          continue;
        }

        $changePerms($path, $perms, $io);
        $walker($path, $perms, $io);
      }
    };

    $worldWritable = bindec('0000000111');
    $walker($dir . '/tmp', $worldWritable, $io);
    $changePerms($dir . '/tmp', $worldWritable, $io);
    $changePerms($dir . '/logs', $worldWritable, $io);
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
