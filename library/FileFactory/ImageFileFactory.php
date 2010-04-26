<?php

/**
 * This file contains the basic ImageFileFile class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 **/

/**
 * Loading the image file class
 */
if (!class_exists('ImageFile')) require('File/ImageFile.php');

/**
 * Loading the file factory class
 */
if (!class_exists('FileFactory')) require('FileFactory/FileFactory.php');

/**
 * The Exception base class for ImageFileFactoryException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class ImageFileFactoryException extends FileFactoryException { };


/**
 * This factory is used to get image files.
 * The difference is, that it returns ImageFiles, instead of normal Files, which
 * have additional image manipulation functions.
 * 
 * @see FileFactory
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
abstract class ImageFileFactory extends FileFactory {

	/**
	 * @var array The allow image types this Factory can handle
	 */
	static protected $allowedImageTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

	/**
	 * @param string $srcUri
	 * @return ImageFile
	 */
	protected static function getFile($srcUri) {
		return new ImageFile($srcUri);	
	}

	/**
	 * @param File $file
	 */
	protected static function processFile($file) {
		if (!($imageInfo = @getimagesize ($file->getUri()))) {
			throw new ImageFileFactoryException ('The uploaded file is not an image, or the file was not readable.');
		}
		if (!in_array($imageInfo[2], self::$allowedImageTypes)) {
			throw new ImageFileFactoryException ('The image type you uploaded is not allowed. Allowed image types are: '.implode (', ', self::$allowedImageTypes).'.');
		}
		$file->setWidth($imageInfo[0]);
		$file->setHeight($imageInfo[1]);
		$file->setType($imageInfo[2]);
	}

}

?>