<?php

namespace Programgames\OroDev\DependencyInjection\CompilerPass;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $applicationDefinition = $container->getDefinition(Application::class);

        foreach ($container->getDefinitions() as $name => $definition) {
            if (is_a($definition->getClass(), Command::class, true)) {
                $applicationDefinition->addMethodCall('add', [new Reference($name)]);
            }
        }
    }
}
