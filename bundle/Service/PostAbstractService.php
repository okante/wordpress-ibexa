<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\PostNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\AuthorServiceAware;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\CategoryServiceAware;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\ImageServiceAware;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Post;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

abstract class PostAbstractService extends AbstractService
{
    use CategoryServiceAware;
    use AuthorServiceAware;
    use ImageServiceAware;

    protected string $objectClass    = Post::class;
    protected string $exceptionClass = PostNotFoundException::class;

    protected function createObject(array $data): ?WPObject
    {
        $data['categoryIds'] = (array) ($data['categories'] ?? []);
        $data['authorId']    = (int) ($data['author'] ?? null);
        unset($data['author'], $data['categories']);

        return parent::createObject($data);
    }

    /**
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function createContent(WPObject $object, bool $update = true): ?Content
    {
        if ($object instanceof Post) {
            $postId           = $object->getWPObjectId();
            $remoteId         = static::DATATYPE.'-'.$postId;
            $values           = $this->configResolver->getParameter(static::ROOT, static::NAMESPACE);
            $parentLocationId = $values['parent_location'] ?? null;

            $authorId = $object->authorId;
            if (!empty($authorId)) {
                try {
                    $object->setAuthorContent($this->authorService->createAsSubObject($authorId)->contentInfo);
                } catch (\Exception $e) {
                    $this->error(
                        __METHOD__,
                        [
                            'authorId' => $authorId,
                            'postId' => $postId,
                            'e' => $e->getTraceAsString(),
                        ]
                    );
                }
            }
            $categoryId = (int) (array_values($object->categoryIds)[0] ?? null);
            if (!empty($categoryId)) {
                try {
                    $parentLocationId = $this->categoryService->createAsSubObject(
                        $categoryId
                    )->contentInfo->mainLocationId;
                } catch (\Exception $e) {
                    $this->error(
                        __METHOD__,
                        [
                            'category' => $categoryId,
                            'postId' => $postId,
                            'e' => $e->getTraceAsString(),
                        ]
                    );
                }
            }

            $featureMedia = (int) $object->featured_media;
            if (!empty($featureMedia)) {
                try {
                    $object->setImageContent($this->imageService->createAsSubObject($featureMedia)->contentInfo);
                } catch (\Exception $e) {
                    $this->error(
                        __METHOD__,
                        [
                            'authorId' => $authorId,
                            'postId' => $postId,
                            'e' => $e->getTraceAsString(),
                        ]
                    );
                }
            }

            return $this->innerCreateContent($object, $values, $remoteId, $parentLocationId, $update);
        }

        return null;
    }
}
