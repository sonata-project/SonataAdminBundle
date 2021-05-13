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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Request\AdminFetcher;
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

    /**
     * NEXT_MAJOR: Restrict second param to AdminFetcherInterface.
     *
     * @param Pool|AdminFetcherInterface $poolOrAdminFetcher
     */
    public function __construct(Environment $twig, $poolOrAdminFetcher, AdminHelper $helper)
    {
        $this->helper = $helper;
        $this->twig = $twig;

        if ($poolOrAdminFetcher instanceof AdminFetcherInterface) {
            $this->adminFetcher = $poolOrAdminFetcher;
        } elseif ($poolOrAdminFetcher instanceof Pool) {
            @trigger_error(sprintf(
                'Passing other type than %s in argument 2 to %s() is deprecated since sonata-project/admin-bundle 3.x'
                .' and will throw %s exception in 4.0.',
                AdminFetcherInterface::class,
                __METHOD__,
                \TypeError::class
            ), \E_USER_DEPRECATED);

            $this->adminFetcher = new AdminFetcher($poolOrAdminFetcher);
        } else {
            throw new \TypeError(sprintf(
                'Argument 2 passed to "%s()" must be either an instance of %s or %s, %s given.',
                __METHOD__,
                Pool::class,
                AdminFetcherInterface::class,
                \is_object($poolOrAdminFetcher) ? 'instance of "'.\get_class($poolOrAdminFetcher).'"' : '"'.\gettype($poolOrAdminFetcher).'"'
            ));
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        // NEXT_MAJOR: Remove this BC-layer.
        if (null === $request->get('_sonata_admin')) {
            @trigger_error(
                'Not passing "_sonata_admin" value in the request is deprecated since sonata-project/admin-bundle 3.x'
                .' and will throw %s exception in 4.0.'
                , \E_USER_DEPRECATED);

            $request->request->set('_sonata_admin', $request->get('code'));
        }

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf(
                'Could not find admin for code "%s"',
                $request->get('_sonata_admin')
            ));
        }

        $objectId = $request->get('objectId');
        if ($objectId) {
            $subject = $admin->getObject($objectId);
            if (!$subject) {
                throw new NotFoundHttpException(sprintf(
                    'Unable to find the object id: %s, class: %s',
                    $objectId,
                    $admin->getClass()
                ));
            }
        } else {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($request);

        $elementId = $request->get('elementId');
        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        $renderer = $this->getFormRenderer();
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }

    private function getFormRenderer(): FormRenderer
    {
        return $this->twig->getRuntime(FormRenderer::class);
    }
}
