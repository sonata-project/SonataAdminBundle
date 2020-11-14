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
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class RetrieveFormFieldElementAction
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminHelper
     */
    private $helper;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, Pool $pool, AdminHelper $helper)
    {
        $this->pool = $pool;
        $this->helper = $helper;
        $this->twig = $twig;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        $code = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

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
