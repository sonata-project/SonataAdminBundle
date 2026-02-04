<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Action;

use SensioLabs\AdminBundle\Admin\AdminHelper;
use SensioLabs\AdminBundle\BCLayer\BCHelper;
use SensioLabs\AdminBundle\Exception\BadRequestParamHttpException;
use SensioLabs\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class AppendFormFieldElementAction
{
    public function __construct(
        private Environment $twig,
        private AdminFetcherInterface $adminFetcher,
        private AdminHelper $helper,
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

        $objectId = BCHelper::getFromRequest($request, 'objectId');
        if (null === $objectId) {
            $subject = $admin->getNewInstance();
        } elseif (\is_string($objectId) || \is_int($objectId)) {
            $subject = $admin->getObject($objectId);
            if (null === $subject) {
                throw new NotFoundHttpException(\sprintf(
                    'Unable to find the object id: %s, class: %s',
                    $objectId,
                    $admin->getClass()
                ));
            }
        } else {
            throw new BadRequestParamHttpException('objectId', ['string', 'int', 'null'], $objectId);
        }

        $admin->setSubject($subject);

        $elementId = BCHelper::getFromRequest($request, 'elementId');
        if (!\is_string($elementId)) {
            throw new BadRequestParamHttpException('elementId', 'string', $elementId);
        }

        [, $form] = $this->helper->appendFormFieldElement($admin, $subject, $elementId);

        $view = $this->helper->getChildFormView($form->createView(), $elementId);
        \assert(null !== $view);

        // render the widget
        $renderer = $this->twig->getRuntime(FormRenderer::class);
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }
}
