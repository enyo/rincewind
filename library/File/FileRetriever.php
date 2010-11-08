<?php

/**
 * This file contains the basic FileRetriever class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
/**
 * Loading the file class.
 * They are codependent so this has to be include_once to avoid an endless loop.
 */
include_once(dirname(__FILE__) . '/File.php');



/**
 * Loading the Log class.
 */
if ( ! class_exists('Log', false)) include dirname(__FILE__) . '/../Logger/Log.php';

/**
 * The Exception base class for FileRetrieverExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileRetrieverExceptions
 */
class FileRetrieverException extends FileException {

  /**
   * The response from server
   * @var string
   */
  protected $response;

  public function  __construct($message, $code, $response) {
    parent::__construct($message, $code);
    $this->response = $response;
  }

  /**
   * @return string
   */
  public function getResponse() {
    return $this->response;
  }

}

/**
 * The FileRetriever actually fetches a files, and returns the File object.
 * There is a static File::create() class which actually instantiates the
 * FileRetriever and gets the file.
 * The FileRetriever is a class on it's own, so it can be passed to other
 * objects, so they don't have to call the static File:: functions (which
 * makes them rather difficult to test.)
 *
 * @see File
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
class FileRetriever {

  /**
   * After a file gets created, the method creating it calls
   * process($file), so the file can be processed before it's returned.
   * If you extend File, you can implement this function.
   *
   * @param File $file
   */
  protected function process($file) {
    
  }

  /**
   * Creates a file, and returns it.
   * @return File
   */
  protected function getFile($uri) {
    return new File($uri);
  }

  /**
   * This is the way to create a file.
   *
   * It is a shortcut for the different methods available (Form upload, http, etc...)
   *
   * If you need more control over the method, you can call the different methods
   * directly (eg: createFromHttp())
   *
   * @param mixed $data Is either an URL, or an array from form upload or a local path.
   * @param int $source One of File::SOURCE_XXX
   * @param int $maxFileSize in kilobytes.
   * @return File
   */
  public function create($data, $source, $maxFileSize = 10000000) {
    switch ($source) {
      case File::SOURCE_FILE:
        return self::createFromLocalFile($data);
        break;
      case File::SOURCE_FORM:
        return self::createFromFormUpload($data, $maxFileSize);
        break;
      case File::SOURCE_HTTP:
        return self::createFromHttp($data);
        break;
      case File::SOURCE_LOCAL:
      case File::SOURCE_REMOTE:
      case File::SOURCE_USER:
        throw new FileRetrieverException("Your source has to be more specific than that.");
      default:
        throw new FileRetrieverException("Unknown source.");
    }
  }

  /**
   * This method is the way to get a file from a form upload.
   *
   * @param array $FILE the array obtained from $_FILES
   * @param int $maxFileSize in kiloBytes Leave null if your php config handles your file size limits.
   * @deprecated use get() instead
   * @return File
   */
  public function createFromFormUpload($FILE, $maxFileSize = null) {
    if ( ! is_array($FILE) || count($FILE) === 0) {
      throw new FileRetrieverException('The FILE array from the form was not valid.');
    }

    if ($FILE['error'] === UPLOAD_ERR_NO_FILE) {
      return null;
    }

    if ($FILE['error'] !== 0) {
      switch ($FILE['error']) {
        case UPLOAD_ERR_INI_SIZE: throw new FileRetrieverException('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
        case UPLOAD_ERR_FORM_SIZE: throw new FileRetrieverException('The uploaded file exceeds the specified form size.');
        case UPLOAD_ERR_PARTIAL: throw new FileRetrieverException('The uploaded file was only partially uploaded.');
        case UPLOAD_ERR_NO_FILE: throw new FileRetrieverException('No file was uploaded.');
        case UPLOAD_ERR_NO_TMP_DIR: throw new FileRetrieverException('Missing a temporary folder.');
        case UPLOAD_ERR_CANT_WRITE: throw new FileRetrieverException('Failed to write file to disk.');
      }
    }

    if (empty($FILE['tmp_name']) || empty($FILE['size'])) {
      throw new FileRetrieverException('The file was probably larger than allowed by the html property MAX_FILE_SIZE.');
    }

    if ($maxFileSize && $FILE['size'] > $maxFileSize) {
      throw new FileRetrieverException('The uploaded file exceeds ' . ($maxFileSize / 1000) . 'kB.');
    }

    $file = $this->getFile($FILE['tmp_name']);

    $file->setSource(File::SOURCE_FORM);
    $file->setName($FILE['name']);
    $file->setSize($FILE['size']);
    $file->setMimeType($FILE['type']);

    $this->process($file);

    return $file;
  }

  /**
   * This method is the way to get a file from a local file.
   *
   * @param array $srcUri the location of the file
   * @deprecated Use get() instead.
   * @return File
   */
  public function createFromLocalFile($srcUri) {
    if ( ! is_file($srcUri)) throw new FileRetrieverException("File '$srcUri' does not exist.");
    $file = $this->getFile($srcUri);
    $file->setSource(File::SOURCE_FILE);
    $file->setSize(filesize($srcUri));
    $this->process($file);
    return $file;
  }

  /**
   * This method is the way to get a file from a http source.
   *
   * @param array $url the location of the file
   * @param array $getParameters A map with get parameters
   * @param array $postParameters A map with post parameters
   * @param int $port If null, the default of 80 is used.
   * @param int $timeout in seconds. If null, the default of 30 is used.
   * @param array $headers optional array of lines to add to the header. Eg: array('Content-type: text/plain', 'Content-length: 100')
   * @deprecated Use get() instead.
   * @return File
   */
  public function createFromHttp($url, $getParameters = null, $postParameters = null, $port = null, $timeout = null, $headers = null) {

    if ($port === null) $port = 80;
    if ($timeout === null) $timeout = 30;

    $curlHandle = curl_init();

    if ($getParameters && is_array($getParameters) && count($getParameters) > 0) $getParameters = '?' . http_build_query($getParameters);
    else $getParameters = '';

    $realUrl = $url . $getParameters;

    Log::debug("Getting: $realUrl", 'FileRetriever',
            array('Port' => $port,
                'Headers' => $headers ? implode(', ', $headers) : null,
                'Post' => $postParameters)
    );

    curl_setopt($curlHandle, CURLOPT_URL, $realUrl);
    if ($headers) {
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($curlHandle, CURLOPT_PORT, $port);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);
    curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
    // return into a variable
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

    if ($postParameters) {
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postParameters);
//      curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');

      if (is_array($postParameters)) {
        curl_setopt($curlHandle, CURLOPT_POST, 1);
      }
    }

    $result = curl_exec($curlHandle);

    $info = curl_getinfo($curlHandle);

    curl_close($curlHandle);

    if ( ! $info['http_code'] || $info['http_code'] >= 400) {
      $errorCode = $info['http_code'] ? $info['http_code'] : 400;
      $errorTypes = array(400 => 'Bad Request', 500 => 'Internal Server Error');
      Log::warning('File could not be downloaded.', 'FileRetriever', array('url' => $realUrl, 'port' => $port, 'errorCode' => $errorCode, 'postParams' => $postParameters ? $postParameters : 'NONE', 'response' => $result));
      throw new FileRetrieverException($errorCode . ' - ' . $errorTypes[floor($errorCode / 100) * 100], $errorCode, $result);
    }

    $file = $this->getFile($url);
    $file->setSource(File::SOURCE_HTTP);
    $file->setContent($result);

    $this->process($file);

    return $file;
  }

}

