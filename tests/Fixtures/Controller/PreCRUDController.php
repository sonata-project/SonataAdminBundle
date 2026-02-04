<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures\Controller;

use SensioLabs\AdminBundle\Controller\CRUDController;
use SensioLabs\AdminBundle\Tests\Fixtures\Entity\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 *
 * @see ConfigureCRUDControllerListener
 *
 * @phpstan-extends CRUDController<Entity>
 */
final class PreCRUDController extends CRUDController
{
    protected function preCreate(Request $request, object $object): Response
    {
        return new Response(\sprintf('preCreate called: %s', $object->getId()));
    }

    protected function preEdit(Request $request, object $object): Response
    {
        return new Response(\sprintf('preEdit called: %s', $object->getId()));
    }

    protected function preDelete(Request $request, object $object): Response
    {
        return new Response(\sprintf('preDelete called: %s', $object->getId()));
    }

    protected function preShow(Request $request, object $object): Response
    {
        return new Response(\sprintf('preShow called: %s', $object->getId()));
    }

    protected function preList(Request $request): Response
    {
        return new Response('preList called');
    }
}
