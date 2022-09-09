<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection\Configuration;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractService
{
    private const NAMESPACE = Configuration::NAMESPACE;
    private const SERVICE_PREFIX = 'wp-json/wp/v2';

    public const SERVICE_URL = '';
    public const ROOT = '';



    protected HttpClientInterface $client;
    protected ConfigResolverInterface $configResolver;
    protected LoggerInterface $logger;
    protected string $objectClass;
    protected string $exceptionClass;

    public function __construct(HttpClientInterface $client, ConfigResolverInterface $configResolver, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->configResolver = $configResolver;
        $this->logger = $logger;
    }

    /**
     * @param int $page
     * @param array $options
     * @param int|null $perPage
     * @return array
     * @throws Exception
     */
    final protected function fetch(int $page = 1, array $options = [], ?int $perPage = null): array
    {
        $requestURL = $this->getRequestUrl();
        if ($perPage === null) {
            $perPage = max(1, $this->getPerPage());
        }
        $headers = $options['headers']?? [];
        $options['headers']['Accept'] = $headers['Accept']?? 'application/json';
        $options['query']['per_page'] = $options['query']['per_page']?? $perPage;
        $options['query']['page'] = $options['query']['per_page']?? max($page, 1);

        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                $requestURL,
                $options
            );
            return $response->toArray();
        } catch (\Exception|ExceptionInterface $exception) {
            throw new Exception($requestURL, $options, $exception);
        }
    }

    /**
     * @throws Exception
     */
    final protected function fetchOne(int $id, array $options = []): array
    {
        $requestURL = $this->getRequestUrl();
        $options['headers']['Accept'] = $options['headers']['Accept']?? 'application/json';
        $requestURL = rtrim($requestURL, '/'). '/'.$id;
        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                $requestURL,
                $options
            );
            return $response->toArray();
        } catch (\Exception|ExceptionInterface $exception) {
            throw new Exception($requestURL, $options, $exception);
        }
    }
    private function getPerPage(): int
    {
        $values = $this->configResolver->getParameter($this->getConfigRoot(), self::NAMESPACE);
        return (int)($values['per_page'] ?? null);
    }


    /**
     * @throws Exception
     */
    private function assertServiceURL(): string
    {
        $serviceURL = $this->getServiceUrl();
        if(empty($serviceURL) || trim($serviceURL, '/') === '') {
            throw new Exception($this->getConfigRoot());
        }
        return $serviceURL;
    }

    /**
     * @throws Exception
     */
    private function getBaseURl(): string
    {
        $baseUrl = (string) $this->configResolver->getParameter('url', self::NAMESPACE);
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if (empty($scheme) || empty($host)) {
            throw new Exception($baseUrl??$this->getConfigRoot());
        }
        return $baseUrl;
    }

    /**
     * @throws Exception
     */
    public function getRequestUrl(): string
    {
        $serviceURL = $this->assertServiceURL();
        $baseUrl = $this->getBaseURl();
        return trim($baseUrl, '/') . '/'. self::SERVICE_PREFIX. '/' .trim($serviceURL, '/');
    }

    public function getConfigRoot(): string
    {
        return static::ROOT;
    }
    public function getServiceUrl(): string
    {
        return (string)(static::SERVICE_URL);
    }

    final protected function createObject(array $data): ?WPObject
    {
        $id = (int)($data['id']??0);
        if ($id > 0) {
            return new $this->objectClass($data);
        }
        return null;
    }

    public function get(int $page = 1, array $options = [], ?int $perPage = null): array
    {
        try {
            return $this->fetch($page, $options, $perPage);
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param int $id
     * @return WPObject|null
     */
    public function getOne(int $id): ?WPObject
    {
        $url = static::SERVICE_URL.'/'.$id;
        try {
            $data = $this->fetchOne($id);
            if (empty($data)) {
                throw new Exception($url);
            }
        } catch (Exception $exception) {
            throw new $this->exceptionClass($url);
        }
        return $this->createObject($data);
    }

}