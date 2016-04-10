<?php

namespace RMA\Bundle\DumpBundle\Tools;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface ToolsInterface
{
    public function hydrateInputOptions (InputInterface $input, ContainerInterface $container);
}
