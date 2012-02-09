<?php

/**
 * This file contains the ContainerAware interface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Config
 */

/**
 * A class has to implement this interface to receive the container.
 */
interface ContainerAware {
 
  /**
   * Has to call verifyRequiredServices()
   *
   * @param sfServiceContainer $container
   */
  public function setContainer($container); 

  /**
   * @return sfServiceContainer
   */
  public function getContainer(); 
 
  /**
   * Has to return a list of required services.
   *
   * @return array
   */
  public function getRequiredServices();
 
  /**
   * Checks if all services are correctly implemented
   *
   * @param sfServiceContainer $container
   * @throws Exception if not everyting was ok.
   */
  public function verifyRequiredServices($container);
 
   
}