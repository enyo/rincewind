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
class JsonRenderer extends BaseRenderer {

  static public $templateFileExtension = null;

  static public $acceptedContentTypes = array('application/json');


  /**
   * {@inheritdoc}
   */
  public function render($viewName, Model $model, $output = true) {

    Profile::start('Renderer', 'Generate JSON');

    $json = json_encode($model->getData(Model::PUBLISHABLE));


    $this->setHeader('Content-type: application/json', $output);
    if ($output) {
      echo $json;
    }

    Profile::stop();

    return $output ? null : $json;
  }

}

