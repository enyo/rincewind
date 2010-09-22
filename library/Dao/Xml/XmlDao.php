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
include dirname(dirname(__FILE__)) . '/FileSourceDao.php';


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
class XmlDao extends FileSourceDao {


  /**
   * Intepretes the xml content, and returns it as array.
   *
   * Parsing XML is not pretty.
   *
   * @param string $content The file content
   * @return array
   */
  public function interpretFileContent($content) {
    $values = array();
    $tags = array();
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parser_set_option($parser, XML_OPTION_SKIP_TAGSTART, 0);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
    if (!xml_parse_into_struct($parser, $content, $values, $tags)) {
      throw new XmlDaoException('Could not parse XML for resource ' . $this->resourceName. '.');
    }
    xml_parser_free($parser);

    $rowCount = 0;
    $itemList = array();

    $error = false;
    $errorMessages = array();

    foreach ($tags as $key=>$value) {
      if ($key == $this->resourceName . 'Entry') {
        $count = 0;
        $items = $value;
        // There's always the start and the end... therefore: every 2 items
        for ($i=0; $i < count($items); $i+=2)
        {
          if (!isset($items[$i]) || !isset($items[$i + 1])) { file_put_contents('/tmp/TTT', $content); throw new XmlDaoException('The XML file seems to be incorrect for resource \'' .$this->resourceName. '\'.'); }
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
      elseif ($key == $this->resourceName . 'RowCount')
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
          if (!isset($items[$i]) || !isset($items[$i + 1])) { throw new XmlDaoException('The XML file seems to be incorrect for resource \'' . $this->resourceName . '\'. (The errors section is not formatted correctly)'); }
          $offset = $items[$i] + 1;
          $len = $items[$i + 1] - $offset;
          $fields = array_slice($values, $offset, $len);

          foreach ($fields as $thisField) {
            $error = true;
            $errorMessages[] = @$thisField["value"];
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
          if (!isset($items[$i]) || !isset($items[$i + 1])) { throw new XmlDaoException('The XML file seems to be incorrect for resource \'' . $this->resourceName . '\'. (The debug section is not formatted correctly)'); }
          $offset = $items[$i] + 1;
          $len = $items[$i + 1] - $offset;
          $fields = array_slice($values, $offset, $len);

          $count ++;
        }
      }
    }

    if ($error) {
      throw new XmlDaoException("Error while retrieving data for resource '" . $this->resourceName . "'.\n The server responded: '" . implode("\n", $errorMessages) . "'");
    }

    if (!$rowCount) { $rowCount = count($itemList); }

    return $itemList;
  }


  public function get($map = null, $exportValues = true, $resourceName = null) {
    if (!$map) return $this->getRawRecord();

    $content = $this->fileDataSource->view($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map);

    $data = $this->interpretFileContent($content);

    return $this->getRecordFromData($data[0]);
  }

  /**
   * Just calls the parent, but then returns only one element of the list
   */
  public function getData($map, $exportValues = true, $resourceName = null) {
    $data = parent::getData($map, $exportValues, $resourceName);
    return $data[0];
  }

  /**
   * Creates an iterator for a Xml data hash.
   *
   * @param array $data
   * @return XmlResultIterator
   */
  public function createIterator($data) {
    return new XmlResultIterator($data, $this);
  }


}

