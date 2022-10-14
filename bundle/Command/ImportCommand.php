<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Command;

use Almaviacx\Bundle\Ibexa\WordPress\Service\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCommand extends Command
{
    private ServiceInterface $service;

    public function __construct(ServiceInterface $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    protected function configure()
    {
        $this
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
                'export-images',
                null,
                InputOption::VALUE_NONE,
                'page'
            )
            ->setDescription('Import Blog content to ibexa');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $perPage = (int) $input->getOption('per-page');
        $perPage = $perPage > 0 ? $perPage : null;
        $page    = (int) $input->getOption('page');
        $page    = $page > 0 ? $page : null;
        $count   = $this->service->import($perPage, $page, $input->getOptions());
        $io->info('content imported => (success:'.($count->success ?? '').',total:'.($count->total ?? '').')');
        $io->success('Done');

        return Command::SUCCESS;
    }
}
