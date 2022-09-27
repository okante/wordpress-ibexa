<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection;

use Almaviacx\Bundle\Ibexa\WordPress\Service\AuthorService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\CategoryService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\ImageService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\MediaService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\PostService;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SAConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SAConfiguration
{
    public const NAMESPACE = WordPressIbexaExtension::EXTENSION_NAME;

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::NAMESPACE);

        $rootNode   = $treeBuilder->getRootNode();
        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode->scalarNode('url')->example('https://blog.hibu.com')->defaultValue('')->isRequired()->end()
            ->append($this->addParametersNode(PostService::ROOT))
            ->append($this->addParametersNode(AuthorService::ROOT))
            ->append($this->addParametersNode(CategoryService::ROOT))
            ->append($this->addParametersNode('pages'))
            ->append($this->addParametersNode('tags'))
            ->append($this->addParametersNode(MediaService::ROOT))
            ->append($this->addParametersNode(ImageService::ROOT))
/*
            ->scalarNode('post_type_dentifier')->example('blog_post')->defaultValue('')->isRequired()->end()
            ->arrayNode('post_fields_mapping')
                ->useAttributeAsKey('name')
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()

            ->arrayNode('posts')
                ->children()
                    ->scalarNode('content_type')->example('blog_post')->isRequired()->end()
                    ->scalarNode('parent_location')->example(2)->defaultValue(2)->isRequired()->end()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('name')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('pages')
                ->children()
                    ->scalarNode('content_type')->example('blog_post')->isRequired()->end()
                    ->scalarNode('parent_location')->example(2)->defaultValue(2)->isRequired()->end()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('name')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('categories')
                ->children()
                    ->scalarNode('content_type')->example('tags')->isRequired()->end()
                    ->scalarNode('parent_location')->example(2)->defaultValue(2)->isRequired()->end()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('name')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('tags')
                ->children()
                    ->scalarNode('content_type')->example('tags')->isRequired()->end()
                    ->scalarNode('parent_location')->example(2)->defaultValue(2)->isRequired()->end()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('name')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
//            ->variableNode('post_fields_mapping')->defaultValue([])->end()
*/
        ;

        return $treeBuilder;
    }

    private function addParametersNode(string $namespace)
    {
        return (new TreeBuilder($namespace))->getRootNode()
                ->children()
                    ->scalarNode('content_type')->defaultValue('wp_'.$namespace)->example('wp_'.$namespace)->isRequired()->end()
                    ->integerNode('parent_location')->example(2)->defaultValue(2)->isRequired()->end()
                    ->scalarNode('per_page')->example(2)->defaultValue(10)->isRequired()->end()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('name')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
        ;
    }
}
