<?php

/**
 * This file contains the Profile definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */

/**
 * The abstract Profile class is very small, and only a static wrapper for a specific
 * Profiler implementation.
 * All classes in the library use this class to profile their actions, so it gets included
 * even if you do not set a profiler. This is the reason I decided to keep this class
 * really simple, so there is nearly no overhead.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
abstract class Profile {

  /**
   * @var Profiler
   */
  protected static $profiler;

  /**
   * @param Profiler $profiler
   */
  public static function setProfiler($profiler) {
    self::$profiler = $profiler;
  }

  /**
   * @return Profiler
   */
  public static function getProfiler() {
    return self::$profiler;
  }

  /**
   * Reroutes to self::$profiler->start()
   * @param string $context
   * @param string $section
   * @see $profiler
   */
  public static function start($context, $section = null) {
    if (self::$profiler) self::$profiler->start($context, $section);
  }

  /**
   * Reroutes to self::$profiler->stop()
   * @see $profiler
   */
  public static function stop() {
    if (self::$profiler) self::$profiler->stop();
  }

}