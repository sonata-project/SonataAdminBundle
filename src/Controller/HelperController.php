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

namespace Sonata\AdminBundle\Controller;

use Sonata\AdminBundle\Action\AppendFormFieldElementAction;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Action\RetrieveAutocompleteItemsAction;
use Sonata\AdminBundle\Action\RetrieveFormFieldElementAction;
use Sonata\AdminBundle\Action\SetObjectFieldValueAction;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

@trigger_error(
    'The '.__NAMESPACE__.'\HelperController class is deprecated since version 3.38.0 and will be removed in 4.0.'
    .' Use actions inside Sonata\AdminBundle\Action instead.',
    E_USER_DEPRECATED
);

/**
 * NEXT_MAJOR: remove this class.
 *
 * @deprecated since version 3.38.0, to be removed in 4.0. Use actions inside Sonata\AdminBundle\Action instead.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class HelperController
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var AdminHelper
     */
    protected $helper;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(Environment $twig, Pool $pool, AdminHelper $helper, $validator)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!($validator instanceof ValidatorInterface)) {
            throw new \InvalidArgumentException(
                'Argument 4 is an instance of '.\get_class($validator).', expecting an instance of'
                .' \Symfony\Component\Validator\Validator\ValidatorInterface'
            );
        }

        $this->twig = $twig;
        $this->pool = $pool;
        $this->helper = $helper;
        $this->validator = $validator;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function appendFormFieldElementAction(Request $request): Response
    {
        $action = new AppendFormFieldElementAction($this->twig, $this->pool, $this->helper);

        return $action($request);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function retrieveFormFieldElementAction(Request $request): Response
    {
        $action = new RetrieveFormFieldElementAction($this->twig, $this->pool, $this->helper);

        return $action($request);
    }

    /**
     * @throws NotFoundHttpException|\RuntimeException
     */
    public function getShortObjectDescriptionAction(Request $request): Response
    {
        $action = new GetShortObjectDescriptionAction($this->twig, $this->pool);

        return $action($request);
    }

    public function setObjectFieldValueAction(Request $request): Response
    {
        $action = new SetObjectFieldValueAction($this->twig, $this->pool, $this->validator);

        return $action($request);
    }

    /**
     * Retrieve list of items for autocomplete form field.
     *
     * @throws \RuntimeException
     * @throws AccessDeniedException
     */
    public function retrieveAutocompleteItemsAction(Request $request): JsonResponse
    {
        $action = new RetrieveAutocompleteItemsAction($this->pool);

        return $action($request);
    }
}
