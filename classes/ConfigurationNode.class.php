<?php

  class ConfigurationNode implements Iterator {
    private $pos  = 0;
    private $data = array();
    private $keys = array();

    public function __construct($importData = FALSE) {
      // If data was supplied to the constructor, merge it in
      if ($importData) $this->mergeImport($importData);
    }

    public function __set($key, $value) {
      // Add a key-value pair to this object
      $this->pos = 0;
      $this->data[$key] = $value;
      $this->keys = array_keys($this->data);
    }

    public function __get($key) {
      // Return the data in the named key, or NULL if the key is not defined
      return (isset($this->data[$key]) ? $this->data[$key] : NULL);
    }

    public function mergeImport($importData) {
      // Load new data over the existing data, overwriting keys that exist
      foreach ($importData as $key => $value) {
        $this->{$key} = $value;
      }
    }

    public function valid() {
      // We haven't gone past the end of the list, right?
      return isset($this->keys[$this->pos]);
    }

    public function key() {
      // Return the key name at the current position
      return $this->keys[$this->pos];
    }

    public function current() {
      // Return the data at the current position
      return $this->data[$this->key()];
    }

    public function next() {
      // Move the position cursor ahead
      $this->pos++;
    }

    public function rewind() {
      // Reset the position cursor
      $this->pos = 0;
    }
  }

?>