<?php


	class DateException extends Exception { }

	/**
	 * The Date object is used to store the date and time.
	 * I tried extending the built in DateTime object, but overriding the constructor yielded unpleasant results.
	 *
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */
	class Date {
	
		/**
		 * This format is used with date() when formatting the date.
		 * Use setFormat() to overwrite it.
		 *
		 * @var string
		 */
		protected $defaultFormat = 'Y-m-d H:i:s';
		
		/**
		 * The time of the date object.
		 *
		 * @var int
		 */
		protected $timestamp;

		/**
		 * The time is specified in the constructor
		 *
		 * @param mixed $time Integer for a timestamp, String for strtotime() or null for current time.
		 **/
		public function __construct($time = null) {
			if (!$time)                                        $this->timestamp = time();
			elseif (is_numeric($time) && $time == (int) $time) $this->timestamp = (int) $time;
			else                                               $this->timestamp = strtotime($time);
			if (!$this->timestamp) throw new DateException('This date could not be converted: "' . $time . '"');
		}

		/**
		 * Formats the timestamp, and returns it.
		 *
		 * @param string $format The format to be used (the string passed to date()). If empty $this->defaultFormat is used.
		 **/
		public function format($format = null) {
			return date($format === null ? $this->defaultFormat : $format, $this->timestamp);
		}

		/**
		 * @param int $timestamp
		 */
		public function setTimestamp($timestamp) {
			$this->timestamp = (int) $timestamp;	
		}


		/**
		 * @param int $timestamp
		 */
		public function set($year, $month, $day, $hour = 0, $minute = 0, $second = 0) {
			$this->timestamp = mktime((int) $hour, (int) $minute, (int) $second, (int) $month, (int) $day, (int) $year);
		}



		/**
		 * Simply returns the timestamp.
		 */
		public function getTimestamp() { return $this->timestamp; }

		/**
		 * @param string $format Sets the defaultFormat.
		 */
		public function setFormat($format) { $this->defaultFormat = $format; }




		public function __toString() { return $this->format(); }
	
	}
	

?>
