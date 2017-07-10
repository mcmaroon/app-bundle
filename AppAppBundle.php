<?php

namespace App\AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\AppBundle\DependencyInjection\TranslationCompilerPass;

class AppAppBundle extends Bundle {

    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new TranslationCompilerPass());
    }

}
