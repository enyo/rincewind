<?php

  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   */

  
  interface LoggerFactoryInterface {
  
    /**
     * Returns a logger
     *
     * @param string $resource
     * @return Logger
     */
    public function getLogger($resource);
  
  
    /**
     * Calls setLoggerFactory on every object in the list.
     *
     * @param array $objectList
     */
    public function apply($objectList);
  
  }


?>
