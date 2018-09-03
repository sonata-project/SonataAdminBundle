<?php

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
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class AppendFormFieldElementAction
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
     *
     * @return Response
     */
    public function __invoke(Request $request)
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

        $subject = $admin->getObject($objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException(sprintf(
                'Could not find subject for id "%s"',
                $objectId
            ));
        }

        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        list(, $form) = $this->helper->appendFormFieldElement($admin, $subject, $elementId);

        \assert($form instanceof Form);
        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        $renderer = $this->getFormRenderer();
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }

    /**
     * @return FormRenderer|TwigRenderer
     */
    private function getFormRenderer()
    {
        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $extension = $this->twig->getExtension(FormExtension::class);
            $extension->initRuntime($this->twig);

            return $extension->renderer;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $runtime = $this->twig->getRuntime(TwigRenderer::class);
            $runtime->setEnvironment($this->twig);

            return $runtime;
        }

        return $this->twig->getRuntime(FormRenderer::class);
    }
}
