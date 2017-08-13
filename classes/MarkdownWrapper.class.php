<?php

  class MarkdownWrapper {
    const MARKDOWN_LIB    = '/lib/php-markdown/markdown.php';
    const SMARTYPANTS_LIB = '/lib/php-smartypants/smartypants.php';

    public static function transform($text) {
      // Set some config constants so the library can't override them
      if (!defined('MARKDOWN_EMPTY_ELEMENT_SUFFIX')) {
        define('MARKDOWN_EMPTY_ELEMENT_SUFFIX', '>');
      }

      // Require the libraries
      require_once APP_DIR . self::MARKDOWN_LIB;
      require_once APP_DIR . self::SMARTYPANTS_LIB;

      // Parse the text as Markdown, then have SmartyPants curl the quotes
      return SmartyPants(Markdown($text));
    }
  }

?>
