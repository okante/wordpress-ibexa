<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection\Configuration;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Post;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\FieldTypeRichText\FieldType\RichText\Type as RichTextType;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractService
{
    protected const NAMESPACE = Configuration::NAMESPACE;
    private const SERVICE_PREFIX = 'wp-json/wp/v2';
    public const CACHE_SUFFIX = 'qlsdmsqlsq';

    public const SERVICE_URL = '';
    public const ROOT = '';



    protected HttpClientInterface $client;
    protected ConfigResolverInterface $configResolver;
    protected LoggerInterface $logger;
    protected string $objectClass;
    protected string $exceptionClass;
    protected TagAwareAdapterInterface $cachePool;
    private Repository $repository;
    private RichTextType $richTextType;

    public function __construct(HttpClientInterface $client, Repository $repository, TagAwareAdapterInterface $cachePool, RichTextType $richTextType, ConfigResolverInterface $configResolver, LoggerInterface $wordPressIbexaLogger)
    {
        $this->client = $client;
        $this->repository = $repository;
        $this->cachePool = $cachePool;
        $this->richTextType = $richTextType;
        $this->configResolver = $configResolver;
        $this->logger = $wordPressIbexaLogger;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function run(?int $perPage = null, ?int $limit = null): int
    {
        $this->clearAllCache();
        $postCount = 0;
        $perPage = $perPage > 0? $perPage: null;
        $page = 1;
        while(true) {
            $posts = $this->get($page, $perPage);
            if (count($posts) === 0) {
                break;
            }
            $postCount += count($posts);
            foreach ($posts as $post) {
                /** @var Post $post */
                try {
                    $this->repository->sudo(function () use ($post) {
                        $content = $this->createContent($post);
                        if ($content) {
                            $this->logger->info('created => ' . $content->getName() . '('.$content->id.')');
                        }
                    });
                } catch (\Exception $exception) {
                    $this->logger->error(__METHOD__, ['e' => $exception, 'post' => $post]);
                }
            }

            $this->logger->info('iteration:'. $page);

            $page++;
        }
        $this->clearAllCache();
        return $postCount;
    }

    /**
     * @throws Exception
     */
    public function clearAllCache()
    {
        $this->cachePool->invalidateTags($this->getCacheTags());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function loadFromCache(string $cacheKey)
    {
        try {
            $realKey = $this->getRealCacheKey($cacheKey);
            $cacheItem = $this->cachePool->getItem($realKey);
            if ($cacheItem->isHit()) {
                $this->logger->debug('['.__METHOD__.']found('.$cacheKey.','.$realKey.')');
                return $cacheItem->get();
            }
        } catch (\Exception $exception) {
        }

        return null;
    }

    /**
     * @throws Exception
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function saveInCache($cacheKey, WPObject $object)
    {
        $realKey = $this->getRealCacheKey($cacheKey);
        $cacheItem = $this->cachePool->getItem($realKey);
        if ($cacheItem->isHit()) {
            $this->logger->debug('['.__METHOD__.']found('.$cacheKey.','.$realKey.')');
            return $cacheItem->get();
        }
        $cacheItem->set($object);
        $cacheItem->tag($this->getCacheTags());
        $this->cachePool->save($cacheItem);
        $this->logger->debug('['.__METHOD__.']saved('.$cacheKey.','.$realKey.')');
        return null;
    }

    /**
     * @throws Exception
     */
    public function getCacheTags(): array
    {
        return [$this->normalizedCacheKey(self::NAMESPACE.'-' . $this->getBaseURl())];
    }

    /**
     * @throws Exception
     */
    private function getRealCacheKey($cacheKey): string
    {
        return $this->normalizedCacheKey(self::NAMESPACE.'-' . $this->getBaseURl(). '-'.static::CACHE_SUFFIX.'-'.$cacheKey);
    }

    private function normalizedCacheKey($cacheKey)
    {
        return str_replace(str_split(ItemInterface::RESERVED_CHARACTERS), '-', $cacheKey);
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
        $requestURL = $this->getRequestUrl();
        if ($perPage === null) {
            $perPage = max(1, $this->getPerPage());
        }
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
    final protected function fetchOne(int $id, array $options = [], bool $force = false): array
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

    public function get(int $page = 1, ?int $perPage = null, array $options = []): array
    {
        try {
            $list = $this->fetch($page, $perPage, $options);
            if (empty($list) || !empty($list['code']) || !empty($list['message'])) {
                return [];
            }
            return $list;
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param int $id
     * @return WPObject|null
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getOne(int $id, bool $force = false): ?WPObject
    {
        $url = static::SERVICE_URL.'/'.$id;
        $baseURL = $this->getBaseURl();
        if ($force === false) {
            $data = $this->loadFromCache( (string) $id);
            if ($data !== null) {
                $this->logger->debug('['.__METHOD__.']found('.$data->id.')');
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
        $object = $this->createObject($data);
        $this->saveInCache((string)$id, $object);
        return $object;
    }

    /**
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function  createContent(WPObject $object, $lang = 'eng-GB'): ?Content
    {
        $values = $this->configResolver->getParameter($this->getConfigRoot(), self::NAMESPACE);
        $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier($values['content_type'] ?? null);
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();
        $parentLocation = $locationService->loadLocation($values['parent_location']??null);
        $mappingFields = $values['mapping']?? [];

        $fields = [];
        foreach ($contentType->getFieldDefinitions()->toArray() as $field) {
            if (array_key_exists($field->identifier, $mappingFields)) {
                $wpObjectAttributeIdentifier = $mappingFields[$field->identifier];
                if (isset($object->$wpObjectAttributeIdentifier)) {
                    $fields[$field->identifier] = ['value' => $object->$wpObjectAttributeIdentifier, 'type' => $field->fieldTypeIdentifier];
                }
            }
        }
        if ($fields) {
            $remoteId = static::CACHE_SUFFIX. '-'.$object->id;
            try {
                $content = $contentService->loadContentByRemoteId($remoteId);

                $contentDraft = $contentService->createContentDraft($content->contentInfo);
                $contentUpdateStruct = $contentService->newContentUpdateStruct();
                $contentUpdateStruct->initialLanguageCode = $lang;

                $this->updateContentStruct($contentUpdateStruct, $fields);

                $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);

                return $contentService->publishVersion($contentDraft->versionInfo);


            } catch (NotFoundException $exception) {
                $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $lang);
                $contentCreateStruct->remoteId = $remoteId;

                $this->updateContentStruct($contentCreateStruct, $fields);
                $locationCreateStruct = $locationService->newLocationCreateStruct($parentLocation->id);

                $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
                return $contentService->publishVersion($draft->versionInfo);
            }
        }
        return null;
    }

    private function prepareRichText($inputText): RichTextValue
    {
        if($inputText === ''){
            $inputText = '&nbsp;';
        }
        if (strip_tags($inputText) === $inputText) {
            $inputText = "<p>{$inputText}</p>";
        }
        if (extension_loaded('tidy')) {
            $tidyConfig = array(
                'show-body-only' => true,
                'output-xhtml'   => true,
                'wrap'           => -1);

            $inputText = tidy_parse_string($inputText, $tidyConfig);

            $inputText = str_replace(array("\r\n", "\r", "\n"), "", $inputText->root()->value);
        }

        $content = ['xml' => '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://ibexa.co/namespaces/ezpublish5/xhtml5/edit">'. $inputText . '</section>'];
        return $this->richTextType->fromHash($content);

    }

    private function updateContentStruct(ContentStruct $contentStruct, $fieldSettings)
    {
        foreach ($fieldSettings as $identifier => $fieldInfo) {
            $fieldValue = $fieldInfo['value'];
            if ($fieldInfo['type'] === 'ezrichtext') {
                $fieldValue = $this->prepareRichText($fieldValue);
            }
            $contentStruct->setField($identifier, $fieldValue);
        }
    }
}