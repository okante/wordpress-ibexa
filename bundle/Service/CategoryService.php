<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\CategoryNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Category;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

final class CategoryService extends AbstractService
{
    public const ROOT        = 'categories';
    public const SERVICE_URL = '/categories';
    public const DATATYPE    = 'category';

    protected string $objectClass    = Category::class;
    protected string $exceptionClass = CategoryNotFoundException::class;

    public function createContent(WPObject $object, $lang = 'eng-GB', bool $update = false): ?Content
    {
        if ($object instanceof Category) {
            $remoteId         = self::DATATYPE.'-'.$object->getWPObjectId();
            $values           = $this->configResolver->getParameter(self::ROOT, self::NAMESPACE);
            $parentLocationId = $values['parent_location'] ?? null;
            if (!empty($object->parent)) {
                $this->info($object->getWPObjectId().' => '.$object->parent.('('.$object->getWPObjectTitle().')'));
                try {
                    $parentCategory = $this->getOne($object->parent);
                    if ($parentCategory) {
                        $parentContent = $this->createContent($parentCategory, $lang, $update);
                        if ($parentContent) {
                            $parentLocationId = $parentContent->contentInfo->mainLocationId;
                        }
                    }
                } catch (Exception $e) {
                    $this->error('Unable to create Category', ['e' => $e]);
                }
            }

            return $this->innerCreateContent($object, $values, $remoteId, $parentLocationId, $lang, $update);
        }

        return null;
    }
}
