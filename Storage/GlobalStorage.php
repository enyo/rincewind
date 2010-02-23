<?php


	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 *
	 * This is the global storage.
	 * Some objects are already implemented, because other library classes may try
	 * to access them (eg: the profiler).
	 * If you need other / more stuff to store, extend this storage.
	 *
	 */
	class GlobalStorage {

		/**
		 * A profiler to be used anywhere in the application.
		 *
		 * @var Profiler $profiler
		 */
		static protected $profiler;


		public static function setProfiler($profiler) { self::$profiler = $profiler; }
		
		public static function getProfiler() { return self::$profiler; }



		public function __construct() { die('The storage is not ment to be instantiated.'); }

	}



?>