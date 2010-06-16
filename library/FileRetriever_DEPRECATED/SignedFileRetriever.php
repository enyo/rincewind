<?php

  require_once('FileRetriever/FileRetriever.php');

  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   *
   * The SignedFileRetriever differs from the normal FileRetriever in the way that it
   * signs the get and post variables with a provided seed and the current time.
   */
  class SignedFileRetriever extends FileRetriever {
    /**
     * The seed used to sign the variables.
     *
     * @var string
     */
    protected $seed;

    /**
     * Sets the seed. This is necessary! If you don't seed the seed, the FileRetriever will fail.
     *
     * @param string $seed
     */
    public function setSeed($seed) { $this->seed = $seed; }


    /**
     * This function signs the data.
     *
     * @param array $array The variables to use.
     */
    protected function generateVariableString($array) { 
      if (!$this->seed) throw new FileRetrieverException('No seed has been set!');

      $data = array('time'=>time(), 'content'=>$array);
      $data = serialize($data);
      $dataHash = md5($this->seed . $data . $this->seed);

      return '&data=' . rawurlencode(base64_encode($data)) . '&dataHash=' . rawurlencode($dataHash);  
    }

  }
  

