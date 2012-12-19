<?php

  class FilesystemUtils {
    public static function getFullPath($file, $base = APP_DIR) {
      $abs = realpath($file);              //treat it as absolute
      $rel = realpath("{$base}/{$file}");  //treat it as relative

      if ($abs) {
        // Absolute file exists on the filesystem
        return $abs;

      } else if ($rel) {
        // Relative file exists on the filesystem
        return $rel;

      } else {
        // File could not be found at all
        throw new Exception("File $file does not exist");
      }
    }

    public static function getFileExtension($file) {
      // Return the extension of any file, in lowercase
      return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }
  }

?>