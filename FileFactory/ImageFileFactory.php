<?php

	require_once('FileFactory/FileFactory.php');
	require_once('File/ImageFile.php');

	class ImageFileFactoryException extends FileFactoryException { }

	class ImageFileFactory extends FileFactory {

		protected $allowedImageTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

		protected function getFile($srcUri) {
			return new ImageFile($srcUri);	
		}

		protected function processFile($file) {
			if (!($imageInfo = @getimagesize ($file->getUri()))) {
				throw new ImageFileFactoryException ('The uploaded file is not an image, or the file was not readable.');
			}
			if (!in_array($imageInfo[2], $this->allowedImageTypes)) {
				throw new ImageFileFactoryException ('The image type you uploaded is not allowed. Allowed image types are: '.implode (', ', $this->allowed_image_types).'.');
			}
			$file->setWidth($imageInfo[0]);
			$file->setHeight($imageInfo[1]);
			$file->setType($imageInfo[2]);
		}

	}

?>