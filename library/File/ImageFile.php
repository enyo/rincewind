<?php

/**
 * This file contains the basic ImageFile class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
/**
 * Loading the file class
 */
if ( ! class_exists('File', false)) require dirname(__FILE__) . '/File.php';


/**
 * Loading the file retriever class.
 * They are codependent. Do not remove the include_once.
 */
include_once dirname(__FILE__) . '/ImageFileRetriever.php';

/**
 * The Exception base class for ImageFileException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class ImageFileException extends FileException {
  
}

/**
 * Gets thrown by getRectanglesToResize when the file should just stay the original.
 * 
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class ImageFileTakeOriginalException extends FileException {
  
}

/**
 * Used to define a rectangle in an image.
 */
class ImageRectangle {

  public $x;
  public $y;
  public $width;
  public $height;

  function __construct($x, $y, $width, $height) {
    $this->x = $x;
    $this->y = $y;
    $this->width = $width;
    $this->height = $height;
  }

}

/**
 * You get an ImageFile by calling ImageFileFactory::get($uri).
 * 
 * The documentation of this class is not yet finished. Sorry about that.
 *
 * @see ImageFileFactory
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
class ImageFile extends File {

  protected $width;
  protected $height;
  protected $type;
  protected $image;
  protected $destinationWidth;
  protected $destinationHeight;

  /**
   * Creates an ImageFile instead of File.
   * @return ImageFile
   */
  protected static function getFile($uri) {
    return new ImageFile($uri);
  }

  /**
   * @param int $type One of IMAGETYPE_XXX
   */
  public function setType($type) {
    $this->type = $type;
  }

  public function setWidth($width) {
    $this->width = $width;
  }

  public function setHeight($height) {
    $this->height = $height;
  }

  public function setDestinationWidth($width) {
    $this->destinationWidth = $width;
  }

  public function setDestinationHeight($height) {
    $this->destinationHeight = $height;
  }

  public function getWidth() {
    return $this->width;
  }

  public function getHeight() {
    return $this->height;
  }

  public function getDestinationWidth() {
    return $this->destinationWidth ? $this->destinationWidth : $this->width;
  }

  public function getDestinationHeight() {
    return $this->destinationHeight ? $this->destinationHeight : $this->height;
  }

  /**
   * Returns the image created with imagecreatefromXXX.
   * This method caches the image, so don't be afraid to call it several times.
   */
  public function getImage() {
    if ( ! $this->image) {
      switch ($this->type) {
        case IMAGETYPE_GIF:
          $this->image = imagecreatefromgif($this->uri);
          break;
        case IMAGETYPE_JPEG:
          $this->image = imagecreatefromjpeg($this->uri);
          break;
        case IMAGETYPE_PNG:
          $this->image = imagecreatefrompng($this->uri);
          break;
        default:
          throw new ImageFileException('Unable to extract the image from the file.');
          break;
      }
    }

    return $this->image;
  }

  /**
   * This is the same as File::save() except that it converts the image to another image format if the suffix is another one.
   */
  public function saveOriginal($trgUri, $mode = 0644) {
    $this->saveImage($trgUri, $mode);
  }

  /**
   * Resizes an image and saves it.
   * The proportions are always the maximum size of the image.
   * If crop is set, the image is cropped to have the exact proportions (The viewport is calculated with $cropOffsets).
   * If crop is not set, the image will just be distorted and resized to the new proportions if dontDistort is not set. If it is set, 
   * the image will be scaled so it fits into the new proportions. Thus the resulting image does not have to have the same
   * proportions as specified in the $proportions array.
   * If one of the values in the proportions array is not given, there is no restriction on this length, so it will be caluclated
   * according to the original image proportions. In this case, $crop is always false, and $dontDistort always true.
   * If $proportions is bigger than the original proportions, the file is returned as is.
   *
   * @param string $trgUri The target URI.
   * @param array $proportions An array containing the width and height. eg.: array(120, 80) = 120px width and 80px height.
   * @param bool $crop Whether to crop the image or not.
   * @param bool $dontDistort If this is true, the resulting image will not be distorted but rather proportinally scaled to fit in the new proportions.
   * @param array $cropOffsets An array containing the crop offsets. If they are floats, they are interpreted as percentages. 0.0 means from the most left/top position possible, 100.0 means from the most right/bottom position possible. array(50.0, 50.0) crops from the center of the image.
   * @param int $mode The chmod of the saved file.
   */
  public function saveResized($trgUri, $proportions, $crop = false, $dontDistort = true, $cropOffsets = array(50.0, 20.0), $mode = 0644) {
    $image = $this->getResized($proportions, $crop, $dontDistort, $cropOffsets, $mode);
    $this->saveImage($trgUri, $mode, $image);
  }

  /**
   * Saves the image.
   *
   * @param string $trgUri The URI to save the file to.
   * @param int $mode The file mode.
   * @param resource $image The image generated with getResized for example. If this is null, and the image types are the same, then this will just copy the original file.
   */
  protected function saveImage($trgUri, $mode = 0644, $image = null) {
    $suffix = explode(".", $trgUri);
    $suffix = strtolower(end($suffix));
    $justSave = false;

    switch ($suffix) {
      case 'png':
        if ( ! $image && $this->type == IMAGETYPE_PNG) {
          $justSave = true;
          break;
        }
        if ( ! $image) {
          $image = $this->getImage();
        }
        if ( ! @imagepng($image, $trgUri)) {
          throw new ImageFileException("Could not write to: $trgUri");
        }
        break;
      case 'jpg':
      case 'jpeg':
        if ( ! $image && $this->type == IMAGETYPE_JPEG) {
          $justSave = true;
          break;
        }
        if ( ! $image) {
          $image = $this->getImage();
        }
        if ( ! @imagejpeg($image, $trgUri)) {
          throw new ImageFileException("Could not write to: $trgUri");
        }
        break;
      case 'gif':
        if ( ! $image && $this->type == IMAGETYPE_GIF) {
          $justSave = true;
          break;
        }
        if ( ! $image) {
          $image = $this->getImage();
        }
        if ( ! @imagegif($image, $trgUri)) {
          throw new ImageFileException("Could not write to: $trgUri");
        }
        break;
      default:
        throw new ImageUploadException('Unknown image extension');
    }
    if ($justSave) {
      $this->save($trgUri, $mode);
      $this->uri = $trgUri; // To avoid the problem that file uploads can access the file only once.
    }
    else {
      chmod($trgUri, $mode);
    }
  }

  /**
   * Returns the two rectangles used to resize.
   *
   * @param array $srcProportions
   * @param array $trgProportions
   * @param bool $crop
   * @param bool $dontDistort
   * @param array $cropOffsets
   * @return array Containg to ImageRectangle objects. The first one being the source, the second the target.
   */
  static public function getRectanglesToResize($srcProportions, $trgProportions, $crop, $dontDistort, $cropOffsets) {
    if ( ! $srcProportions[0] || ! $srcProportions[1]) {
      throw new ImageFileException('Wrong source proportions.');
    }
    if ( ! $trgProportions[0] && ! $trgProportions[1]) {
      throw new ImageFileException('Wrong target proportions.');
    }

    $srcRatio = $srcProportions[0] / $srcProportions[1];
    if ( ! $trgProportions[0] || ! $trgProportions[1]) {
      $crop = false;
      $dontDistort = true;
    }

    $trgRatio = $trgProportions[0] / $trgProportions[1];
    if ($srcRatio == $trgRatio) {
      $crop = false;
    }

    $trgWidth = $trgProportions[0];
    $trgHeight = $trgProportions[1];
    $srcWidth = $srcProportions[0];
    $srcHeight = $srcProportions[1];
    $srcX = 0;
    $srcY = 0;

    if ($trgHeight > $srcHeight) $trgHeight = $srcHeight; // Prevent upsizing
    if ($trgWidth > $srcWidth) $trgWidth = $srcWidth; // Prevent upsizing
    $trgRatio = $trgWidth / $trgHeight;

    if ($srcWidth == $trgWidth && $srcHeight == $trgHeight) {
      throw new ImageFileTakeOriginalException(); /* Nothing to be done */
    }



    if ( ! $crop && ! $dontDistort) { /* Just resize the image to the new proportions. Nevermind the ratio. */
    }
    elseif ($crop) {
      if (is_int($cropOffsets[0])) $srcX = $cropOffsets[0];
      if (is_int($cropOffsets[1])) $srcY = $cropOffsets[1];

      $dontDistort = true;
      if ($srcRatio < $trgRatio) {
        // Source is proportionally higher than source. So: scale to width, and cut height.
        $srcHeight = round($srcWidth / $trgRatio);
        if (is_float($cropOffsets[1])) {
          $span = $srcProportions[1] - $srcHeight;
          $srcY = round(($span * $cropOffsets[1]) / 100);
        }
      }
      else {
        // Target is wider. If they are equal $crop as already been set to false.
        $srcWidth = round($srcHeight * $trgRatio);
        if (is_float($cropOffsets[0])) {
          $span = $srcProportions[0] - $srcWidth;
          $srcX = round(($span * $cropOffsets[0]) / 100);
        }
      }
    }
    else {
      if ($trgProportions[0] && $trgProportions[1]) {
        if ($trgRatio == $srcRatio) {
          if ($trgWidth > $srcProportions[0] || $trgHeight > $srcProportions[1]) throw new ImageFileTakeOriginalException(); // To prevent upsizing
        }
        elseif ($srcRatio > $trgRatio) {
          if ($trgWidth > $srcProportions[0]) throw new ImageFileTakeOriginalException(); // To prevent upsizing
          $trgHeight = round($trgWidth / $srcRatio);
        }
        else {
          if ($trgHeight > $srcProportions[1]) throw new ImageFileTakeOriginalException(); // To prevent upsizing
          $trgWidth = round($trgHeight * $srcRatio);
        }
      }
      elseif ($trgProportions[0]) {
        $trgHeight = round($trgWidth / $srcRatio);
      }
      elseif ($trgProportions[1]) {
        $trgWidth = round($trgHeight * $srcRatio);
      }
    }

    return array(new ImageRectangle($srcX, $srcY, $srcWidth, $srcHeight), new ImageRectangle(0, 0, $trgWidth, $trgHeight));
  }

  /**
   * Resizes the original image, and returns it.
   * See saveResized() for a detailed parameter list.
   *
   * @param array $proportions
   * @param bool $crop
   * @param bool $dontDistort
   * @param array $cropOffsets
   * @param int $mode
   */
  protected function getResized($proportions, $crop, $dontDistort, $cropOffsets, $mode) {

    try {
      list($srcRectangle, $trgRectangle) = self::getRectanglesToResize(array($this->width, $this->height), $proportions, $crop, $dontDistort, $cropOffsets, $mode);
    }
    catch (ImageFileTakeOriginalException $e) {
      return null;
    }

    $createAlphaImage = $this->type == IMAGETYPE_PNG;
    $srcImage = $this->getImage();

    if ($createAlphaImage) {
      $newImage = imagecreatetruecolor($trgRectangle->width, $trgRectangle->height);
      imagealphablending($newImage, false);
      imagesavealpha($newImage, true);

      imagealphablending($srcImage, true);
    }
    else {
      $newImage = imagecreatetruecolor($trgRectangle->width, $trgRectangle->height);
    }

    imagecopyresampled($newImage, $srcImage, $trgRectangle->x, $trgRectangle->y, $srcRectangle->x, $srcRectangle->y, $trgRectangle->width, $trgRectangle->height, $srcRectangle->width, $srcRectangle->height);

    return $newImage;
  }

  /**
   * Destroys the image if it has been created
   */
  public function __destruct() {
    if ($this->image) @imagedestroy($this->image);
  }

  /**
   * This is a wrapper for (new ImageFileRetriever())->create()
   *
   * @param mixed $data Is either an URL, or an array from form upload or a local path.
   * @param int $source One of File::SOURCE_XXX
   * @param int $maxFileSize in kilobytes.
   * @return File
   */
  static public function create($data, $source, $maxFileSize = 10000000) {
    $fileRetriever = new ImageFileRetriever();
    return $fileRetriever->create($data, $source, $maxFileSize);
  }

}

