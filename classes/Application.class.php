<?php

  class Application {
    const ROOT_CONFIG_FILE = 'root.json';

    private $chunker   = NULL;
    private $cfgReader = NULL;
    private $template  = NULL;

    public function __construct($requestURL = '') {
      header('X-Powered-By: Docroute (http://www.scottsmitelli.com/projects/docroute)');

      // Classes to split requests URLs up and build config paths from them
      $this->chunker   = new URLChunker($requestURL);
      $this->cfgReader = new ConfigurationReader(self::ROOT_CONFIG_FILE);

      // Read path components (left to right) and add each to the config reader
      while ($part = $this->chunker->getNextPart()) {
        $this->cfgReader->addRequestPart($part);
      }

      // Create a template builder and pass the current config reader
      $this->template = new TemplateBuilder($this->cfgReader);

      // Ascend the template chain (child to parent) and load each file
      $tplChain = $this->cfgReader->findAll('template');
      foreach ($tplChain as $tpl) {
        $this->template->loadTemplate($tpl);
      }

      // Send the combined output to the client
      $this->template->send();
    }
  }

?>