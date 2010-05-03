<?php

/**
 * This file contains the basic FileRetriever class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 **/



/**
 * Loading the file class.
 * They are codependent so this has to be include_once to avoid an endless loop.
 */
include_once('File/File.php');



/**
 * The Exception base class for FileRetrieverExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileRetrieverExceptions
 */
class FileRetrieverException extends FileException { };


/**
 * The FileRetriever actually fetches a files, and returns the File object.
 * It's basically a list of static functions, which the File class extends,
 * so normally you don't call them on the FileRetriever but rather the File
 * class itself.
 * The FileRetriever is a class on it's own, so it can be passed to other
 * objects, so they don't have to call the static File:: functions (which
 * makes them rather difficult to test.)
 *
 * @see File
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
class FileRetriever {


	/**
	 * After a file gets created, the method creating it calls
	 * process($file), so the file can be processed before it's returned.
	 * If you extend File, you can implement this function.
	 *
	 * @param File $file
	 */
	protected static function process($file) { }


  /**
   * Creates a file, and returns it.
   * @return $File
   */
  protected static function getFile($uri) {
    return new File($uri);
  }


	/**
	 * This is the way to create a file.
	 *
	 * @param mixed $data Is either an URL, or an array from form upload or a local path.
	 * @param int $source One of File::SOURCE_XXX
	 * @param int $maxFileSize in kilobytes.
	 * @return File
	 */
	public static function create($data, $source, $maxFileSize = 10000000) {
		switch ($source) {
			case File::SOURCE_FILE:
				return self::createFromLocalFile($data);
				break;
			case File::SOURCE_FORM:
				return self::createFromFormUpload($data, $maxFileSize);
				break;
			case File::SOURCE_HTTP:
				return self::createFromHTTP($data);
				break;
			case FILE::SOURCE_LOCAL:
			case FILE::SOURCE_REMOTE:
			case FILE::SOURCE_USER:
				throw new FileRetrieverException("Your source has to be more specific than that.");
			default:
				throw new FileRetrieverException("Unknown source.");
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
	public static function createFromFormUpload($FILE, $maxFileSize = 100000) {
		if (!is_array ($FILE) || count ($FILE) == 0) { throw new FileRetrieverException ('The FILE array from the form was not valid.'); }

		if (empty($FILE['tmp_name']) || empty($FILE['size'])) { throw new FileRetrieverException ('The file was probably larger than allowed by the html property MAX_FILE_SIZE.'); }

		if ($FILE['error'] == UPLOAD_ERR_NO_FILE) { return null; }

		if ($FILE['error'] != 0) {
			switch ($FILE['error']) {
				case UPLOAD_ERR_INI_SIZE:   throw new FileRetrieverException ('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
				case UPLOAD_ERR_FORM_SIZE:  throw new FileRetrieverException ('The uploaded file exceeds '.($maxFileSize / 1000).'kB.');
				case UPLOAD_ERR_PARTIAL:    throw new FileRetrieverException ('The uploaded file was only partially uploaded.');
				case UPLOAD_ERR_NO_FILE:    throw new FileRetrieverException ('No file was uploaded.');
				case UPLOAD_ERR_NO_TMP_DIR: throw new FileRetrieverException ('Missing a temporary folder.');
				case UPLOAD_ERR_CANT_WRITE: throw new FileRetrieverException ('Failed to write file to disk.');
			}
		}

		if ($FILE['size'] > $maxFileSize) {
			throw new FileRetrieverException ('The uploaded file exceeds '.($maxFileSize / 1000).'kB.');
		}

		$file = static::getFile($FILE['tmp_name']);

		$file->setSource(File::SOURCE_FORM);
		$file->setName($FILE['name']);
		$file->setSize($FILE['size']);
		$file->setMimeType($FILE['type']);

		static::process($file);
		
		return $file;
	}

	/**
	 * This static function is the way to get a file from a local file.
	 *
	 * @param array $srcUri the location of the file
	 * @deprecated Use get() instead.
	 * @return File
	 */
	public static function createFromLocalFile($srcUri) {
		if (!is_file($srcUri)) throw new FileRetrieverException("File '$srcUri' does not exist.");
		$file = static::getFile($srcUri);
		$file->setSource(File::SOURCE_FILE);
		$file->setSize(filesize($srcUri));
		static::process($file);
		return $file;
	}

	/**
	 * This static function is the way to get a file from a http source.
	 *
	 * @param array $url the location of the file
	 * @param int $port
	 * @param int $timeout in seconds
	 * @deprecated Use get() instead.
	 * @return File
	 */
	public static function createFromHTTP($url, $port = 80, $timeout = 30) {

    $curlHandle = curl_init();

		curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_PORT, $port);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
		curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
		// return into a variable
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curlHandle);

    if ($result === false) $info = curl_getinfo($curlHandle);

		curl_close($curlHandle);

    if ($result === false) {

			$errorCode = $info['http_code'] ? $info['http_code'] : 400;
      $errorTypes = array(400=>'Bad Request', 500=>'Internal Server Error');
      throw new FileRetrieverException('File could not be downloaded. ' . $errorCode . ' - ' . $errorTypes[floor($errorCode / 100) * 100] . '.', $errorCode);
    }

		$file = static::getFile($url);
		$file->setSource(File::SOURCE_HTTP);
		$file->setContent($result);

		static::process($file);

		return $file;
	}



}



?>