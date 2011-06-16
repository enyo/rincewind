<?php

/**
 * This file contains the FileLogger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * */
/**
 * Include the Logger
 */
if ( ! class_exists('Logger', false)) include(dirname(__FILE__) . '/Logger.php');

/**
 * The HtmlLogger is the default implementation for a logger logging as html.
 * This logger just writes to the output.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * */
class HtmlLogger extends Logger {

  /**
   * Writes the log message to output.
   *
   * @param string $message
   * @param int $level
   * @param string $context Can contain a context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  public function doLog($message, $level, $context, $additionalInfo) {
    echo $this->formatMessage($message, $level, $context, $additionalInfo);
  }

  /**
   * Returns the css that styles the lines
   */
  public function getDefaultCss() {
    $css = '
      .logger-line {
        border-bottom: 1px solid #eee;
        padding: 5px 0;
        position: relative;
        font-family: courier, monospace;
      }
      .logger-line.logger-level-INFO .logger-context {
        color: black;
      }
      .logger-line.logger-level-DEBUG .logger-context {
        color: gray;
      }
      .logger-line.logger-level-WARN .logger-context {
        color: orange;
      }
      .logger-line.logger-level-ERROR .logger-context {
        color: red;
      }
      .logger-line.logger-level-FATAL .logger-context {
        color: red;
        font-weight: bold;
      }
      .logger-line .logger-level {
        display: none;
      }
      .logger-line .logger-context {
        position: absolute;
        top: 5px;
        left: 0;
        width: 120px;
        text-align: right;
        display: inline-block;
        margin-right: 5px;
        padding-right: 5px;
        border-right: 1px solid #ccc;
      }
      .logger-line .logger-message {
        display: block;
        margin-left: 130px;
        overflow: auto;
      }
      
      .logger-line .logger-additional-info {
        display: none;
        background: #f3f3f3;
        padding: 10px;
        overflow: auto;
        position: absolute;
        top: 20px;
        left: 250px;
        right: 0;
        z-index: 200;
      }
      .logger-line:hover .logger-additional-info, .logger-line .logger-additional-info:hover {
        display: block;
      }
            
      .logger-line .logger-additional-info-set {
        display: block;
      }
      .logger-line .logger-additional-info-set .logger-key {
        font-weight: bold;
      }
      .logger-line .logger-additional-info-set .logger-key:after {
        content: " => ";
      }
    ';
    return $css;
  }

  /**
   * Formats a message so it can be included in the file.
   *
   * If you overwrite it, don't forget the newline at the end.
   *
   * @param string $message
   * @param int $level
   * @param string $context Can contain a context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  protected function formatMessage($message, $level, $context, $additionalInfo) {
    $line = '<div class="logger-line logger-level-' . $this->levelStrings[$level] . ' logger-context-' . $context . '">';
    $line .= '<span class="logger-level">' . $this->levelStrings[$level] . '</span>';
    $line .= '<span class="logger-context">' . ($context ? $context : '-') . '</span>';
    $line .= '<span class="logger-message">' . $message . '</span>';
    if ($additionalInfo) {
      $line .= ' <span class="logger-additional-info">';
      foreach ($additionalInfo as $key => $value) {
        $valueOutput = print_r($value, true);
        if (strlen($valueOutput) > 203) {
          // Check if the value is binary.
//          if (substr_count($blk, "^ -~", "^\r\n") / 512 > 0.3 || substr_count($blk, "\x00") > 0) {
//            $valueOutput = '[ Contains binary data ]';
//          }
        }
        $line .= '<span class="logger-additional-info-set"><span class="logger-key">' . $key . '</span> <span class="logger-value">' . $valueOutput . '</span></span>';
      }
      $line .= '</span>';
    }
    $line .= '</div>';
    return $line;
  }

}

