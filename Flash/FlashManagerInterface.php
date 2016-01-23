<?php

namespace Sonata\AdminBundle\Flash;

interface FlashManagerInterface
{
    public function getFlashBag();

    public function addFlash($type, $message);
}