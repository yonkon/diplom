<?php

class Autoloader {
  public static function autoload($namespace) {
    if (strpos($namespace, '\\') === false) {
      return;
    }
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    $path = DIR_FS_DOCUMENT_ROOT;
    $filepath = $path . DIRECTORY_SEPARATOR . $file . '.php';

    if (file_exists($filepath)) {
      require_once($filepath);
    } else {
      $flag = true;
      self::recursive_autoload($file, $path, $flag);
    }
  }

  private static function recursive_autoload($file, $path, &$flag) {
    if (is_dir($path) && FALSE !== ($handle = opendir($path)) && $flag) {
      while (FAlSE !== ($dir = readdir($handle)) && $flag) {
        if (strpos($dir, '.') === FALSE) {
          $path2 = $path . DIRECTORY_SEPARATOR . $dir;
          $filepath = $path2 . DIRECTORY_SEPARATOR . $file . '.php';
          if (file_exists($filepath)) {
            $flag = FALSE;
            require_once($filepath);
            break;
          }
          Autoloader::recursive_autoload($file, $path2, $flag);
        }
      }
      closedir($handle);
    }
  }
}

\spl_autoload_register(__NAMESPACE__  . 'Autoloader::autoload');