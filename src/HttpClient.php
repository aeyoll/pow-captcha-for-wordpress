<?php

namespace Aeyoll\PowCaptchaForWordpress;

/**
 * Simple HTTP client
 */
class HttpClient
{
    private $base_uri;
    private $timeout;
    private $default_headers;

    public function __construct(array $config = [])
    {
        $this->base_uri = rtrim($config['base_uri'] ?? '', '/');
        $this->timeout = $config['timeout'] ?? 30;
        $this->default_headers = $config['headers'] ?? [];
    }

    /**
     * Make a POST request
     *
     * @param string $uri
     * @param array $options
     * @return HttpResponse
     * @throws \Exception
     */
    public function post($uri, array $options = [])
    {
        return $this->request('POST', $uri, $options);
    }

    /**
     * Make an HTTP request
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return HttpResponse
     * @throws \Exception
     */
    private function request($method, $uri, array $options = [])
    {
        $url = $this->base_uri . '/' . ltrim($uri, '/');

        // Prepare WordPress HTTP API arguments
        $wp_args = [
            'timeout' => $this->timeout,
            'method' => $method,
            'sslverify' => true,
            'redirection' => 3,
        ];

        // Set headers
        $headers = array_merge($this->default_headers, $options['headers'] ?? []);
        if (!empty($headers)) {
            $wp_args['headers'] = $headers;
        }

        // Set body for POST requests
        if ($method === 'POST' && isset($options['body'])) {
            $wp_args['body'] = $options['body'];
        }

        // Make the request using WordPress HTTP API
        $response = \wp_remote_request($url, $wp_args);

        // Check for WordPress errors
        if (\is_wp_error($response)) {
            throw new \Exception('HTTP request failed: ' . \esc_html($response->get_error_message()));
        }

        $response_body = \wp_remote_retrieve_body($response);
        $http_code = \wp_remote_retrieve_response_code($response);

        return new HttpResponse($response_body, $http_code);
    }
}

/**
 * HTTP Response class to mimic Guzzle's response interface
 */
class HttpResponse
{
    private $body;
    private $status_code;

    public function __construct($body, $status_code)
    {
        $this->body = $body;
        $this->status_code = $status_code;
    }

    /**
     * Get response body
     *
     * @return HttpResponseBody
     */
    public function getBody()
    {
        return new HttpResponseBody($this->body);
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }
}

/**
 * HTTP Response Body class to mimic Guzzle's body interface
 */
class HttpResponseBody
{
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Get contents as string
     *
     * @return string
     */
    public function getContents()
    {
        return $this->content;
    }
}
