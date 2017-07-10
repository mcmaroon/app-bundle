<?php

namespace App\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TranslationCompilerPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container) {
        $container->getDefinition('translator.default')->addMethodCall('addResource', [
            'yml',
            __DIR__ . '/../Resources/translations/global.yml',
            'pl'
        ]);
    }

}
