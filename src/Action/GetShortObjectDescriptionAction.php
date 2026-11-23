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

use Sonata\AdminBundle\BCLayer\BCHelper;
use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

final class GetShortObjectDescriptionAction
{
    public function __construct(
        private Environment $twig,
        private AdminFetcherInterface $adminFetcher,
        private ?RequestStack $requestStack = null,
    ) {
        // NEXT_MAJOR: Remove the deprecation and make the argument mandatory.
        if (null === $requestStack) {
            @trigger_error(\sprintf(
                'Not passing an instance of %s as argument 3 of "%s()" is deprecated since'
                .' sonata-project/admin-bundle 4.x and will throw an error in 5.0.',
                RequestStack::class,
                __METHOD__
            ), \E_USER_DEPRECATED);
        }
    }

    /**
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $objectId = BCHelper::getFromRequest($request, 'objectId');
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

        // The action shows details of the object, so direct requests need the show permission on it.
        if ($this->mustCheckAccess($request)) {
            $admin->checkAccess('show', $object);
        }

        if ('json' === BCHelper::getFromRequest($request, '_format')) {
            return new JsonResponse(['result' => [
                'id' => $admin->id($object),
                'label' => $admin->toString($object),
            ]]);
        }

        if ('html' === BCHelper::getFromRequest($request, '_format')) {
            $templateRegistry = $admin->getTemplateRegistry();

            return new Response($this->twig->render($templateRegistry->getTemplate('short_object_description'), [
                'admin' => $admin,
                'description' => $admin->toString($object),
                'object' => $object,
                'link_parameters' => BCHelper::getFromRequest($request, 'linkParameters', []),
            ]));
        }

        throw new BadRequestHttpException('Invalid format');
    }

    /**
     * The model list and the one-to-one/many-to-one association widgets render the description of
     * their value through a sub request. These sub requests are not checked, otherwise the form would
     * break for users without any permission on the admin of the associated object.
     *
     * Note that the rendered value is not always the stored one: when a form is shown again after a
     * submit, it is the submitted value. So this doesn't keep the labels of the associated admin away
     * from somebody who can edit the form, it only checks direct requests.
     */
    private function mustCheckAccess(Request $request): bool
    {
        // NEXT_MAJOR: Remove this, the request stack will be mandatory.
        // Without the request stack sub requests can't be told apart, so keep the old behaviour.
        if (null === $this->requestStack) {
            return false;
        }

        $mainRequest = $this->requestStack->getMainRequest();

        return null === $mainRequest || $mainRequest === $request;
    }
}
