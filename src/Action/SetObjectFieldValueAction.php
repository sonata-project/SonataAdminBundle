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
use Sonata\AdminBundle\Form\DataTransformerResolverInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormRenderer;
use Twig\Environment;

final class SetObjectFieldValueAction
{
    private RenderElementRuntime $renderElementRuntime;

    public function __construct(
        private Environment $twig,
        private AdminFetcherInterface $adminFetcher,
        private ValidatorInterface $validator,
        private DataTransformerResolverInterface $resolver,
        private PropertyAccessorInterface $propertyAccessor,
        ?RenderElementRuntime $renderElementRuntime = null,
    ) {
        // NEXT_MAJOR: Remove the deprecation and restrict param constructor to RenderElementRuntime.
        if (null === $renderElementRuntime) {
            @trigger_error(\sprintf(
                'Passing null as argument 5 of "%s()" is deprecated since sonata-project/admin-bundle 4.7'
                .' and will throw an error in 5.0. You MUST pass an instance of %s instead.',
                __METHOD__,
                RenderElementRuntime::class
            ), \E_USER_DEPRECATED);
        }
        $this->renderElementRuntime = $renderElementRuntime ?? new RenderElementRuntime($propertyAccessor);
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

        $object = $admin->getObject($objectId);
        if (null === $object) {
            return new JsonResponse('Object does not exist', Response::HTTP_NOT_FOUND);
        }

        // check user permission
        if (false === $admin->hasAccess('edit', $object)) {
            return new JsonResponse('Invalid permissions', Response::HTTP_FORBIDDEN);
        }

        $context = $request->get('context');
        if ('list' !== $context) {
            return new JsonResponse('Invalid context', Response::HTTP_BAD_REQUEST);
        }

        $field = $request->get('field');
        if (!\is_string($field)) {
            throw new BadRequestParamHttpException('field', 'string', $field);
        }

        if (!$admin->hasListFieldDescription($field)) {
            return new JsonResponse('The field does not exist', Response::HTTP_BAD_REQUEST);
        }

        $fieldDescription = $admin->getListFieldDescription($field);
        if (true !== $fieldDescription->getOption('editable')) {
            return new JsonResponse('The field cannot be edited, editable option must be set to true', Response::HTTP_BAD_REQUEST);
        }

        $admin->setSubject($object);
        $formBuilder = $admin->getFormContractor()->getFormBuilder('editable', ['data_class' => $admin->getClass()]);
        $formBuilder->add($fieldDescription->getFieldName());

        $form = $formBuilder->getForm();
        $form->setData($object);
        $form->handleRequest($admin->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $admin->update($object);

            return new Response(
                $this->renderElementRuntime->renderListElement($this->twig, $object, $fieldDescription),
                Response::HTTP_OK
            );
        }

        $status = $form->isSubmitted() && !$form->isValid()
            ? Response::HTTP_BAD_REQUEST
            : Response::HTTP_OK;

        $view = $form->createView();
        $renderer = $this->twig->getRuntime(FormRenderer::class);
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($this->twig->render('@SonataAdmin/Action/set_object_field_value.html.twig', [
            'admin' => $admin,
            'field_description' => $fieldDescription,
            'object' => $object,
            'form' => $view,
        ]), $status);
    }
}
