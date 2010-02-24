<?php

	require_once('File/File.php');

	class FileFactoryException extends FileException { };


	/**
	 * This factory is used to get files.
	 * The 3 common types of getting files is from a form upload, a local file or an URL.
	 * The factory makes sure that all File relevant info is set in the file object.
	 */
	class FileFactory {

		protected $maxFileSize = 10000000;

		/**
		 * This function returns a file. If you need to return another type of file (eg.: ImageFile), overwrite it.
		 *
		 * @param string $fileUri
		 */
		protected function getFile($fileUri) {
			return new File($fileUri);
		}


		/**
		 * To set another maximum file size for file uploads.
		 *
		 * @param int $size The size in bytes.
		 */
		protected function setMaxFileSize($size) {
			$this->maxFileSize = $size;
		}


		/**
		 * After a file gets created with the factory, the method creating it calls processFile($file), so the file can be processed before it's returned.
		 *
		 * @param File $file
		 */
		protected function processFile($file) { }

		/**
		 * This static function is the way to get a file from a form upload.
		 *
		 * @param array $FILE the array obtained from $_FILES
		 */
		public function getFromFormUpload($FILE) {
			if (!is_array ($FILE) || count ($FILE) == 0) { throw new FileFactoryException ('The FILE array from the form was not valid.'); }
	
			if (empty($FILE['tmp_name']) || empty($FILE['size'])) { throw new FileFactoryException ('The file was probably larger than allowed by the html property MAX_FILE_SIZE.'); }
	
			if ($FILE['error'] == UPLOAD_ERR_NO_FILE) { return null; }
	
			if ($FILE['error'] != 0) {
				switch ($FILE['error']) {
					case UPLOAD_ERR_INI_SIZE:   throw new FileFactoryException ('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
					case UPLOAD_ERR_FORM_SIZE:  throw new FileFactoryException ('The uploaded file exceeds '.($this->maxFileSize / 1000).'kB.');
					case UPLOAD_ERR_PARTIAL:    throw new FileFactoryException ('The uploaded file was only partially uploaded.');
					case UPLOAD_ERR_NO_FILE:    throw new FileFactoryException ('No file was uploaded.');
					case UPLOAD_ERR_NO_TMP_DIR: throw new FileFactoryException ('Missing a temporary folder.');
					case UPLOAD_ERR_CANT_WRITE: throw new FileFactoryException ('Failed to write file to disk.');
				}
			}
	
			if ($FILE['size'] > $this->maxFileSize) {
				throw new FileFactoryException ('The uploaded file exceeds '.($this->maxFileSize / 1000).'kB.');
			}

			$file = $this->getFile($FILE['tmp_name']);

			$file->setSource(File::SOURCE_FORM);
			$file->setName($FILE['name']);
			$file->setSize($FILE['size']);
			$file->setMimeType($FILE['type']);

			$this->processFile($file);
			
			return $file;
		}

		public function getFromLocalFile($srcUri) {
			$file = $this->getFile($srcUri);
			$file->setSource(File::SOURCE_FILE);
			$this->processFile($file);
			return $file;
		}

		public function getFromUrl($srcUri) {
			// TODO: finish
			throw new FileFactoryException('Not implemented yet');
			$this->processFile($file);
			return $file;
		}

	}



?>