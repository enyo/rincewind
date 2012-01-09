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
  public function render($viewName, Model $model, NotificationCenter $notificationCenter, $output = true) {

    Profile::start('Renderer', 'Generate JSON');

    if (strpos($viewName, 'errors/') === 0) {
      $data = array('error' => substr($viewName, strlen('errors/')));
    }
    else {
      $c = $model->getData(Model::PUBLISHABLE);
      $data = array('content' => count($c) === 0 ? null : $c);
    }

    if (count ($notificationCenter->getErrors())) $data['errorMessages'] = $notificationCenter->getErrors();
    if (count ($notificationCenter->getSuccesses())) $data['successMessages'] = $notificationCenter->getSuccesses();

    $json = json_encode($data);

    $this->setHeader('Content-type: application/json', $output);
    if ($output) {
      echo $json;
    }

    Profile::stop();

    return $output ? null : $json;
  }

}

