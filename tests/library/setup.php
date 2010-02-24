<?php

	define('TESTS_ROOT_PATH', dirname(dirname(__FILE__)) . '/');

	define('LIBRARY_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/library/');


	ini_set('include_path', '.:' . LIBRARY_ROOT_PATH);


	$conf = parse_ini_file(TESTS_ROOT_PATH . '/library/local.conf', true);

	foreach ($conf as $section=>$values) {
		foreach ($values as $name=>$value) {
			define(strtoupper('CONF_' . $section . '_' . $name), $value);	
		}
	}


		ini_set('error_reporting', E_ALL | E_STRICT);
		ini_set('display_errors', 1);
		ini_set('log_errors', 0);

?>