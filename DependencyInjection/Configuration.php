<?php

namespace Pirastru\FormBuilderBundle\DependencyInjection;

use Pirastru\FormBuilderBundle\Handler\SimpleFileHandler;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('pirastru_formbuilder');
        $rootNode = $treeBuilder->getRootNode();
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('email_no_reply')->defaultValue('no-reply@exemple.com')->end()
                ->scalarNode('formbuilder_email_from')->defaultValue('mail_from@exemple.com')->end()
                ->scalarNode('file_handler')->defaultValue(SimpleFileHandler::class)->end()
            ->end();

        return $treeBuilder;
    }
}
