<?php

/**
 * This file contains the basic ImageFileRetriever class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 **/



/**
 * Loading the file class.
 * They are codependent so this has to be include_once to avoid an endless loop.
 */
include_once(dirname(__FILE__) . '/ImageFile.php');



/**
 * The Exception base class for ImageFileRetrieverExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileRetrieverExceptions
 */
class ImageFileRetrieverException extends FileRetrieverException { };


/**
 * The ImageFileRetriever mainly does the same thing as the FileRetriever
 * 
 * The process() and getFile() methods differ.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
class ImageFileRetriever extends FileRetriever {


  /**
   * @var array The allow image types this Factory can handle
   */
  protected $allowedImageTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

  /**
   * @param File $file
   */
  protected function process($file) {
    if (!($imageInfo = @getimagesize ($file->getUri()))) {
      throw new ImageFileException ('The uploaded file is not an image, or the file was not readable.');
    }
    if (!in_array($imageInfo[2], $this->allowedImageTypes)) {
      throw new ImageFileException ('The image type you uploaded is not allowed. Allowed image types are: '.implode (', ', $this->$allowedImageTypes).'.');
    }
    $file->setWidth($imageInfo[0]);
    $file->setHeight($imageInfo[1]);
    $file->setType($imageInfo[2]);
  }


  /**
   * Creates a file, and returns it.
   * @return File
   */
  protected function getFile($uri) {
    return new ImageFile($uri);
  }

}



?>
