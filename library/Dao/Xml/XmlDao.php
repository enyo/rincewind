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
 * Loading the XmlResultIterator
 */
include dirname(__FILE__) . '/XmlResultIterator.php';



/**
 * The Exception base class for XmlDaoException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class XmlDaoException extends DaoException { }



/**
 * The XmlDao implementation of a FileDao
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class XmlDao extends FileDao {


	/**
	 * Intepretes the xml content, and returns it as array.
	 *
	 * Parsing XML is not pretty.
	 *
	 * @param string $content The file content
	 * @return array
	 */
	protected function interpretFileContent($content) {
		$values = array();
		$tags = array();
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_SKIP_TAGSTART, 0);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		if (!xml_parse_into_struct($parser, $content, $values, $tags)) {
			throw new XmlDaoException('Could not parse XML for table ' . $this->tableName. '.');
		}
		xml_parser_free($parser);

		$rowCount = 0;
		$itemList = array();

		$error = false;
		$errorMessages = array();

		foreach ($tags as $key=>$value) {
			if ($key == $this->tableName . 'Entry') {
				$count = 0;
				$items = $value;
				// There's always the start and the end... therefore: every 2 items
				for ($i=0; $i < count($items); $i+=2)
				{
					if (!isset($items[$i]) || !isset($items[$i + 1])) { file_put_contents('/tmp/TTT', $content); throw new XmlDaoException('The XML file seems to be incorrect for table \'' .$this->tableName. '\'.'); }
					$offset = $items[$i] + 1;
					$len = $items[$i + 1] - $offset;
					$fields = array_slice($values, $offset, $len);

					$itemList[$count] = array();
					foreach ($fields as $thisField)
					{
						$itemList[$count][$thisField["tag"]] = @$thisField["value"];
					}
					$count ++;
				}
			}
			elseif ($key == $this->tableName . 'RowCount')
			{
				$rowCount = intval(@$values[$value[0]]['value']);
			}
			elseif ($key == 'errors')
			{
				$count = 0;
				$items = $value;
				// There's always the start and the end... therefore: every 2 items
				for ($i=0; $i < count($items); $i+=2)
				{
					if (!isset($items[$i]) || !isset($items[$i + 1])) { throw new XmlDaoException('The XML file seems to be incorrect for table \'' . $this->tableName . '\'. (The errors section is not formatted correctly)'); }
					$offset = $items[$i] + 1;
					$len = $items[$i + 1] - $offset;
					$fields = array_slice($values, $offset, $len);

					foreach ($fields as $thisField) {
						$error = true;
						$errorMessages[] = @$thisField["value"];
						$this->log(@$thisField["value"]);
					}
					$count ++;
				}
			}
			elseif ($key == 'debug')
			{
				$count = 0;
				$items = $value;
				// There's always the start and the end... therefore: every 2 items
				for ($i=0; $i < count($items); $i+=2) {
					if (!isset($items[$i]) || !isset($items[$i + 1])) { throw new XmlDaoException('The XML file seems to be incorrect for table \'' . $this->tableName . '\'. (The debug section is not formatted correctly)'); }
					$offset = $items[$i] + 1;
					$len = $items[$i + 1] - $offset;
					$fields = array_slice($values, $offset, $len);

					foreach ($fields as $thisField) {
						$this->debug(@$thisField["value"]);
					}
					$count ++;
				}
			}
		}

		if ($error) {
			throw new XmlDaoException("Error while retrieving data for table '" . $this->tableName . "'.\n The server responded: '" . implode("\n", $errorMessages) . "'");
		}

		if (!$rowCount) { $rowCount = count($itemList); }

		return $itemList;
	}


	public function get($map = null, $exportValues = true, $tableName = null) {
		if (!$map) return $this->getRawObject();

		$content = $this->fileDataSource->view($this->exportTable($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)), $exportValues ? $this->exportMap($map) : $map);

		$data = $this->interpretFileContent($content);

		return $this->getObjectFromData($data[0]);
	}

  /**
   * Just calls the parent, but then returns only one element of the list
   */
	protected function getData($map, $exportValues = true, $tableName = null) {
	  $data = parent::getData($map, $exportValues, $tableName);
	  return $data[0];
	}

	/**
	 * Creates an iterator for a Xml data hash.
	 *
	 * @param array $data
	 * @return XmlResultIterator
	 */
	protected function createIterator($data) {
		return new XmlResultIterator($data, $this);
	}


}

?>
