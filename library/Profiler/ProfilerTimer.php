<?php

/**
 * This file contains the ProfilerTimer definition, which is the actual class that
 * times parts of the program.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */

/**
 * The actual timer that gets instantiated and stored every time the profiler gets invoked.
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
class ProfilerTimer {

  /**
   * @var string
   */
  private $context;
  /**
   * @var string
   */
  private $section;
  /**
   * @var float
   */
  private $startTime;
  /**
   * @var float
   */
  private $endTime;
  /**
   * @var float
   */
  private $duration;
  /**
   * @var float
   */
  private $pauseStartTime;
  /**
   * @var float
   */
  private $totalPauseDuration = 0.0;

  /**
   * Sets the startTime
   * @param float $time
   */
  public function __construct($context, $section = null, $time = null) {
    $this->context = $context;
    $this->section = $section;
    $this->startTime = $time === null ? microtime(true) : $time;
  }

  /**
   * Sets the endtime, and calculates the duration
   * @param float $time
   */
  public function end($time = null) {
    $this->resume();
    $this->endTime = $time === null ? microtime(true) : $time;
    $this->duration = $this->endTime - $this->startTime;
  }

  /**
   * @return string
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * @return string
   */
  public function getSection() {
    return $this->section;
  }

  /**
   * @return float
   */
  public function getStartTime() {
    return $this->startTime;
  }

  /**
   * @return float
   */
  public function getEndTime() {
    return $this->endTime;
  }

  /**
   * @return float
   */
  public function getDuration() {
    if ($this->duration === null) return null;
    return $this->duration - $this->totalPauseDuration;
  }

  /**
   * @return float
   */
  public function getPauseStartTime() {
    return $this->pauseStartTime;
  }

  /**
   * @return float
   */
  public function getTotalPauseDuration() {
    return $this->totalPauseDuration;
  }

  /**
   * Whether the timer is paused or not.
   * @return bool
   */
  public function isPaused() {
    return $this->pauseStartTime !== null;
  }

  /**
   * Pauses the timer
   * @param float $time
   */
  public function pause($time = null) {
    if ( ! $this->isPaused()) $this->pauseStartTime = ($time === null ? microtime(true) : $time);
  }

  /**
   * Resumes the timer
   * @param float $time
   */
  public function resume($time = null) {
    if ($this->isPaused()) {
      $time = $time === null ? microtime(true) : $time;
      $pauseDuration = $time - $this->pauseStartTime;
      $this->totalPauseDuration += $pauseDuration;
      $this->pauseStartTime = null;
    }
  }

}