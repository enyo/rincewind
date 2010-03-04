<?php

	/**
	 * The basic implementation of the IniFileConfig
	 *
	 * @author Matthias Loitsch
	 * @copyright Copyright (c) 2010, Matthias Loitsch
	 * @package Config
	 */

	/**
	 * Including the base Class
	 */
	include dirname(__FILE__) . 'Config.php';

	/**
	 * This is the IniFileConfig implementation of Config
	 * It's written to parse default php.ini files to set the config.
	 *
	 * @package Config
	 */
	class IniFileConfig extends Config {
		
		protected $config;
		
		/**
		 * Parses the config file. If a defaultConfigFile is provided, they get merged, and the config file overwrites the default settings.
		 * All variables do not have to exist in the defaultConfig file, but if a section in the defaultConfigFile does not exist, then
		 * the section is ignored in the config file.
		 *
		 * @param string $configFileUri
		 * @param string $defaultConfigFileUri
		 */
		public function __construct($configFileUri, $defaultConfigFileUri = null) {
			if ($defaultConfigFileUri) $this->config = $this->mergeConfigArrays(parse_ini_file($defaultConfigFileUri, true), parse_ini_file($configFileUri, true));
			else                       $this->config = parse_ini_file($configFileUri, true);
		}
		
		/**
		 * Just returns the appropriate indices.
		 *
		 * @param string $variable
		 * @param string $section
		 */
		public function get($variable, $section = null) {
			$variable = $this->sanitizeToken($variable);
			$section = $this->sanitizeToken($section);
			if ($section && $this->section) $this->section = null;
			else $section = $section ? $section : $this->section;
			if (!$section) throw new ConfigException('No section set.');

			if (!isset($this->config[$section]))            throw new ConfigException("Section '$section' does not exist.");
			if (!isset($this->config[$section][$variable])) throw new ConfigException("Variable '$section.$variable' does not exist.");
			
			return $this->config[$section][$variable];
		}

		
		/**
		 * @return array
		 */
		public function getArray() { return $this->config; }
		
		/**
		 * Merges to arrays
		 *
		 * @param array $defaultConfig
		 * @param array $config
		 */
		private function mergeConfigArrays($defaultConfig, $config) {
			foreach ($defaultConfig as $section=>$sectionVariables) {
				if (isset($config[$section])) {
					$sectionVariables = array_merge($sectionVariables, $config[$section]);
					$defaultConfig[$section] = $sectionVariables;
				}
			}
			return $defaultConfig;
		}
		
	}


?>