<?php

/**
 * This file contains the basic File class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 **/


/**
 * The Exception base class for FileExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class FileException extends Exception { };


/**
 * The file class makes mostly sense in combination with a FileFactory.
 * You shouldn't include this file, but rather include the FileFactory which
 * takes care of dependencies.
 * You then call:
 * <code>
 * <?php
 *   FileFactory::get($uri);
 * ?>
 * </code>
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
class File {

	/**#@+
	 * Source constants that represent all source categories for a file.
	 * They are not set directly, but are used to group the real sources
	 * together.
	 * Test if a source is remote, local or user by using the bit operator &.
	 * Example:
	 * <code>
	 * <?php
	 *   $isRemote = $source & File::SOURCE_REMOTE;
	 * ?>
	 * </code>
	 *
	 * @var int
	 */
	const SOURCE_REMOTE = 0x001000;
	const SOURCE_LOCAL  = 0x010000;
	const SOURCE_USER   = 0x100000;
	/**#@-*/

	/**#@+
	 * Source constants that represent all source types for a file.
	 *
	 * @var int
	 */
	const SOURCE_FILE = 0x010001; // Local
	const SOURCE_FORM = 0x100001; // User
	const SOURCE_HTTP = 0x001001; // Remote
	const SOURCE_FTP  = 0x001010; // Remote
	/**#@-*/

	/**
	 * The file URI.
	 *
	 * @var string
	 */
	protected $uri;


	/**
	 * The content of the file.
	 * When you get a file, and read the content, the file is first read, and the content
	 * put in this variable for caching purposes.
	 * Also: When you set the content manually with setContent, and save the file,
	 * This content is used instead of moving some file.
	 * The FileFactory get method for HTTP sets this content for example.
	 *
	 * @var mixed
	 */
	protected $content;


	/**
	 * Whether the content has been fetched or not.
	 * @var bool
	 */
	protected $fetchedContent = false;

	/**
	 * The filename without the path of the file. This gets automatically set in the constructor
	 * (from the uri), but can be overwritten.
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * The file size in bytes.
	 *
	 * @var int
	 */
	protected $size;
	
	/**
	 * The mime type.
	 *
	 * @var string
	 */
	protected $mimeType;

	/**
	 * The file source. One of File::SOURCE_XXX
	 *
	 * @var int
	 */
	protected $source;

	/**
	 * The constructor sets the uri and the name (generated from the uri).
	 * If the filename is different (eg.: from a form upload), use setName afterwards.
	 *
	 * @param string $uri For example: /tmp/myFile.txt
	 */
	public function __construct($uri) {
		$this->uri = $uri;
		$this->name = basename($uri);
	}

	
	/**
	 * Use this function to set a filename.
	 * This is not necessary if the name is the basename of the uri passed in the constructor.
	 *
	 * @param string $name The filename
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Sets the content of the file
	 *
	 * @param mixed $content
	 */
	public function setContent($content) {
		$this->fetchedContent = true;
		$this->content = $content;
	}

	/**
	 * Set the size of the file.
	 *
	 * @param int $size The file size in bytes.
	 */
	public function setSize($size) {
		$this->size = $size;
	}
	
	/**
	 * Set the mime type of a file.
	 *
	 * @param string $mimeType
	 */
	public function setMimeType($mimeType) {
		$this->mimeType = $mimeType;
	}

	/**
	 * Sets the source of the file.
	 *
	 * @param int $source One of File::SOURCE_XXX .
	 */
	public function setSource($source) {
		$this->source = $source;
	}



	
	/**
	 * Get the file uri set in the constructor.
	 *
	 * @return string
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * Get the content of the file.
	 * If $this->content has not been set, it reads the file.
	 *
	 * @return mixed
	 */
	public function getContent() {
		if (!$this->fetchedContent) {
			$this->setContent(file_get_contents($this->uri));
		}
		return $this->content;
	}



	/**
	 * Get the file name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get the file size.
	 *
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * Get the mime type set in the constructor.
	 *
	 * @return string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}
	
	/**
	 * Get source
	 *
	 * @return int One of File::SOURCE_XXX .
	 */
	public function getSource() {
		return $this->source;
	}
	 
	/**
	 * Saves the file to antoher location.
	 *
	 * @param string $targetUri The target location to save the file to.
	 * @param int $mode The file mode used with chmod.
	 */
	public function save($targetUri, $mode = 0644) {
		if ($this->source == self::SOURCE_FORM) {
			if (!move_uploaded_file ($this->uri, $targetUri)) { throw new FileException ("Possible file upload attack!"); }
		}
		else {
			if ($this->fetchedContent) {
				if (file_put_contents($targetUri, $this->getContent()) === false) throw new FileException ("Failed to save the file...");
			}
			else {
				if (!copy ($this->uri, $targetUri)) { throw new FileException ("Failed to copy the file..."); }
			}
		}
		chmod ($targetUri, $mode);
	}






  /* From here on only static functions to create files */







	/**
	 * After a file gets created, the method creating it calls
	 * process($file), so the file can be processed before it's returned.
	 * If you extend File, you can implement this function.
	 *
	 * @param File $file
	 */
	protected static function process($file) { }


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
				throw new FileException("Your source has to be more specific than that.");
			default:
				throw new FileException("Unknown source.");
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
		if (!is_array ($FILE) || count ($FILE) == 0) { throw new FileException ('The FILE array from the form was not valid.'); }

		if (empty($FILE['tmp_name']) || empty($FILE['size'])) { throw new FileException ('The file was probably larger than allowed by the html property MAX_FILE_SIZE.'); }

		if ($FILE['error'] == UPLOAD_ERR_NO_FILE) { return null; }

		if ($FILE['error'] != 0) {
			switch ($FILE['error']) {
				case UPLOAD_ERR_INI_SIZE:   throw new FileException ('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
				case UPLOAD_ERR_FORM_SIZE:  throw new FileException ('The uploaded file exceeds '.($maxFileSize / 1000).'kB.');
				case UPLOAD_ERR_PARTIAL:    throw new FileException ('The uploaded file was only partially uploaded.');
				case UPLOAD_ERR_NO_FILE:    throw new FileException ('No file was uploaded.');
				case UPLOAD_ERR_NO_TMP_DIR: throw new FileException ('Missing a temporary folder.');
				case UPLOAD_ERR_CANT_WRITE: throw new FileException ('Failed to write file to disk.');
			}
		}

		if ($FILE['size'] > $maxFileSize) {
			throw new FileException ('The uploaded file exceeds '.($maxFileSize / 1000).'kB.');
		}

		$file = new static($FILE['tmp_name']);

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
		if (!is_file($srcUri)) throw new FileException("File '$srcUri' does not exist.");
		$file = new static($srcUri);
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
      throw new FileException('File could not be downloaded. ' . $errorCode . ' - ' . $errorTypes[floor($errorCode / 100) * 100] . '.', $errorCode);
    }

		$file = new static($url);
		$file->setSource(File::SOURCE_HTTP);
		$file->setContent($result);

		static::process($file);

		return $file;
	}





}



?>