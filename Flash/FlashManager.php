<?php

namespace Sonata\AdminBundle\Flash;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class FlashManager implements FlashManagerInterface
{
    protected $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    public function getFlashBag()
    {
        return $this->flashBag;
    }

    public function addFlash($type, $message)
    {
        $this->flashBag->add($type, $message);
    }
}