<?php

namespace Unikka\FilePreviews\Service;

/*
 * This file is part of the Unikka.FilePreviews package.
 *
 * (c) unikka and ttree ltd
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

/**
 * File Previews Client Service
 */
class Client
{

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $config;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(array $config = [])
    {
        $this->client = new GuzzleClient(['base_uri' => $config['api_url']]);
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getDefaultHeaders()
    {
        $client_ua = [
            'lang' => 'php',
            'publisher' => 'filepreviews',
            'bindings_version' => $this->config['version'],
            'lang_version' => phpversion(),
            'platform' => PHP_OS,
            'uname' => php_uname(),
        ];
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-FilePreviews-Client-User-Agent' => json_encode($client_ua),
            'User-Agent' => 'FilePreviews/v2 PhpBindings/' . $this->config['version']
        ];
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function get($path)
    {
        $response = $this->client->request(
            'GET',
            $path,
            [
                'headers' => $this->getDefaultHeaders(),
                'auth' => [$this->config['api_key'], $this->config['api_secret']]
            ]
        );

        return json_decode($response->getBody());
    }

    /**
     * @param string $path
     * @param string $data
     * @return mixed
     */
    public function post($path, $data)
    {
        $response = $this->client->request(
            'POST',
            $path,
            [
                'headers' => $this->getDefaultHeaders(),
                'auth' => [$this->config['api_key'], $this->config['api_secret']],
                'body' => $data
            ]
        );

        return json_decode($response->getBody());
    }

}
