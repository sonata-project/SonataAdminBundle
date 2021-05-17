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
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class GetShortObjectDescriptionAction
{
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * NEXT_MAJOR: Restrict second param to AdminFetcherInterface.
     *
     * @param Pool|AdminFetcherInterface $poolOrAdminFetcher
     */
    public function __construct(Environment $twig, $poolOrAdminFetcher)
    {
        $this->twig = $twig;

        if ($poolOrAdminFetcher instanceof AdminFetcherInterface) {
            $this->adminFetcher = $poolOrAdminFetcher;
        } elseif ($poolOrAdminFetcher instanceof Pool) {
            @trigger_error(sprintf(
                'Passing other type than %s in argument 2 to %s() is deprecated since sonata-project/admin-bundle 3.x'
                .' and will throw %s exception in 4.0.',
                AdminFetcherInterface::class,
                __METHOD__,
                \TypeError::class
            ), \E_USER_DEPRECATED);

            $this->adminFetcher = new AdminFetcher($poolOrAdminFetcher);
        } else {
            throw new \TypeError(sprintf(
                'Argument 2 passed to "%s()" must be either an instance of %s or %s, %s given.',
                __METHOD__,
                Pool::class,
                AdminFetcherInterface::class,
                \is_object($poolOrAdminFetcher) ? 'instance of "'.\get_class($poolOrAdminFetcher).'"' : '"'.\gettype($poolOrAdminFetcher).'"'
            ));
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        // NEXT_MAJOR: Remove this BC-layer.
        if (null === $request->get('_sonata_admin')) {
            @trigger_error(
                'Not passing "_sonata_admin" value in the request is deprecated since sonata-project/admin-bundle 3.x'
                .' and will throw %s exception in 4.0.',
                \E_USER_DEPRECATED
            );

            $request->query->set('_sonata_admin', $request->get('code'));
        }

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf(
                'Could not find admin for code "%s"',
                $request->get('_sonata_admin')
            ));
        }

        $objectId = $request->get('objectId');
        $object = $admin->getObject($objectId);

        if (!$object) {
            // NEXT_MAJOR: Remove the deprecation and uncomment the exception.
            @trigger_error(
                'Trying to get a short object description for a non found object is deprecated'
                .' since sonata-project/admin-bundle 3.76 and will be throw a 404 in version 4.0.',
                \E_USER_DEPRECATED
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
                'link_parameters' => $request->get('linkParameters', []),
            ]));
        }

        throw new \RuntimeException('Invalid format');
    }
}
