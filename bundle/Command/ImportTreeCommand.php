<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Command;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\Service\CategoryService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\PostService;
use Carbon\Carbon;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Content as ValueContent;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Repository;
use Novactive\Bundle\eZExtraBundle\Core\Manager\eZ\Content as ContentManager;
use Psr\Cache\InvalidArgumentException;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportTreeCommand extends Command
{
    private CategoryService $categoryService;

    /**
     * @required
     */
    public function setDependencies(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    protected function configure()
    {
        $this
            ->setName('wordpress:ibexa:import:tree')
            ->addOption(
                'per-page',
                null,
                InputOption::VALUE_OPTIONAL,
                'Per page'
            )
            ->addOption(
                'page',
                null,
                InputOption::VALUE_OPTIONAL,
                'page'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Rolls back any database changes'
            )
            ->setDescription('Import Blog Posts from wordpress to ibexa content');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $perPage = (int) ($input->getOption('per-page'));
        $perPage = $perPage > 0? $perPage: null;
        $count = $this->categoryService->import($perPage);
        $io->info("Post imported => {$count}");
        $io->success('Done');
        return Command::SUCCESS;
    }
}
//php bin/console w:i:im -vv