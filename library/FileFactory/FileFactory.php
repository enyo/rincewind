<?php


/**
 * This file contains the basic FileFactory class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 **/

/**
 * Loading the file class
 */
require('File/File.php');

/**
 * The Exception base class for FileFactoryExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class FileFactoryException extends FileException { };


/**
 * This factory is used to get files.
 * The 3 common types of getting files is from a form upload, a local file or an URL.
 * The factory makes sure that all File relevant info is set in the file object.
 * Typical usage:
 * <code>
 * <?php
 *   $file1 = FileFactory::get('test.txt', File::SOURCE_LOCAL);
 *   $file2 = FileFactory::get('http://www.google.com/', File::SOURCE_HTTP);
 * ?>
 * </code>
 *
 * NOTE: If you extend this class, you *have to* define getFile() and processFile()
 * due to the nature of static::
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
class FileFactory {

	/**
	 * This function returns a file.
	 * If you extend FileFactory, you have to implement this function, and you can
	 * return a different File type (eg: ImageFile) if necessary.
	 *
	 * @param string $fileUri
	 * @return File
	 */
	protected static function getFile($fileUri) {
		return new File($fileUri);
	}


	/**
	 * After a file gets created with the factory, the method creating it calls
	 * processFile($file), so the file can be processed before it's returned.
	 * If you extend FileFactory, you have to implement this function.
	 *
	 * @param File $file
	 */
	protected static function processFile($file) { }


	/**
	 * This is the way to get a file from the factory.
	 *
	 * @param mixed $data Is either an URL, or an array from form upload or a local path.
	 * @param int $source One of File::SOURCE_XXX
	 * @param int $maxFileSize in kilobytes.
	 * @return File
	 */
	public static function get($data, $source, $maxFileSize = 100000) {
		switch ($source) {
			case File::SOURCE_FILE:
				return self::getFromLocalFile($data, $maxFileSize);
				break;
			case File::SOURCE_FORM:
				return self::getFromFormUpload($data, $maxFileSize);
				break;
			case File::SOURCE_HTTP:
				return self::getFromHTTP($data, $maxFileSize);
				break;
			case FILE::SOURCE_LOCAL:
			case FILE::SOURCE_REMOTE:
			case FILE::SOURCE_USER:
				throw new FileFactoryException("Your source has to be more specific than that.");
			default:
				throw new FileFactoryException("Unknown source.");
		}
	}


	/**
	 * This static function is the way to get a file from a form upload.
	 *
	 * @param array $FILE the array obtained from $_FILES
	 * @param int $maxFileSize in kiloBytes
	 * @deprecated use get() instead
	 * @return File
	 */
	public static function getFromFormUpload($FILE, $maxFileSize = 100000) {
		if (!is_array ($FILE) || count ($FILE) == 0) { throw new FileFactoryException ('The FILE array from the form was not valid.'); }

		if (empty($FILE['tmp_name']) || empty($FILE['size'])) { throw new FileFactoryException ('The file was probably larger than allowed by the html property MAX_FILE_SIZE.'); }

		if ($FILE['error'] == UPLOAD_ERR_NO_FILE) { return null; }

		if ($FILE['error'] != 0) {
			switch ($FILE['error']) {
				case UPLOAD_ERR_INI_SIZE:   throw new FileFactoryException ('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
				case UPLOAD_ERR_FORM_SIZE:  throw new FileFactoryException ('The uploaded file exceeds '.($maxFileSize / 1000).'kB.');
				case UPLOAD_ERR_PARTIAL:    throw new FileFactoryException ('The uploaded file was only partially uploaded.');
				case UPLOAD_ERR_NO_FILE:    throw new FileFactoryException ('No file was uploaded.');
				case UPLOAD_ERR_NO_TMP_DIR: throw new FileFactoryException ('Missing a temporary folder.');
				case UPLOAD_ERR_CANT_WRITE: throw new FileFactoryException ('Failed to write file to disk.');
			}
		}

		if ($FILE['size'] > $maxFileSize) {
			throw new FileFactoryException ('The uploaded file exceeds '.($maxFileSize / 1000).'kB.');
		}

		$file = static::getFile($FILE['tmp_name']);

		$file->setSource(File::SOURCE_FORM);
		$file->setName($FILE['name']);
		$file->setSize($FILE['size']);
		$file->setMimeType($FILE['type']);

		static::processFile($file);
		
		return $file;
	}

	/**
	 * This static function is the way to get a file from a local file.
	 *
	 * @param array $srcUri the location of the file
	 * @deprecated Use get() instead.
	 * @return File
	 */
	public static function getFromLocalFile($srcUri) {
		if (!is_file($srcUri)) throw new FileFactoryException("File '$srcUri' does not exist.");
		$file = static::getFile($srcUri);
		$file->setSource(File::SOURCE_FILE);
		$file->setSize(filesize($srcUri));
		static::processFile($file);
		return $file;
	}

	/**
	 * This static function is the way to get a file from a http source.
	 *
	 * @param array $srcUri the location of the file
	 * @deprecated Use get() instead.
	 * @return File
	 */
	public static function getFromHTTP($srcUri) {
		// TODO: finish
		throw new FileFactoryException('Not implemented yet');
		static::processFile($file);
		return $file;
	}

}



?>