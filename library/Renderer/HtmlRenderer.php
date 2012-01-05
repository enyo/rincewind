<?php

/**
 * The file for the JsonRenderer class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
/**
 * Including the Renderer
 */
require_class('BaseRenderer', 'Renderer');

/**
 * The JsonRenderer is the json implementation of a Renderer.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class HtmlRenderer extends BaseRenderer {

  static public $templateFileExtension = 'html';

  static public $acceptedContentTypes = array('text/html', '*');


  /**
   * {@inheritdoc}
   */
  public function render($viewName, Model $model, NotificationCenter $notificationCenter, $output = true) {
    Profile::start('Renderer', 'Generate HTML');

    $filename = $this->templatesPath . $viewName . '.' . static::$templateFileExtension;

    if ($output) {
      $html = null;
      readfile($filename);
    }
    else {
      $html = file_get_contents($filename);
    }

    Profile::stop();

    return $html;
  }

}

