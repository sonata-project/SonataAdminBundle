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
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Form\DataTransformerResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

@trigger_error(sprintf(
    'The %s\HelperController class is deprecated since version 3.38.0 and will be removed in 4.0.'
    .' Use actions inside Sonata\AdminBundle\Action instead.',
    __NAMESPACE__
), E_USER_DEPRECATED);

/**
 * NEXT_MAJOR: remove this class.
 *
 * @deprecated since sonata-project/admin-bundle 3.38.0, to be removed in 4.0. Use actions inside Sonata\AdminBundle\Action instead.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class HelperController
{
    /**
     * @var Environment
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
     * @var DataTransformerResolver
     */
    private $resolver;

    /**
     * @param ValidatorInterface           $validator
     * @param DataTransformerResolver|null $resolver
     */
    public function __construct(Environment $twig, Pool $pool, AdminHelper $helper, $validator, $resolver = null)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!($validator instanceof ValidatorInterface)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 4 is an instance of %s, expecting an instance of %s',
                \get_class($validator),
                ValidatorInterface::class
            ));
        }

        // NEXT_MAJOR: Move DataTransformerResolver check to method signature
        if (!$resolver instanceof DataTransformerResolverInterface) {
            @trigger_error(sprintf(
                'Passing other type than %s in argument 4 to %s() is deprecated since sonata-project/admin-bundle 3.x and will throw %s exception in 4.0.',
                DataTransformerResolverInterface::class,
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);
            $resolver = new DataTransformerResolver();
        }

        $this->twig = $twig;
        $this->pool = $pool;
        $this->helper = $helper;
        $this->validator = $validator;
        $this->resolver = $resolver;
    }

    /**
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function appendFormFieldElementAction(Request $request)
    {
        $action = new AppendFormFieldElementAction($this->twig, $this->pool, $this->helper);

        return $action($request);
    }

    /**
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function retrieveFormFieldElementAction(Request $request)
    {
        $action = new RetrieveFormFieldElementAction($this->twig, $this->pool, $this->helper);

        return $action($request);
    }

    /**
     * @throws NotFoundHttpException|\RuntimeException
     *
     * @return Response
     */
    public function getShortObjectDescriptionAction(Request $request)
    {
        $action = new GetShortObjectDescriptionAction($this->twig, $this->pool);

        return $action($request);
    }

    /**
     * @return Response
     */
    public function setObjectFieldValueAction(Request $request)
    {
        $action = new SetObjectFieldValueAction($this->twig, $this->pool, $this->validator, $this->resolver);

        return $action($request);
    }

    /**
     * Retrieve list of items for autocomplete form field.
     *
     * @throws \RuntimeException
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function retrieveAutocompleteItemsAction(Request $request)
    {
        $action = new RetrieveAutocompleteItemsAction($this->pool);

        return $action($request);
    }
}
