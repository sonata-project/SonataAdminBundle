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
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function __construct(Environment $twig, Pool $pool, $validator)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!($validator instanceof ValidatorInterface)) {
            throw new \InvalidArgumentException(
                'Argument 3 is an instance of '.\get_class($validator).', expecting an instance of'
                .' \Symfony\Component\Validator\Validator\ValidatorInterface'
            );
        }
        $this->pool = $pool;
        $this->twig = $twig;
        $this->validator = $validator;
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
            return new JsonResponse(sprintf('Invalid request method given "%s", %s expected', $request->getMethod(), Request::METHOD_POST), Response::HTTP_METHOD_NOT_ALLOWED);
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
            $object = $this->pool->getPropertyAccessor()->getValue($object, $propertyPath->getParent());

            $elements = $propertyPath->getElements();
            $field = end($elements);
            $propertyPath = new PropertyPath($field);
        }

        // Handle date type has setter expect a DateTime object
        if ('' !== $value && 'date' === $fieldDescription->getType()) {
            $value = new \DateTime($value);
        }

        // Handle boolean type transforming the value into a boolean
        if ('' !== $value && 'boolean' === $fieldDescription->getType()) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // Handle entity choice association type, transforming the value into entity
        if ('' !== $value
            && 'choice' === $fieldDescription->getType()
            && null !== $fieldDescription->getOption('class')
            && $fieldDescription->getOption('class') === $fieldDescription->getTargetEntity()
        ) {
            $value = $admin->getModelManager()->find($fieldDescription->getOption('class'), $value);

            if (!$value) {
                return new JsonResponse(sprintf(
                    'Edit failed, object with id: %s not found in association: %s.',
                    $originalValue,
                    $field
                ), Response::HTTP_NOT_FOUND);
            }
        }

        $this->pool->getPropertyAccessor()->setValue($object, $propertyPath, '' !== $value ? $value : null);

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
        $extension = $this->twig->getExtension(SonataAdminExtension::class);

        $content = $extension->renderListElement($this->twig, $rootObject, $fieldDescription);

        return new JsonResponse($content, Response::HTTP_OK);
    }
}
