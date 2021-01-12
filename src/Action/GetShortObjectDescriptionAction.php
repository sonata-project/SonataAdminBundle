<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Action;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class GetShortObjectDescriptionAction
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, Pool $pool)
    {
        $this->pool = $pool;
        $this->twig = $twig;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        $code = $request->get('code');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');
        $linkParameters = $request->get('linkParameters', []);

        try {
            $admin = $this->pool->getInstance($code);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf('Could not find admin for code "%s"', $code));
        }

        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $object = $admin->getObject($objectId);

        if (!$object) {
            // NEXT_MAJOR: Remove the deprecation and uncomment the exception.
            @trigger_error(
                'Trying to get a short object description for a non found object is deprecated'
                .' since sonata-project/admin-bundle 3.76 and will be throw a 404 in version 4.0.',
                E_USER_DEPRECATED
            );
            //throw new NotFoundHttpException(sprintf('Could not find subject for id "%s"', $objectId));

            // NEXT_MAJOR: Remove this.
            if ('html' === $request->get('_format')) {
                return new Response();
            }
        }

        if ('json' === $request->get('_format')) {
            return new JsonResponse(['result' => [
                'id' => $admin->id($object),
                'label' => $admin->toString($object),
            ]]);
        }

        if ('html' === $request->get('_format')) {
            return new Response($this->twig->render($admin->getTemplate('short_object_description'), [
                'admin' => $admin,
                'description' => $admin->toString($object),
                'object' => $object,
                'link_parameters' => $linkParameters,
            ]));
        }

        throw new \RuntimeException('Invalid format');
    }
}
