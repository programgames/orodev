<?php

namespace Programgames\OroDev;

use Exception;
use Programgames\OroDev\DependencyInjection\CompilerPass\CommandCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    /**
     * In more complex app, add bundles here
     */
    public function registerBundles(): array
    {
        return [];
    }

    /**
     * Load all services
     * @param LoaderInterface $loader
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/services/services.yml');
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CommandCompilerPass());
    }
}
