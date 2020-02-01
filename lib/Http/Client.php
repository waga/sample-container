<?php

namespace App\Http;

use Exception;
use App\Config;

class Client
{
    protected $retries = 1;

    protected $timeout = 60;
    protected $returnTransfer = 1;
    protected $maxRedirects = 10;
    protected $verbose = false;

    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function request(Request $request)
    {
        $lastError = null;

        for ($i = 0; $i < $this->retries; $i++)
        {
            $uniqueFileName = tempnam($this->config['temp']['dir'], 'http_session_');
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $request->getUrl());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, $this->returnTransfer);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirects);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request->getHeaders());
            curl_setopt($curl, CURLOPT_USERAGENT, $request->getHeader('User-Agent'));
            curl_setopt($curl, CURLOPT_POST, $request->methodIsPost());
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getBody());
            curl_setopt($curl, CURLOPT_NOBODY, $request->methodIsHead());
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, $this->config['http']['client']['certificate']);
            curl_setopt($curl, CURLOPT_CAPATH, $this->config['http']['client']['certificate']);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            curl_setopt($curl, CURLOPT_COOKIEJAR, $uniqueFileName);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $uniqueFileName);
            curl_setopt($curl, CURLOPT_VERBOSE, $this->verbose);

            if ($acceptEncoding = $request->getHeader('Accept-Encoding'))
            {
                curl_setopt($curl, CURLOPT_ENCODING , $acceptEncoding);
            }

            $httpResponse = curl_exec($curl);

            if (curl_errno($curl))
            {
                $lastError = curl_error($curl);
                continue;
            }

            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            curl_close($curl);

            $body = substr($httpResponse, $headerSize);
            $headers = substr($httpResponse, 0, $headerSize);

            $response = new Response();
            $response->setHeaders($headers)->setBody($body);

            return $response;
        }

        throw new Exception('Http client request error: '. $lastError);
    }
}
