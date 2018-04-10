<?php
/**
 * This file contains code about \DocCloud\Convert class
 */

namespace DocCloud;

use DocCloud\Exceptions\InvalidParameterException;

/**
 * DocCloud Process Wrapper
 *
 * @package DocCloud
 * @category DocCloud
 * @author Björn Valk <bjorn.valk@byyoursite.nl>
 */
class Convert extends ApiObject {

  /**
   * Construct a new Convert instance
   *
   * @param Api $api
   * @param string $result The Convert Result
   *
   * @return \DocCloud\Convert
   *
   * @throws InvalidParameterException if one parameter is missing or with bad
   *   value
   */
  public function __construct(Api $api, $result) {
    parent::__construct($api, $result);
    return $this;
  }

  /**
   * Download process files from API
   *
   * @param string $localfile Local file name (or directory) the file should be
   *   downloaded to
   * @param string $remotefile Remote file name which should be downloaded (if
   *   there are multiple output files available)
   *
   * @return \DocCloud\Convert
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   * @throws Exceptions\InvalidParameterException
   *
   */
  public function download($localfile = NULL, $remotefile = NULL) {
    if (isset($localfile) && is_dir($localfile) && isset($this->result['files']) && count($this->result['files']) == 1) {
        $localfile = realpath($localfile) . DIRECTORY_SEPARATOR
        . (isset($remotefile) ? $remotefile : basename($localfile));
    }
    elseif (!isset($localfile) && isset($this->result['files']) && count($this->result['files']) == 1 && isset($this->result['files'][0]['filename'])) {
      $localfile = (isset($remotefile) ? $remotefile : $this->result['files'][0]['filename']);
    }

    if (!isset($localfile) || is_dir($localfile)) {
      throw new Exceptions\InvalidParameterException("localfile parameter is not set correctly");
    }

    if (!isset($remotefile) && (isset($this->result['files']) && count($this->result['files']) == 1)) {
        $remotefile = $this->result['files'][0];
    }

    if (get_resource_type($remotefile) && get_resource_type($remotefile) == 'stream') {
        return $this->downloadStream(fopen($localfile, 'w'), $remotefile);
    } elseif(is_string($remotefile)) {
        $remotefile = base64_decode($remotefile);
        if(file_put_contents($localfile, $remotefile) !== false) {
            return $localfile;
        } else {
            throw new Exceptions\InvalidParameterException("put contents of remotefile into localfile failed");
        }
    }
  }

  /**
   * Download process files from API and write to a given stream
   *
   * @param resource $stream Stream to write the downloaded data to
   * @param string $remotefile Remote file name which should be downloaded (if
   *   there are multiple output files available)
   *
   * @return \DocCloud\Convert
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function downloadStream($stream, $remotefile = NULL) {
    if (!isset($this->output->url)) {
      throw new Exceptions\ApiException("There is no output file available (yet)", 400);
    }

    $local = \GuzzleHttp\Psr7\stream_for($stream);
    $path = $this->output->url . (isset($remotefile) ? '/' . rawurlencode($remotefile) : '');
    $download = $this->api->get($path, FALSE, FALSE);
    $local->write($download);
    return $this;
  }

  /**
   * Download all output process files from API
   *
   * @param string $directory Local directory the files should be downloaded to
   *
   * @return \DocCloud\Convert
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function downloadAll($directory = NULL) {
    if (!isset($this->output->files)) { // the are not multiple output files -> do normal downloader
      return $this->download($directory);
    }

    foreach ($this->output->files as $file) {
      $this->download($directory, $file);
    }

    return $this;
  }


  /**
   * Delete Convert from API
   *
   * @return \DocCloud\Convert
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function delete() {
    $this->api->delete($this->url, FALSE, FALSE);
    return $this;
  }
}
