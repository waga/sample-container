<?php

namespace App\Http;

class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';

    protected $url;
    protected $method;
    protected $headers = array();
    protected $body;

    public function __construct($url = '', $method = self::METHOD_GET, array $headers = array(), $body = '')
    {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function methodIsGet()
    {
        return $this->method == self::METHOD_GET;
    }

    public function methodIsPost()
    {
        return $this->method == self::METHOD_POST;
    }

    public function methodIsHead()
    {
        return $this->method == self::METHOD_HEAD;
    }

    public function setHeader($field, $value)
    {
        $this->headers[$field] = $value;
        return $this;
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $field => $value)
        {
            $this->setHeader($field, $value);
        }
        return $this;
    }

    public function hasHeader($field)
    {
        return array_key_exists($field, $this->headers);
    }

    public function getHeader($field, $defaultReturnValue = null)
    {
        if (array_key_exists($field, $this->headers))
        {
            return $this->headers[$field];
        }
        return $defaultReturnValue;
    }

    public function getHeaders($renderHeaders = true, $renderStartLine = true)
    {
        if ($renderHeaders)
        {
            return $this->getRenderedHeaders($renderStartLine);
        }
        return $this->headers;
    }

    public function getRenderedHeaders($renderStartLine = true)
    {
        $renderedHeaders = array();

        if ($renderStartLine)
        {
            $renderedHeaders[] = $this->renderStartLine();
        }

        foreach ($this->headers as $field => $value)
        {
            $renderedHeaders[] = $field .': '. $value;
        }
        return $renderedHeaders;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    protected function renderStartLine()
    {
        $parsedUrl = parse_url($this->url);
        $url = '';

        if (isset($parsedUrl['path']))// path
        {
            $url .= $parsedUrl['path'];
        }
        else
        {
            $url .= '/';
        }

        if (isset($parsedUrl['query']))// query - after the question mark ?
        {
            $url .= '?'. $parsedUrl['query'];
        }

        if (isset($parsedUrl['fragment']))// fragment - after the hashmark #
        {
            $url .= '#'. $parsedUrl['fragment'];
        }

        return $this->method .' '. $url .' HTTP/1.0';
    }
}
