<?php

namespace Angle\Common\S3Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AngleCommonS3Extension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (!isset($config['amazon_s3']['amazon_s3_key'])) {
            throw new \InvalidArgumentException(
                'The option "angle_common_s3.amazon_s3.amazon_s3_key" must be set.'
            );
        }

        $container->setParameter(
            'angle_common_s3.amazon_s3.amazon_s3_key',
            $config['amazon_s3']['amazon_s3_key']
        );

        if (!isset($config['amazon_s3']['amazon_s3_secret'])) {
            throw new \InvalidArgumentException(
                'The option "angle_common_s3.amazon_s3.amazon_s3_secret" must be set.'
            );
        }

        $container->setParameter(
            'angle_common_s3.amazon_s3.amazon_s3_secret',
            $config['amazon_s3']['amazon_s3_secret']
        );

        if (!isset($config['amazon_s3']['amazon_s3_bucket'])) {
            throw new \InvalidArgumentException(
                'The option "angle_common_s3.amazon_s3.amazon_s3_bucket" must be set.'
            );
        }

        $container->setParameter(
            'angle_common_s3.amazon_s3.amazon_s3_bucket',
            $config['amazon_s3']['amazon_s3_bucket']
        );

        if (!isset($config['amazon_s3']['amazon_s3_region'])) {
            throw new \InvalidArgumentException(
                'The option "angle_common_s3.amazon_s3.amazon_s3_region" must be set.'
            );
        }

        $container->setParameter(
            'angle_common_s3.amazon_s3.amazon_s3_region',
            $config['amazon_s3']['amazon_s3_region']
        );
    }

    /**
     * {@inheritdoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getAlias()
    {
        return 'angle_common_s3';
    }
}
