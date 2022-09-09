<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection;

use Exception;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WordPressIbexaExtension extends Extension implements PrependExtensionInterface
{
    public const EXTENSION_NAME = 'word_press_ibexa';

    public function getAlias(): string
    {
        return self::EXTENSION_NAME;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $processor     = new ConfigurationProcessor($container, Configuration::NAMESPACE);
        $processor->mapSetting('url', $config);
        $processor->mapConfigArray('posts', $config/*, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL*/);
        $processor->mapConfigArray('pages', $config/*, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL*/);
        $processor->mapConfigArray('categories', $config/*, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL*/);
        $processor->mapConfigArray('tags', $config/*, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL*/);
        /*
        $processor->mapConfig(
            $config,
            function ($scopeSettings, $currentScope, ContextualizerInterface $contextualizer) {
                $contextualizer->setContextualParameter('url', $currentScope, $scopeSettings['url']);
                $contextualizer->setContextualParameter('posts', $currentScope, $scopeSettings['posts']);
                $contextualizer->setContextualParameter('pages', $currentScope, $scopeSettings['pages']);
                $contextualizer->setContextualParameter('categories', $currentScope, $scopeSettings['categories']);
                $contextualizer->setContextualParameter('tags', $currentScope, $scopeSettings['tags']);
                //$contextualizer->mapConfigArray('posts', $scopeSettings['posts'], ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
                $contextualizer->mapConfigArray('categories', $scopeSettings['categories'], ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
                $contextualizer->mapConfigArray('tags', $scopeSettings['tags'], ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
            }
        );
        */

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * @throws Exception
     */
    public function prepend(ContainerBuilder $container)
    {
        $configPath = __DIR__.'/../Resources/config';
        $loader     = new Loader\YamlFileLoader($container, new FileLocator($configPath));
        $loader->load('default_settings.yaml');
        $loader->load('logger.yaml');
    }
}
