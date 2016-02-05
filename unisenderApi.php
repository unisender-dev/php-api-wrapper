<?php

/**
 * API UniSender.
 *
 * @link http://www.unisender.com/ru/help/api/
 *
 * @version 1.3.2
 */
class unisenderApi
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * @var int
     */
    protected $retryCount = 0;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var bool
     */
    protected $compression = false;

    /**
     * UniSender Api constructor
     *
     * @param string $apiKey
     * @param string $encoding
     * @param int    $retryCount
     * @param int    $timeout
     * @param bool   $compression
     */
    public function __construct($apiKey, $encoding = 'UTF-8', $retryCount = 4, $timeout = null, $compression = false)
    {
        $this->apiKey = $apiKey;

        if (!empty($encoding)) {
            $this->encoding = $encoding;
        }

        if (0 < $retryCount) {
            $this->retryCount = $retryCount;
        }

        if (null !== $timeout) {
            $this->timeout = $timeout;
        }

        if ($compression) {
            $this->compression = $compression;
        }
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (!is_array($arguments) || 0 === count($arguments)) {
            $params = [];
        } else {
            $params = $arguments[0];
        }

        return $this->callMethod($name, $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function subscribe($params)
    {
        $params = (array) $params;

        if (empty($params['request_ip'])) {
            $params['request_ip'] = $this->getClientIp();
        }

        return $this->callMethod('subscribe', $params);
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    protected function decodeJSON($json)
    {
        return json_decode($json);
    }

    /**
     * @return string
     */
    protected function getClientIp()
    {
        $result = '';

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $result = $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (preg_match('/([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}/', $result, $match)) {
            return $match[0];
        }

        return $result;
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function iconv(&$value, $key)
    {
        $value = iconv($this->encoding, 'UTF-8//IGNORE', $value);
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function mb_convert_encoding(&$value, $key)
    {
        $value = mb_convert_encoding($value, 'UTF-8', $this->encoding);
    }

    /**
     * @param string $methodName
     * @param array  $params
     *
     * @return array
     */
    protected function callMethod($methodName, $params = [])
    {
        if (strtoupper($this->encoding) !== 'UTF-8') {
            if (function_exists('iconv')) {
                array_walk_recursive($params, [$this, 'iconv']);
            } elseif (function_exists('mb_convert_encoding')) {
                array_walk_recursive($params, [$this, 'mb_convert_encoding']);
            }
        }

        $url = $methodName.'?format=json';

        if ($this->compression) {
            $url .= '&api_key='.$this->apiKey.'&request_compression=bzip2';
            $content = bzcompress(http_build_query($params));
        } else {
            $params = array_merge((array) $params, ['api_key' => $this->apiKey]);
            $content = http_build_query($params);
        }

        $contextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $content,
            ],
        ];

        if ($this->timeout) {
            $contextOptions['http']['timeout'] = $this->timeout;
        }

        $retryCount = 0;
        $context = stream_context_create($contextOptions);

        do {
            $host = $this->getApiHost($retryCount);
            $result = file_get_contents($host.$url, false, $context);
            ++$retryCount;
        } while ($result === false && $retryCount < $this->retryCount);

        return $result;
    }

    /**
     * @param int $retryCount
     *
     * @return string
     */
    protected function getApiHost($retryCount = 0)
    {
        if ($retryCount % 2 === 0) {
            return 'http://api.unisender.com/ru/api/';
        } else {
            return 'http://www.api.unisender.com/ru/api/';
        }
    }
}
