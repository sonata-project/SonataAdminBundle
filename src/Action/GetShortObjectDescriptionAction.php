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

use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class GetShortObjectDescriptionAction
{
    public function __construct(
        private Environment $twig,
        private AdminFetcherInterface $adminFetcher,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $objectId = $request->get('objectId');
        if (!\is_string($objectId) && !\is_int($objectId)) {
            throw new BadRequestParamHttpException('objectId', ['string', 'int'], $objectId);
        }

        // If the subclass parameter is present it can cause conflict with other admin.
        // The admin do not need subclass parameter to load an existing object.
        $subclass = $request->query->get('subclass');
        $request->query->remove('subclass');

        try {
            $object = $admin->getObject($objectId);
            if (null === $object) {
                throw new NotFoundHttpException(\sprintf('Could not find subject for id "%s"', $objectId));
            }
        } finally {
            // Restore the subclass if present to reduce impact of the parameter removal above.
            if (null !== $subclass) {
                $request->query->set('subclass', $subclass);
            }
        }

        if ('json' === $request->get('_format')) {
            return new JsonResponse(['result' => [
                'id' => $admin->id($object),
                'label' => $admin->toString($object),
            ]]);
        }

        if ('html' === $request->get('_format')) {
            $templateRegistry = $admin->getTemplateRegistry();

            return new Response($this->twig->render($templateRegistry->getTemplate('short_object_description'), [
                'admin' => $admin,
                'description' => $admin->toString($object),
                'object' => $object,
                'link_parameters' => $request->get('linkParameters', []),
            ]));
        }

        throw new BadRequestHttpException('Invalid format');
    }
}
