<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\PostNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Post;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Exception;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

final class PostService extends AbstractService
{
    public const ROOT = 'posts';
    public const SERVICE_URL = '/posts';
    public const DATATYPE = 'post';
    private CategoryService $categoryService;
    private AuthorService $authorService;


    protected string $objectClass = Post::class;
    protected string $exceptionClass = PostNotFoundException::class;

    /**
     * @required
     * @param CategoryService $categoryService
     * @param AuthorService $authorService
     * @return $this
     */
    public function setRelatedServices(CategoryService $categoryService, AuthorService $authorService): PostService
    {
        $this->categoryService = $categoryService;
        $this->authorService = $authorService;
        return $this;
    }

    protected function createObject(array $data): ?WPObject
    {
        $data['categoryIds'] = (array)($data['categories']?? []);
        $data['authorId'] = (int) ($data['author']?? null);
        unset($data['author'], $data['categories']);
        return parent::createObject($data);
    }

    /**
     * @param WPObject $object
     * @param $lang
     * @param bool $update
     * @return Content|null
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function  createContent(WPObject $object, $lang = 'eng-GB', bool $update = true): ?Content
    {
        if ($object instanceof Post) {
            $postId = $object->getWPObjectId();
            $remoteId = self::DATATYPE. '-'. $postId;
            $values = $this->configResolver->getParameter(self::ROOT, self::NAMESPACE);
            $parentLocationId = $values['parent_location']??null;

            $authorId = $object->authorId;
            if (!empty($authorId)) {
                try {
                    $object->setAuthorContent($this->authorService->createAsSubObject($authorId)->contentInfo);
                } catch(Exception $e) {
                    $this->error(__METHOD__, ['authorId' => $authorId, 'postId' => $postId, 'e' => $e->getTraceAsString()]);
                }
            }
            $categoryId = (int) (array_values($object->categoryIds)[0] ?? null);// array_shift(array_values($array));($object->categoryIds);
            if (!empty($categoryId)) {
                try {
                    $parentLocationId = $this->categoryService->createAsSubObject($categoryId)->contentInfo->mainLocationId;
                } catch(Exception $e) {
                    $this->error(__METHOD__, ['category' => $categoryId, 'postId' => $postId, 'e' => $e->getTraceAsString()]);
                }
            }
            return $this->innerCreateContent($object, $values, $remoteId, $parentLocationId, $lang, $update);
        }
        return null;
    }

}