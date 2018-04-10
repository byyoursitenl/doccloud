<?php
/**
 * This file contains code about \DocCloud\ApiObject class
 */

namespace DocCloud;

/**
 * Base class for Objects returned from the DocCloud API
 *
 * @package DocCloud
 * @category DocCloud
 * @author Björn Valk <bjorn.valk@byyoursite.nl>
 */
class ApiObject {

  /** @var Api */
  protected $api;

  /** @var string */
  public $result;

  /**
   * Contains the object data returned from the DocCloud API
   *
   * @var array
   */
  protected $data = [];

  /**
   * Construct a new ApiObject instance
   *
   * @param Api $api
   * @param string $result The Object result
   *
   * @throws Exceptions\InvalidParameterException If one parameter is missing
   *   or with bad value
   */
  public function __construct(Api $api, $result) {
    if (!isset($api)) {
      throw new Exceptions\InvalidParameterException("API parameter is not set");
    }
    if (!isset($result)) {
      throw new Exceptions\InvalidParameterException("Object Result parameter is not set");
    }
    $this->api = $api;
    $this->result = $result;
    return $this;
  }

  /**
   * Refresh Object Data
   *
   * @param array $parameters Parameters for refreshing the Object.
   *
   * @return \DocCloud\ApiObject
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function refresh($parameters = NULL) {
    $this->data = $this->api->get($parameters['url'], $parameters, FALSE);
    return $this;
  }

  /**
   * Access Object data via $object->prop->subprop
   *
   * @param string $name
   *
   * @return null|object
   */
  public function __get($name) {

    if (is_array($this->data) && array_key_exists($name, $this->data)) {
      return self::arrayToObject($this->data[$name]);
    }

    return NULL;
  }

  /**
   * Converts multi dimensional arrays into objects
   *
   * @param array $d
   *
   * @return object
   */
  private static function arrayToObject($d) {
    if (is_array($d)) {
      /*
       * Return array converted to object
       * Using [__CLASS__, __METHOD__] (Magic constant)
       * for recursive call
       */
      return (object) array_map([__CLASS__, __METHOD__], $d);
    }
    else {
      // Return object
      return $d;
    }
  }
}
