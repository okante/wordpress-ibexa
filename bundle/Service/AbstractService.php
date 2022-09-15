<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection\Configuration;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\ConfigResolverTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\IbexaRepositoryTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\LoggerTrait;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\OrderBy;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractService implements ServiceInterface
{
    use ConfigResolverTrait;
    use LoggerTrait;
    use IbexaRepositoryTrait;
    protected const NAMESPACE = Configuration::NAMESPACE;
    private const SERVICE_PREFIX = 'wp-json/wp/v2';
    public const DATATYPE = '';

    public const SERVICE_URL = '';
    public const ROOT = '';

    protected HttpClientInterface $client;
    protected string $objectClass;
    protected string $exceptionClass;
    protected StorageInterface $storage;
    protected ContentInterface $contentInterface;

    public function __construct(HttpClientInterface $client, StorageInterface $storage, ContentInterface $contentInterface)
    {
        $this->client = $client;
        $this->storage = $storage;
        $this->contentInterface = $contentInterface;
    }

    /**
     * @param int $objectId
     * @param string $lang
     * @param bool $update
     * @return Content
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createAsSubObject(int $objectId, string $lang = 'eng-GB', bool $update = true): Content
    {
        $wpObject = $this->getOne($objectId);
        if ($wpObject === null) {
            throw new RuntimeException ('Cannot find '.static::DATATYPE.' Id '.$objectId);
        }
        $wpObjectContent = $this->createContent($wpObject, $lang, $update);
        if ($wpObjectContent === null) {
            throw new RuntimeException ('Cannot create '.static::DATATYPE.' Id '.$objectId);
        }
        return $wpObjectContent;
    }

    /**
     */
    final public function import(?int $perPage = null, ?int $page = null): int
    {
        $this->storage->clearAll();
        $perPage = $perPage > 0? $perPage: null;
        $page = abs($page ?? 1);
        $postCount = 0;
        while(true) {
            $objects = $this->get($page, $perPage);
            if (count($objects) === 0) {
                break;
            }
            $postCount += count($objects);
            foreach ($objects as $object) {
                /** @var WPObject $object */
                try {
                    $this->createContent($object);
                } catch (\Exception $exception) {
                    $this->error(__METHOD__, ['e' => $exception, 'object' => $object]);
                }
                break;
            }
            $this->info('iteration:'. $page);
            $page++;
        }
        $this->storage->clearAll();
        return $postCount;
    }

    /**
     * @param int $page
     * @param array $options
     * @param int|null $perPage
     * @return array
     * @throws Exception
     */
    final protected function fetch(int $page = 1, ?int $perPage = null, array $options = []): array
    {
        $requestURL = $this->getRequestedUrl(static::SERVICE_URL, self::SERVICE_PREFIX, self::NAMESPACE);
        if ($perPage === null) {
            $perPage = max(1, $this->getPerPage(static::ROOT));
        }
        $this->getOrderBy($options);
        $headers = $options['headers']?? [];
        $options['headers']['Accept'] = $headers['Accept']?? 'application/json';
        $options['query']['per_page'] = $options['query']['per_page']?? $perPage;
        $options['query']['page'] = $options['query']['page']?? max($page, 1);

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
        $requestURL = $this->getRequestedUrl(static::SERVICE_URL, self::SERVICE_PREFIX, self::NAMESPACE);
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

    protected function createObject(array $data): ?WPObject
    {
        $id = (int)($data['id']??0);
        if ($id > 0) {
            $object = new $this->objectClass($data);
            $this->storage->store((string)$id, static::DATATYPE, $object);
            return $object;
        }
        return null;
    }

    public function get(int $page = 1, ?int $perPage = null, array $options = []): array
    {
        try {
            $elements = $this->fetch($page, $perPage, $options);
            if (empty($elements) || !empty($elements['code']) || !empty($elements['message'])) {
                return [];
            }
            $objects = [];
            foreach ($elements as $element) {
                $objects [] = $this->createObject($element);
            }
            return $objects;
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param int $id
     * @param bool $force
     * @return WPObject|null
     */
    public function getOne(int $id, bool $force = false): ?WPObject
    {
        $url = static::SERVICE_URL.'/'.$id;
        if ($force === false) {
            $data = $this->storage->load((string)$id, static::DATATYPE);
            if ($data !== null) {
                $this->debug('['.__METHOD__.']found('.$data->getWPObjectId().')');
                return $data;
            }
        }
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

    /**
     * @param WPObject $object
     * @param string $lang
     * @param bool $update
     * @return Content|null
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function  createContent(WPObject $object, string $lang = 'eng-GB', bool $update = false): ?Content
    {
        $values = $this->configResolver->getParameter(static::ROOT, self::NAMESPACE);
        $remoteId = static::DATATYPE. '-'. $object->getWPObjectId();
        $parentLocationId = $values['parent_location']??null;
        return $this->innerCreateContent($object, $values, $remoteId, $parentLocationId, $lang, $update);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws NotFoundException
     * @throws BadStateException
     * @throws ContentValidationException
     * @throws UnauthorizedException
     * @throws ContentFieldValidationException
     */
    protected function innerCreateContent(WPObject $object, array $values, string $remoteId, int $parentLocationId, string $lang = 'eng-GB', bool $update = false): ?Content
    {
        $content = $this->contentInterface->createContent($object, $values, $remoteId, $parentLocationId, $lang, $update);
        if ($content) {
            $this->info('created (content) => ' . $content->getName() . '('.$content->id.')('.$content->contentInfo->remoteId.')');
        }
        return $content;
    }

    private function getOrderBy(array &$options)
    {
        $orderBy = (new OrderBy($options))->format();
        unset($options['order'], $options['orderby']);
        if (!empty($orderBy['orderby'])) {
            $options['orderby'] = $orderBy['orderby'];
            $options['order'] = $orderBy['order'];
        }
        $options = $orderBy + $options;
    }
}