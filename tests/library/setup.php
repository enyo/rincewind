<?php

	define('TESTS_ROOT_PATH', dirname(dirname(__FILE__)) . '/');

	define('LIBRARY_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/library/');


	ini_set('include_path', '.:' . LIBRARY_ROOT_PATH);


	if (!is_file(TESTS_ROOT_PATH . '/library/local.conf')) die('No local.conf file!');

	$conf = parse_ini_file(TESTS_ROOT_PATH . '/library/local.conf', true);

	foreach ($conf as $section=>$values) {
		foreach ($values as $name=>$value) {
			define(strtoupper('CONF_' . $section . '_' . $name), $value);	
		}
	}


?>
