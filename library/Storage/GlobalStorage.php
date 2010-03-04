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
class GlobalStorage {

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
	 * Trying to instanciate the class aborts the script.
	 */
	public function __construct() { die('The storage is not ment to be instantiated.'); }

}

?>