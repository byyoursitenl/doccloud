<?php
/**
 * This file contains code about \DocCloud\Api class
 */

namespace DocCloud;

use DocCloud\Exceptions\InvalidParameterException;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

/**
 * Base Wrapper to manage login and exchanges with DocCloud API
 *
 * Http connections use guzzle http client api and result of request are
 * object from this http wrapper
 *
 * @package DocCloud
 * @category DocCloud
 * @author Bjorn Valk <bjorn.valk@byyoursite.nl>
 */
class Api {

  /**
   * Url to communicate with DocCloud API
   *
   * @var string
   */
  private $endpoint = 'doccloud.byshosting.nl/api/v1';

  /**
   * Protocol (http or https) to communicate with DocCloud API
   *
   * @var string
   */
  private $protocol = 'http';

  /**
   * API Key of the current application
   *
   * @var string
   */
  private $oauth_token = NULL;

  /**
   * Contain http client connection
   *
   * @var GuzzleClient
   */
  private $http_client = NULL;

  /**
   * Construct a new wrapper instance
   *
   * @param string $access_token Access Key of your application.
   * @param GuzzleClient $http_client Instance of http client
   *
   * @throws InvalidParameterException if one parameter is missing or with bad
   *   value
   */
  public function __construct($access_token, GuzzleClient $http_client = NULL) {
    if (!isset($access_token)) {
      throw new Exceptions\InvalidParameterException("Access Token parameter is empty");
    }
    if (!isset($http_client)) {
      $http_client = new GuzzleClient();
    }
    $this->access_token = $access_token;
    $this->http_client = $http_client;
    return TRUE;
  }

    /**
    * This is the main method of this wrapper. It will
    * sign a given query and return its result.
    *
    * @param string $method HTTP method of request (GET,POST,PUT,DELETE)
    * @param string $path relative url of API request
    * @param string $content body of the request
    * @param boolean $is_authenticated if the request use authentication
    *
    * @return mixed
    *
    * @throws Exception
    * @throws Exceptions\ApiBadRequestException
    * @throws Exceptions\ApiConversionFailedException
    * @throws Exceptions\ApiException if the DocCloud API returns an error
    * @throws Exceptions\ApiTemporaryUnavailableException
    * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
    *   network error
    */
    private function rawCall($method, $path, $content = NULL, $is_authenticated = TRUE) {
        $url = $path;
        if (strpos($path, '//') === 0) {
          $url = $this->protocol . ":" . $path;
        }
        elseif (strpos($url, 'http') !== 0) {
          $url = $this->protocol . '://' . $this->endpoint . $path;
        }

        $options = [
          'query' => NULL,
          'body' => NULL,
          'headers' => [],
          'multipart' => NULL,
          'debug' => false
        ];


        if (is_array($content) && $method == 'GET') {
          $options['query'] = $content;
        }
        elseif (gettype($content) == 'resource' && $method == 'POST') {
          // is upload
          $options['body'] = \GuzzleHttp\Psr7\stream_for($content);

        }
        elseif (is_array($content)) {
          if ($content['files'] && $method == 'POST') {
            unset($options['query'], $options['body']);
            $options['multipart'] = [];
            foreach ($content['files'] as $key => $tmpfile) {
              $options['multipart'][] = [
                'name' => 'files['.$key.']',
                'contents' => fopen($tmpfile, 'r'),
              ];
            }
          }
          else {
            $body = json_encode($content);
            $options['body'] = \GuzzleHttp\Psr7\stream_for($body);
            $options['headers']['Content-Type'] = 'application/json; charset=utf-8';
          }
        }

        if ($is_authenticated) {
          $options['headers']['Authorization'] = 'Bearer ' . $this->access_token;
        }

        try {
          $response = $this->http_client->request($method, $url, $options);
          if ($response->getHeader('Content-Type')
            && strpos($response->getHeader('Content-Type')[0], 'application/json') === 0) {
            return json_decode($response->getBody()->getContents(), true);
          }
          elseif ($response->getBody()->isReadable()) {
            // if response is a download, return the stream
            return $response->getBody();
          }
        } catch (RequestException $e) {
            if (!$e->getResponse()) {
                throw $e;
            }
            // check if response is JSON error message from the DocCloud API
            $json = json_decode($e->getResponse()->getBody(), TRUE);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('Error parsing JSON response');
            }

            if (isset($json['message']) || isset($json['error'])) {
                $msg = isset($json['error']) ? $json['error'] : $json['message'];
                $code = $e->getResponse()->getStatusCode();
                if ($code == 400) {
                    throw new Exceptions\ApiBadRequestException($msg, $code);
                }
                elseif ($code == 422) {
                    throw new Exceptions\ApiConversionFailedException($msg, $code);
                }
                elseif ($code == 503) {
                    $retryAfterHeader = $e->getResponse()->getHeader('Retry-After');
                    throw new Exceptions\ApiTemporaryUnavailableException(
                        $msg,
                        $code,
                        $retryAfterHeader ? $retryAfterHeader[0] : NULL
                    );
                }
                else {
                    throw new Exceptions\ApiException($msg, $code);
                }
            }
            else {
                throw $e;
            }
        }
    }

  /**
   * Wrap call to DocCloud APIs for GET requests
   *
   * @param string $path path ask inside api
   * @param string $content content to send inside body of request
   * @param boolean $is_authenticated if the request use authentication
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function get($path, $content = NULL, $is_authenticated = TRUE) {
    return $this->rawCall("GET", $path, $content, $is_authenticated);
  }

  /**
   * Wrap call to DocCloud APIs for POST requests
   *
   * @param string $path path ask inside api
   * @param string $content content to send inside body of request
   * @param boolean $is_authenticated if the request use authentication
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function post($path, $content, $is_authenticated = TRUE) {
    return $this->rawCall("POST", $path, $content, $is_authenticated);
  }

  /**
   * Wrap call to DocCloud APIs for PUT requests
   *
   * @param string $path path ask inside api
   * @param string $content content to send inside body of request
   * @param boolean $is_authenticated if the request use authentication
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns
   *   an error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function put($path, $content, $is_authenticated = TRUE) {
    return $this->rawCall("PUT", $path, $content, $is_authenticated);
  }

  /**
   * Wrap call to DocCloud APIs for DELETE requests
   *
   * @param string $path path ask inside api
   * @param string $content content to send inside body of request
   * @param boolean $is_authenticated if the request use authentication
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API
   *   returns an error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function delete($path, $content = NULL, $is_authenticated = TRUE) {
    return $this->rawCall("DELETE", $path, $content, $is_authenticated);
  }

  /**
   * Get the current Access Token
   *
   * @return string
   */
  public function getAccessToken() {
    return $this->access_token;
  }

  /**
   * Return instance of http client
   *
   * @return GuzzleClient
   */
  public function getHttpClient() {
    return $this->http_client;
  }

  /**
   * Create a new Convert
   *
   * @return \DocCloud\Convert
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function createConvert($parameters) {
    $result = $this->post("/convert", $parameters, TRUE);
    return new Convert($this, $result);
  }

  /**
   * Shortcut: Create a new Convert and start it
   *
   * @return \DocCloud\Convert
   *
   * @throws \DocCloud\Exceptions\ApiException if the DocCloud API returns an
   *   error
   * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP /
   *   network error
   *
   */
  public function convert($parameters) {
    $startparameters = $parameters;

    $convert = $this->createConvert($startparameters);
    return $convert;
  }
}
