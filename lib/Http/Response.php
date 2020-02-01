<?php

namespace App\Http;

class Response
{
    const STATUS_CODE_200 = '200 OK';

    protected $headers = array();
    protected $body;

    protected $activeHeadersSetIndex = 0;

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        if (!is_array($this->headers))
        {
            $this->parseRawHeaders();
        }
        return $this;
    }

    public function setRawHeaders($headers)
    {
        return $this->setHeaders($headers, true);
    }

    public function getHeaders()
    {
        return $this->headers[$this->activeHeadersSetIndex];
    }

    public function getHeader($field, $defaultReturnValue = null)
    {
        if (array_key_exists($field, $this->headers[$this->activeHeadersSetIndex]))
        {
            return $this->headers[$this->activeHeadersSetIndex][$field];
        }
        return $defaultReturnValue;
    }

    public function getStartLine()
    {
        return $this->headers[$this->activeHeadersSetIndex][0];
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

    public function isStatusCode($statusCode)
    {
        return false !== strpos($this->getStartLine(), $statusCode);
    }

    protected function parseRawHeaders()
    {
        $headers = $this->headers;
        $headers = array_filter(preg_split('/\r\n\r\n/', $headers), function($header) {
            return (bool) $header;
        });

        $this->headers = array();
        foreach ($headers as $headerSetIndex => $headerSet)
        {
            $headerRows = explode("\r\n", $headerSet);
            $this->headers[$headerSetIndex][] = $headerRows[0];
            foreach ($headerRows as $headerRow)
            {
                if (false !== strpos($headerRow, ':'))
                {
                    list($fieldName, $fieldValue) = explode(':', $headerRow, 2);
                    $this->headers[$headerSetIndex][$fieldName] = trim($fieldValue);
                }
            }
        }

        $this->activeHeadersSetIndex = count($this->headers) - 1;
        return $this;
    }
}
