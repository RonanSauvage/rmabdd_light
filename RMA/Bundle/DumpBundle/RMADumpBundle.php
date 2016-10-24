<?php

namespace RMA\Bundle\DumpBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use RMA\Bundle\DumpBundle\DependencyInjection\RMADumpExtension;

class RMADumpBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new RMADumpExtension();
    }
}
