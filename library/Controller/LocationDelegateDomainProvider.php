<?php
/**
 * This file contains the LocationDomainProviderInterface definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */

/**
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2009, Matthias Loitsch
 * @package Controller
 */
interface LocationDelegateDomainProvider {

  /**
   * This method should cache the domain, so calling it multiple times does not
   * impact performance.
   * 
   * @return string the domain. Eg: www.shop.com:8080
   */
  public function getDomain();
  
}