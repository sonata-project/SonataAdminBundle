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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformerResolverInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
        ?RenderElementRuntime $renderElementRuntime = null
    ) {
        // NEXT_MAJOR: Remove the deprecation and restrict param constructor to RenderElementRuntime.
        if (null === $renderElementRuntime) {
            @trigger_error(sprintf(
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
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        // alter should be done by using a post method
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse('Expected an XmlHttpRequest request header', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if (Request::METHOD_POST !== $request->getMethod()) {
            return new JsonResponse(sprintf(
                'Invalid request method given "%s", %s expected',
                $request->getMethod(),
                Request::METHOD_POST
            ), Response::HTTP_METHOD_NOT_ALLOWED);
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

        $propertyPath = new PropertyPath($field);
        $rootObject = $object;

        // If property path has more than 1 element, take the last object in order to validate it
        $parent = $propertyPath->getParent();
        if (null !== $parent) {
            $object = $this->propertyAccessor->getValue($object, $parent);

            $elements = $propertyPath->getElements();
            $field = end($elements);
            \assert(\is_string($field));

            $propertyPath = new PropertyPath($field);
        }

        $value = $request->get('value');

        if ('' === $value) {
            $this->propertyAccessor->setValue($object, $propertyPath, null);
        } else {
            $dataTransformer = $this->resolver->resolve($fieldDescription, $admin->getModelManager());

            if ($dataTransformer instanceof DataTransformerInterface) {
                $value = $dataTransformer->reverseTransform($value);
            }

            if (null === $value && FieldDescriptionInterface::TYPE_CHOICE === $fieldDescription->getType()) {
                return new JsonResponse(sprintf(
                    'Edit failed, object with id "%s" not found in association "%s".',
                    $objectId,
                    $field
                ), Response::HTTP_NOT_FOUND);
            }

            $this->propertyAccessor->setValue($object, $propertyPath, $value);
        }

        $violations = $this->validator->validate($object);

        if (\count($violations) > 0) {
            $messages = [];

            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            return new JsonResponse(implode("\n", $messages), Response::HTTP_BAD_REQUEST);
        }

        \assert(\is_object($object));
        $admin->update($object);

        // render the widget
        $content = $this->renderElementRuntime->renderListElement($this->twig, $rootObject, $fieldDescription);

        return new JsonResponse($content, Response::HTTP_OK);
    }
}
