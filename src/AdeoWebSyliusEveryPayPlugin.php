<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin;

use AdeoWeb\SyliusEveryPayPlugin\DependencyInjection\Compiler\PayumStoragePaymentAliaser;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AdeoWebSyliusEveryPayPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PayumStoragePaymentAliaser());
    }
}
