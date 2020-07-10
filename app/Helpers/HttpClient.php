<?php

namespace App\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;

class HttpClient
{
    protected $baseUrl;

    protected $client;

    protected $headers;

    /**
     * @var bool
     */
    public $requestAsync = false;

    /**
     * @var int
     */
    public $maxRetries = 2;

    /**
     * @var int
     */
    public $retryDelay = 500;

    /**
     * @var Callable
     */
    private $requestCallback;

    /**
     * Turn on, turn off async requests
     *
     * @param bool $on
     * @return $this
     */
    public function async($on = true)
    {
        $this->requestAsync = $on;
        return $this;
    }

    /**
     * Callback to execute after OneSignal returns the response
     * @param callable $requestCallback
     * @return $this
     */
    public function callback(callable $requestCallback)
    {
        $this->requestCallback = $requestCallback;
        return $this;
    }

    public function __construct()
    {
        $this->client = new Client([
            'handler' => $this->createGuzzleHandler(),
        ]);
        $this->headers = ['headers' => []];
    }

    private function createGuzzleHandler()
    {
        return tap(HandlerStack::create(new CurlHandler()), function (HandlerStack $handlerStack) {
            $handlerStack->push(
                Middleware::retry(
                    function (
                        $retries,
                        Psr7Request $request,
                        Psr7Response $response = null,
                        RequestException $exception = null
                    ) {
                        if ($retries >= $this->maxRetries) {
                            return false;
                        }

                        if ($exception instanceof ConnectException) {
                            return true;
                        }

                        if ($response && $response->getStatusCode() >= 500) {
                            return true;
                        }

                        return false;
                    }
                ),
                $this->retryDelay
            );
        });
    }

    public function getInfo()
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl): HttpClient
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function addHeaders($params = [])
    {
        $this->headers = $params;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        if (!empty($this->headers[$key]) && is_array($this->headers[$key]) && is_array($value)) {
            $this->headers[$key] = array_merge($this->headers[$key], $value);
        } else {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    protected function post($endPoint)
    {
        if ($this->requestAsync === true) {
            $promise = $this->client->postAsync($this->baseUrl . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->post($this->baseUrl . $endPoint, $this->headers);
    }

    protected function put($endPoint)
    {
        if ($this->requestAsync === true) {
            $promise = $this->client->putAsync($this->baseUrl . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->put($this->baseUrl . $endPoint, $this->headers);
    }

    protected function get($endPoint)
    {
        return $this->client->get($this->baseUrl . $endPoint, $this->headers);
    }

    protected function delete($endPoint)
    {
        if ($this->requestAsync === true) {
            $promise = $this->client->deleteAsync($this->baseUrl . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->delete($this->baseUrl . $endPoint, $this->headers);
    }

    private function prepare()
    {
        if (!$this->baseUrl) {
            throw new InvalidArgumentException("Missing baseUrl");
        }
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, array('post', 'put', 'get', 'delete'))) {
            $this->prepare();
            return call_user_func_array(array($this, $method), $arguments);
        }
        throw new InvalidArgumentException("Method not found");
    }
}
