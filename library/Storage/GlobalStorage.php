<?php

/**
 * This file contains the definition for the global Storage.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Storage
 **/


/**
 * This is the global storage.
 * Some objects are already implemented for common usage.
 * If you need other / more stuff to store, extend this storage.
 * All functions in this class have to be static.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Storage
 */
abstract class GlobalStorage {

	/**
	 * A profiler to be used anywhere in the application.
	 *
	 * @var Profiler $profiler
	 */
	static protected $profiler;

	/**
	 * Sets the profiler
	 *
	 * @param Profiler $profiler
	 */
	public static function setProfiler($profiler) {
		self::$profiler = $profiler;
	}
	
	/**
	 * Returns the profiler
	 *
	 * @return Profiler
	 * @see setProfiler
	 */
	public static function getProfiler() {
		return self::$profiler;
	}


  /**
   * @var array A list of already included files.
   * @see includeOnce()
   */
  protected static $includedFiles = array();

  /**
   * Since include_once and require_once are so incredibly slow, this function
   * fills the includedFiles array with the filenames.
   *
   * Downside: Your include files must have the exact same path & name. (Including ../myFile.php
   * and ./myFile.php will result in including the file twice, even though it's referencing
   * the same file.)
   *
   * Upside: It's much faster than include_once()
   *
   * @param string $fileUri
   * @return bool True if the file has been included, false if not.
   */
  public static function includeOnce($fileUri) {
    if (!in_array($fileUri, self::$includedFiles)) {
      include($fileUri);
      self::$includedFiles[] = $fileUri;
      return true;
    }
    return false;
  }

}

?>