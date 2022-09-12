<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\PostNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Post;

final class PostService extends AbstractService
{
    public const ROOT = 'posts';
    public const SERVICE_URL = '/posts';
    public const CACHE_SUFFIX = 'post';
    private CategoryService $categoryService;
    private AuthorService $authorService;


    protected string $objectClass = Post::class;
    protected string $exceptionClass = PostNotFoundException::class;

    /**
     * @required
     * @param CategoryService $categoryService
     * @return $this
     */
    public function setServices(CategoryService $categoryService, AuthorService $authorService): PostService
    {
        $this->categoryService = $categoryService;
        $this->authorService = $authorService;
        return $this;
    }

    public function get(int $page = 1, ?int $perPage = null, array $options = []): array
    {
        $posts = [];
        $postsArray = parent::get($page, $perPage, $options);
        try {
            foreach ($postsArray as $postArray) {
                $categories = [];
                $postId = $postArray['id'];
                foreach ($postArray['categories'] as $category) {
                    try {
                        $categories[] = $this->categoryService->getOne((int)$category);
                    } catch(\Exception $e) {
                        $this->logger->error(__METHOD__, ['category' => $category, 'postId' => $postId, 'e' => $e->getTraceAsString()]);
                    }
                }
                $postArray['categories'] = $categories;

                $authorId =  (int) ($postArray['author']?? null);
                $author = null;
                if ($authorId) {
                    try {
                        $author = $this->authorService->getOne((int)$authorId);
                    } catch(\Exception $e) {
                        $this->logger->error(__METHOD__, ['authorId' => $authorId, 'postId' => $postId, 'e' => $e->getTraceAsString()]);
                    }
                }
                $postArray['author'] = $author;

                try {
                    /** @var Post $post */
                    $post = $this->createObject($postArray);
                    if ($post !== null) {
                        $this->logger->info($post->title);
                    }
                    $posts[] = $post;
                } catch(\Exception $e) {
                    $this->logger->error(__METHOD__, ['postId' => $postId, 'e' => $e->getTraceAsString()]);
                }
            }
            return $posts;
        } catch (Exception $e) {
            $this->logger->error(__METHOD__, ['postId' => $page, 'perPage' => $perPage, 'options' => $options, 'e' => $e]);
            return [];
        }
    }
}