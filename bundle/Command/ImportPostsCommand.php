<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Command;

use Almaviacx\Bundle\Ibexa\WordPress\Service\PostService;
use Carbon\Carbon;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Content as ValueContent;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Repository;
use Novactive\Bundle\eZExtraBundle\Core\Manager\eZ\Content as ContentManager;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportPostsCommand extends Command
{
    protected Repository $repository;
    private ConfigResolverInterface $configResolver;
    private PostService $postService;

    /**
     * @required
     */
    public function setDependencies(
        PostService $postService,
        ConfigResolverInterface $configResolver,
        Repository $repository
    ) {
        $this->postService = $postService;
        $this->repository = $repository;
        $this->configResolver = $configResolver;
    }

    protected function configure()
    {
        $this
            ->setName('wordpress:ibexa:import')
            ->addOption(
                'per-page',
                null,
                InputOption::VALUE_OPTIONAL,
                'Per page',
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Rolls back any database changes'
            )
            ->setDescription('Import Blog Posts from wordpress to ibexa content');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $posts = $this->postService->get();
        //dump($posts);
        dump(count($posts));
        $io->success('Done');
        return Command::SUCCESS;
    }

    protected function fakeexecute(InputInterface $input, OutputInterface $output)
    {
        $io         = new SymfonyStyle($input, $output);
        $url        = $this->configResolver->getParameter('posts_url', 'hibu_blog');
        $rss        = simplexml_load_file($url);
        $namespaces = $rss->channel->item->getNamespaces(true);

        $rootLocationId = $this->configResolver->getParameter('root_location_id', 'hibu_blog');

        foreach ($rss->channel->item as $item) {
            $description = $item->description[0]->__toString();
            $content                = [];
            $content['title']       = (string) $item->title;
            $content['richtext']    = <<<DESCRIPTION
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://ibexa.co/namespaces/ezpublish5/xhtml5/edit">
$description
</section>
DESCRIPTION;
            $item->description[0]->__toString();
            //$content['remoteid']    = md5((string) $item->guid);
            try {
                $this->repository->sudo(function () use ($item, $content, $rootLocationId, $io) {
                    //$content['pubdate'] = Carbon::createFromFormat(DateTimeInterface::RFC2822, (string)$item->pubDate);
                    $this->sync($content, $rootLocationId, $io);
                });
            } catch (Exception $exception) {
                dump($exception);
            }
            break;
        }

        $io->success('Done');
        return Command::SUCCESS;
    }

    protected function sync($content, int $parentLocationId, SymfonyStyle $io)
    {
        try{
            $content  = $this->createUpdateContent(
                'test_richtext',
                $this->container->getParameter('hibu_blog_posts_container_location_id'),
                $content
            );
        }catch  (\Exception $exception){
            throw new \RuntimeException(__METHOD__, 400, $exception);
        }

        $io->note("Content {$content->id} synced. ({$content->contentInfo->name})");
    }

    public function createUpdateContent(
        string $contentTypeIdentifier,
        int $parentLocationId,
        array $data,
        string $lang = 'eng-US'
    ): ?ValueContent
    {
        try {
            $contentService = $this->repository->getContentService();
            $locationService = $this->repository->getLocationService();
            $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier($contentTypeIdentifier);
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $lang);

            foreach ($contentType->getFieldDefinitions() as $field) {
                /** @var FieldDefinition $field */
                $fieldName = $field->identifier;
                if (!\array_key_exists($fieldName, $data)) {
                    continue;
                }
                $contentCreateStruct->setField($fieldName, $data[$fieldName]);
            }


            $locationCreateStruct = $locationService->newLocationCreateStruct($parentLocationId);

            $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
            return $contentService->publishVersion($draft->versionInfo);

        } catch (\Exception $exception) {
            throw new \RuntimeException(__METHOD__, 401, $exception);
        }
    }
}
