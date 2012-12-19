<?php

  class TemplateBuilder {
    // Overly-specific libtidy configuration
    public $tidyConfig = array(
      'alt-text'            => '',
      'anchor-as-name'      => FALSE,
      'join-classes'        => TRUE,
      'join-styles'         => TRUE,
      'numeric-entities'    => TRUE,
      'preserve-entities'   => TRUE,
      'quote-marks'         => TRUE,
      'indent'              => TRUE,
      'indent-spaces'       => 2,
      'vertical-space'      => FALSE,
      'wrap'                => 0,
      'char-encoding'       => 'utf8',
      'input-encoding'      => 'utf8',
      'newline'             => 'LF',
      'output-bom'          => FALSE,
      'output-encoding'     => 'utf8',
      'tidy-mark'           => FALSE,
      // HACK - Make HTML5 tags sorta work properly
      'new-blocklevel-tags' => 'article,aside,audio,canvas,details,figcaption,figure,footer,header,hgroup,menu,nav,output,section,summary,video',
      'new-inline-tags'     => 'abbr,bdi,mark,meter,progress,rp,rt,ruby,time',
      'new-empty-tags'      => 'source'
    );

    private $cfgReader = NULL;
    private $output    = '';

    public function __construct($reader) {
      // Save a copy of the configuration reader for this request
      $this->cfgReader = $reader;
    }

    public function loadTemplate($file) {
      // Determine the full filename and extension
      $file = FilesystemUtils::getFullPath($file, CONTENT_DIR);
      $type = FilesystemUtils::getFileExtension($file);

      switch ($type) {
        case 'tpl':
          // Smarty template
          $this->output = $this->filterSmarty($file);
          break;

        case 'md':
          // Static Markdown file
          $this->output = $this->filterMarkdown($file);
          break;

        case 'tplmd':
          // Smarty template, with the output run through the Markdown parser
          $this->output = $this->filterMarkdown($this->filterSmarty($file), TRUE);
          break;

        case 'php':
          // PHP script
          $this->output = $this->filterPHP($file);
          break;

        case 'html':
        default:
          // HTML or other unsupported file; passed through unchanged
          $this->output = file_get_contents($file);
          break;
      }
    }

    public function send() {
      // Clean up the HTML in the output string
      $tidy = new Tidy();
      $tidy->parseString($this->output, $this->tidyConfig);
      $tidy->cleanRepair();

      // Send the output string to the client
      echo $tidy;
    }

    private function filterSmarty($file) {
      // Run the file through Smarty
      $smarty = SmartyWrapper::getInstance();

      // Set a few global template vars
      $smarty->assign('subtemplate', $this->output);
      $smarty->assign('pathclasses', $this->cfgReader->getPathClasses());
      $smarty->assign('cfg',         $this->cfgReader->getResolved());

      // Return the template's output
      return $smarty->fetch($file);
    }

    private function filterMarkdown($file, $isString = FALSE) {
      // Run the file/string through the Markdown parser
      $text = ($isString ? $file : file_get_contents($file));
      return MarkdownWrapper::transform($text);
    }

    private function filterPHP($file) {
      // Run a PHP script, capturing the output and returning it
      ob_start();

      // The variable $template can be used in the script as $this is used here
      $template = $this;
      require $file;

      // Return the script's output
      return ob_get_clean();
    }
  }

?>