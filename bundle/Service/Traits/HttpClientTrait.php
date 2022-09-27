<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Symfony\Contracts\HttpClient\HttpClientInterface;

trait HttpClientTrait
{
    protected HttpClientInterface $client;

    /**
     * @required
     */
    public function setHttpClient(HttpClientInterface $client)
    {
        $this->client = $client;
    }
}
