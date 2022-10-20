<?php

declare(strict_types=1);

namespace Enabel\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserBundleExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
//        $container->prependExtensionConfig('stof_doctrine_extensions', [
//            'orm' => [
//                'default' => [
//                    'timestampable' => true,
//                ],
//            ],
//        ]);

        $container->prependExtensionConfig('twig', [
            'form_themes' => ['bootstrap_5_layout.html.twig'],
        ]);
    }

    public function getAlias(): string
    {
        return 'enabel_user';
    }
}
