<?php

declare(strict_types=1);

namespace Enabel\UserBundle\DependencyInjection;

use Enabel\UserBundle\Entity\User;
use Enabel\UserBundle\Repository\UserRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('enabel_user');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('login_redirect_route')
            ->defaultValue('app_homepage')
            ->end()
            ->scalarNode('user_class')
            ->defaultValue(User::class)
            ->validate()
            ->ifString()
            ->then(static function ($value): string {
                if (!class_exists($value) || !is_a($value, User::class, true)) {
                    throw new InvalidConfigurationException(sprintf(
                        'User class must be a valid class extending %s. "%s" given.',
                        User::class,
                        $value
                    ));
                }

                return $value;
            })
            ->end()
            ->end()
            ->scalarNode('user_repository')
            ->defaultValue(UserRepository::class)
            ->validate()
            ->ifString()
            ->then(static function ($value): string {
                if (!class_exists($value) || !is_a($value, UserRepository::class, true)) {
                    throw new InvalidConfigurationException(sprintf(
                        'User repository must be a valid class extending %s. "%s" given.',
                        UserRepository::class,
                        $value
                    ));
                }

                return $value;
            })
            ->end()
            ->end()
            ->scalarNode('available_locales')
            ->defaultValue('en|fr|nl')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
