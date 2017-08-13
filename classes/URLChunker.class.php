<?php

  class URLChunker {
    private $requestParts = array();

    public function __construct($request) {
      // Get the path component of the request URI
      $request = parse_url($request, PHP_URL_PATH);

      // Always append 'default' to try index pages, remove double slashes
      $request .= '/default';
      $request = preg_replace('@/+@', '/', $request);

      // Trim leading/trailing slashes off, split the remainder up
      $request = trim($request, '/');
      $this->requestParts = explode('/', $request);
    }

    public function getNextPart() {
      // If we have something to shift off the front of the array, do so
      if (count($this->requestParts) > 0) {
        return array_shift($this->requestParts);

      } else {
        return FALSE;
      }
    }
  }

?>
