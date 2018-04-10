<?php

namespace DocCloud\Exceptions;


/**
 * ApiBadRequestException exception is throwned when a the DocCloud API returns
 * any HTTP error code 503
 *
 * @package DocCloud
 * @category Exceptions
 * @author Björn Valk <bjorn.valk@byyoursite.nl>
 */
class ApiTemporaryUnavailableException extends ApiException {

  public $retryAfter = 0;

  /**
   * @param string $msg
   * @param int $code
   * @param int $retryAfter
   */
  public function __construct($msg, $code, $retryAfter = 0) {
    $this->retryAfter = $retryAfter;
    return parent::__construct($msg, $code);
  }
}
