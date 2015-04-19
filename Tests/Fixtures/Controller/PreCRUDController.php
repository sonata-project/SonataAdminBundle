<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController;

/**
 * PreCRUDController
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class PreCRUDController extends CRUDController
{
    protected function preCreate(Request $request, $object)
    {
        return new Response(sprintf('preCreate called: %s', $object->foo));
    }

    protected function preEdit(Request $request, $object)
    {
        return new Response(sprintf('preEdit called: %s', $object->foo));
    }

    protected function preDelete(Request $request, $object)
    {
        return new Response(sprintf('preDelete called: %s', $object->foo));
    }

    protected function preShow(Request $request, $object)
    {
        return new Response(sprintf('preShow called: %s', $object->foo));
    }

    protected function preList(Request $request)
    {
        return new Response(sprintf('preList called'));
    }
}
