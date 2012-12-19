<?php

  class ConfigurationReader {
    const MAX_INCLUDE_DEPTH = 10;

    private $cfgPath  = array();
    private $cfgNodes = array();
    private $lastNode = NULL;

    public function __construct($rootCfg) {
      // Load the root config and store its content
      $config = $this->readFromFile($rootCfg);
      $this->pushConfig($config);
    }

    public function getPathClasses() {
      return implode(' ', $this->cfgPath);
    }

    public function getNodes() {
      // Return the full config array in its native structure
      return $this->cfgNodes;
    }

    public function addRequestPart($requestPart) {
      if (!$this->lastNode->children) {
        // Most recent node has no 'children' property - ignore further calls
        return;

      } if ($this->lastNode->children->{$requestPart}) {
        // Most recent node has a 'children' property with matching subkey
        $this->cfgPath[] = $requestPart;
        $this->pushConfig($this->lastNode->children->{$requestPart});

      } else if ($notfound = $this->findMostRecent('notfound')) {
        // No match in the 'children' property, but a 'notfound' property exists
        $this->cfgPath[] = 'notfound';
        $this->pushConfig($notfound);

      } else {
        // No 'notfound' property, not possible to continue any further
        throw new Exception("Unhandled 404 error.");
      }
    }

    public function findMostRecent($key) {
      // Ascend the config nodes, child to parent
      for ($i = count($this->cfgNodes) - 1; $i >= 0; $i--) {
        // If the desired key is present, return it and stop searching
        $value = $this->cfgNodes[$i]->{$key};
        if ($value) {
          return $value;
        }
      }

      // Key was not present in any of the config nodes
      return NULL;
    }

    public function findAll($key) {
      $result = array();

      // Ascend the config nodes, child to parent
      for ($i = count($this->cfgNodes) - 1; $i >= 0; $i--) {
        // If the desired key is present, push it onto the result array
        $value = $this->cfgNodes[$i]->{$key};
        if ($value) {
          $result[] = $value;
        }
      }

      // Return the result array
      return $result;
    }

    public function getResolved() {
      $config = new ConfigurationNode();

      // Descend the config nodes, parent to child
      foreach ($this->cfgNodes as $node) {
        // Merge each node into the new config, replacing existing properties
        $config->mergeImport($node);
      }

      // Return the new config node
      return $config;
    }

    private function pushConfig($data) {
      // Push a new config node onto the end of the list
      $node = new ConfigurationNode($data);
      $this->cfgNodes[] = $node;
      $this->lastNode   = $node;

      // Send off any headers encountered in this config
      if ($node->headers) {
        foreach ($node->headers as $header) {
          header($header);
        }
      }
    }

    private function readFromFile($file) {
      // Load a JSON file from CONTENT_DIR into an object
      $file = FilesystemUtils::getFullPath($file, CONTENT_DIR);
      $data = json_decode(file_get_contents($file));

      if (!$data) {
        // File could not be read
        $code = $this->lastJsonError();
        throw new Exception("Error $code encountered in $file");
      }

      // Scan for 'include' properties, stop if all are found or max is hit
      for ($i = 0; $i < self::MAX_INCLUDE_DEPTH && $this->scanForIncludes($data); $i++);

      // Convert all StdClass objects in the config into ConfigurationNode
      $this->wrapNodes($data);

      // Return the object representation of this JSON file and its includes
      return $data;
    }

    private function lastJsonError() {
      // json_last_error() returns a numeric constant, useless for our message
      $code = function_exists('json_last_error') ? json_last_error() : NULL;
      $constList = get_defined_constants(TRUE);

      // Search the JSON_ERROR_* constants and return the first match
      foreach ($constList['json'] as $name => $value) {
        if (strpos($name, 'JSON_ERROR_') === 0 && $value == $code) {
          return $name;
        }
      }

      // Hopefully will never get here
      return '[unknown]';
    }

    private function scanForIncludes(&$data) {
      // Flag -- Did this call encounter an 'include' property?
      $includeEncountered = FALSE;

      // Loop over each top-level property in the object
      foreach ($data as $key => $value) {
        if (is_object($value)) {
          // This property is a descendant object; recurse into it
          $includeEncountered = $this->scanForIncludes($value);

        } else if ($key == 'include') {
          // Not an object, but the property key is 'include'

          // Remove the 'include' property so it is not evaluated again
          unset($data->include);

          // Loop over each property in the included file, add it to the object
          foreach ($this->readFromFile($value) as $newKey => $newValue) {
            $data->{$newKey} = $newValue;
          }

          // An include was encountered, bail out and set the caller's flag
          return TRUE;
        }
      }

      // Return the flag
      return $includeEncountered;
    }

    private function wrapNodes(&$data) {
      // Loop over each top-level property in the object
      foreach ($data as $key => $value) {
        if ($value instanceof StdClass) {
          // This property is a StdClass; convert to ConfigurationNode
          $data->{$key} = new ConfigurationNode($value);

          // Recursively search and convert any descendant objects
          $this->wrapNodes($data->{$key});
        }
      }
    }
  }

?>