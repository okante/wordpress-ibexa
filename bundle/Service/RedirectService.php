<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\ConfigResolverTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\IbexaRepositoryTrait;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;

class RedirectService
{
    use IbexaRepositoryTrait;
    use ConfigResolverTrait;

    private PostService $postService;
    private CategoryService $categoryService;

    public function __construct(PostService $postService, CategoryService $categoryService)
    {
        $this->postService = $postService;
        $this->categoryService = $categoryService;
    }

    public function getIbexaLocationId(string $path, array $parameters = []): ?int
    {
        $pathArray = array_filter(explode('/', trim($path)));//'category/marketing-tips';
        $slug = end($pathArray);
        $rootPath = reset($pathArray);
        if (count($pathArray) >= 2 && $rootPath === 'blog' && !empty($slug)) {
            try {
                $rootLocation = $this->repository->getLocationService()->loadLocation($this->getRootLocationId());
                $criteria = [
                    new Criterion\Subtree($rootLocation->pathString),
                    new Criterion\LogicalOr(
                        [
                            new Criterion\LogicalAnd(
                                [
                                    new Criterion\ContentTypeIdentifier($this->categoryService->getContentTypeIdentifier()),
                                    new Criterion\Field($this->categoryService->getSlugFieldIdentifier(), Criterion\Operator::EQ, $slug),
                                ]),
                            new Criterion\LogicalAnd([
                                new Criterion\ContentTypeIdentifier($this->postService->getContentTypeIdentifier()),
                                new Criterion\Field($this->postService->getSlugFieldIdentifier(), Criterion\Operator::EQ, $slug),
                            ])
                        ]
                    ),
                    new Criterion\Visibility(Criterion\Visibility::VISIBLE)
                ];
                $locationQuery = new LocationQuery();
                $locationQuery->filter = new Criterion\LogicalAnd($criteria);
                $locationQuery->limit = 1;

                $searchHits = $this->repository->getSearchService()->findLocations($locationQuery);
                if ($searchHits->totalCount > 0) {
                    return (int) $searchHits->searchHits[0]->valueObject->id;
                }
            } catch (Exception $exception) {

            }

        }
        return null;
    }
}