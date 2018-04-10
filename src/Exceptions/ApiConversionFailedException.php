<?php

namespace DocCloud\Exceptions;


/**
 * ApiConversionFailedException exception is throwned when a the DocCloud API
 * returns any HTTP error code 422
 *
 * @package DocCloud
 * @category Exceptions
 * @author Bjorn Valk <bjorn.valk@byyoursite.nl>
 */
class ApiConversionFailedException extends ApiException {

}
