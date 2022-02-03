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

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class RetrieveFormFieldElementAction
{
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var AdminHelper
     */
    private $helper;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, AdminFetcherInterface $adminFetcher, AdminHelper $helper)
    {
        $this->helper = $helper;
        $this->twig = $twig;
        $this->adminFetcher = $adminFetcher;
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
        if (null === $objectId) {
            $subject = $admin->getNewInstance();
        } elseif (\is_string($objectId) || \is_int($objectId)) {
            $subject = $admin->getObject($objectId);
            if (null === $subject) {
                throw new NotFoundHttpException(sprintf(
                    'Unable to find the object id: %s, class: %s',
                    $objectId,
                    $admin->getClass()
                ));
            }
        } else {
            throw new BadRequestParamHttpException('objectId', ['string', 'int', 'null'], $objectId);
        }

        $admin->setSubject($subject);

        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($request);

        $elementId = $request->get('elementId');
        if (!\is_string($elementId)) {
            throw new BadRequestParamHttpException('elementId', 'string', $elementId);
        }

        $view = $this->helper->getChildFormView($form->createView(), $elementId);
        \assert(null !== $view);

        // render the widget
        $renderer = $this->twig->getRuntime(FormRenderer::class);
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }
}
