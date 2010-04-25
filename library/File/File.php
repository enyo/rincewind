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
 * Normally you would get a File from one of the FileFactories getter methods.
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
			if (!copy ($this->uri, $targetUri)) { throw new FileException ("Failed to copy the file..."); }
		}
		chmod ($targetUri, $mode);
	}


}



?>