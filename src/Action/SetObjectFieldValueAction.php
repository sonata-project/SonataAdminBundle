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

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformerResolverInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Twig\Extension\RenderElementExtension;
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
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var DataTransformerResolverInterface
     */
    private $resolver;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(
        Environment $twig,
        AdminFetcherInterface $adminFetcher,
        ValidatorInterface $validator,
        DataTransformerResolverInterface $resolver,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->adminFetcher = $adminFetcher;
        $this->twig = $twig;
        $this->validator = $validator;
        $this->resolver = $resolver;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf(
                'Could not find admin for code "%s".',
                $request->get('_sonata_admin')
            ));
        }

        $field = $request->get('field');
        $objectId = $request->get('objectId');
        $value = $originalValue = $request->get('value');
        $context = $request->get('context');

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

        $object = $admin->getObject($objectId);
        if (null === $object) {
            return new JsonResponse('Object does not exist', Response::HTTP_NOT_FOUND);
        }

        // check user permission
        if (false === $admin->hasAccess('edit', $object)) {
            return new JsonResponse('Invalid permissions', Response::HTTP_FORBIDDEN);
        }

        if ('list' !== $context) {
            return new JsonResponse('Invalid context', Response::HTTP_BAD_REQUEST);
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
            $propertyPath = new PropertyPath($field);
        }

        if ('' === $value) {
            $this->propertyAccessor->setValue($object, $propertyPath, null);
        } else {
            $dataTransformer = $this->resolver->resolve($fieldDescription, $admin->getModelManager());

            if ($dataTransformer instanceof DataTransformerInterface) {
                $value = $dataTransformer->reverseTransform($value);
            }

            if (null === $value && FieldDescriptionInterface::TYPE_CHOICE === $fieldDescription->getType()) {
                return new JsonResponse(sprintf(
                    'Edit failed, object with id: %s not found in association: %s.',
                    $originalValue,
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

        $admin->update($object);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $extension = $this->twig->getExtension(RenderElementExtension::class);
        \assert($extension instanceof RenderElementExtension);

        $content = $extension->renderListElement($this->twig, $rootObject, $fieldDescription);

        return new JsonResponse($content, Response::HTTP_OK);
    }
}
