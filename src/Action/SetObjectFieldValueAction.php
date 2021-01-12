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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Form\DataTransformerResolverInterface;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
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
     * @var Pool
     */
    private $pool;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var DataTransformerResolver
     */
    private $resolver;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * NEXT_MAJOR: Make all arguments mandatory.
     *
     * @param ValidatorInterface           $validator
     * @param DataTransformerResolver|null $resolver
     */
    public function __construct(
        Environment $twig,
        Pool $pool,
        $validator,
        $resolver = null,
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!($validator instanceof ValidatorInterface)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 3 is an instance of %s, expecting an instance of %s',
                \get_class($validator),
                ValidatorInterface::class
            ));
        }

        // NEXT_MAJOR: Move DataTransformerResolver check to method signature
        if (!$resolver instanceof DataTransformerResolverInterface) {
            @trigger_error(sprintf(
                'Passing other type than %s in argument 4 to %s() is deprecated since sonata-project/admin-bundle 3.76 and will throw %s exception in 4.0.',
                DataTransformerResolverInterface::class,
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);
            $resolver = new DataTransformerResolver();
        }

        // NEXT_MAJOR: Remove this check.
        if (null === $propertyAccessor) {
            @trigger_error(sprintf(
                'Omitting the argument 5 for "%s()" or passing "null" is deprecated since sonata-project/admin-bundle'
                .' 3.82 and will throw a \TypeError error in version 4.0. You must pass an instance of %s instead.',
                __METHOD__,
                PropertyAccessorInterface::class
            ), E_USER_DEPRECATED);

            $propertyAccessor = $pool->getPropertyAccessor();
        }

        $this->pool = $pool;
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
        $field = $request->get('field');
        $code = $request->get('code');
        $objectId = $request->get('objectId');
        $value = $originalValue = $request->get('value');
        $context = $request->get('context');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

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

        $rootObject = $object = $admin->getObject($objectId);

        if (!$object) {
            return new JsonResponse('Object does not exist', Response::HTTP_NOT_FOUND);
        }

        // check user permission
        if (false === $admin->hasAccess('edit', $object)) {
            return new JsonResponse('Invalid permissions', Response::HTTP_FORBIDDEN);
        }

        if ('list' === $context) {
            $fieldDescription = $admin->getListFieldDescription($field);
        } else {
            return new JsonResponse('Invalid context', Response::HTTP_BAD_REQUEST);
        }

        if (!$fieldDescription) {
            return new JsonResponse('The field does not exist', Response::HTTP_BAD_REQUEST);
        }

        if (!$fieldDescription->getOption('editable')) {
            return new JsonResponse('The field cannot be edited, editable option must be set to true', Response::HTTP_BAD_REQUEST);
        }

        $propertyPath = new PropertyPath($field);

        // If property path has more than 1 element, take the last object in order to validate it
        if ($propertyPath->getLength() > 1) {
            $object = $this->propertyAccessor->getValue($object, $propertyPath->getParent());

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

            if (!$value && FieldDescriptionInterface::TYPE_CHOICE === $fieldDescription->getType()) {
                return new JsonResponse(sprintf(
                    'Edit failed, object with id: %s not found in association: %s.',
                    $originalValue,
                    $field
                ), Response::HTTP_NOT_FOUND);
            }

            $this->propertyAccessor->setValue($object, $propertyPath, $value);
        }

        $violations = $this->validator->validate($object);

        if (\count($violations)) {
            $messages = [];

            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            return new JsonResponse(implode("\n", $messages), Response::HTTP_BAD_REQUEST);
        }

        $admin->update($object);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        // NEXT_MAJOR: Modify lines below to use RenderElementExtension instead of SonataAdminExtension
        $extension = $this->twig->getExtension(SonataAdminExtension::class);
        \assert($extension instanceof SonataAdminExtension);

        // NEXT_MAJOR: Remove the last two arguments
        $content = $extension->renderListElement($this->twig, $rootObject, $fieldDescription, [], 'sonata_deprecation_mute');

        return new JsonResponse($content, Response::HTTP_OK);
    }
}
