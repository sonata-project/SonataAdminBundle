<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DemoAdminBundle  extends Bundle
{
    protected $path = null;

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}
