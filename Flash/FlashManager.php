<?php

namespace Sonata\AdminBundle\Flash;

// use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class FlashManager implements FlashManagerInterface
{
    protected $flashBag;

    public function __construct(Session $session)
    {
        $this->flashBag = $session->getFlashBag();
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