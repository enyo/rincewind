<?php

/**
 * This file contains the Json Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the FileDao
 */
include dirname(dirname(__FILE__)) . '/FileDao.php';

/**
 * Loading the Exceptions
 */
include_once dirname(__FILE__) . '/JsonDaoExceptions.php';

/**
 * Loading the JsonResultIterator
 */
include dirname(__FILE__) . '/JsonResultIterator.php';


/**
 * The XmlDao implementation of a FileDao
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class XmlDao extends FileDao {


	/**
	 * Intepretes the xml content, and returns it.
	 * @param string $content The file content
	 * @return array
	 */
	protected function interpretFileContent($content) {
		$return = json_decode($content, true);
		if ($return === null) throw new JsonDaoException("Json could not be decoded.");
		return $return;
	}

	/**
	 * Creates an iterator for a json data hash.
	 *
	 * @param array $data
	 * @return JsonResultIterator
	 */
	protected function createIterator($data) {
		return new JsonResultIterator($data, $this);
	}


}

?>
