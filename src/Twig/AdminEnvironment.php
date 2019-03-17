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

namespace Sonata\AdminBundle\Twig;

use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

final class AdminEnvironment
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbBuilder;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EngineInterface|null
     */
    private $templating;

    public function __construct(Pool $pool, TemplateRegistryInterface $templateRegistry, BreadcrumbsBuilderInterface $breadcrumbBuilder, RequestStack $requestStack, Environment $twig, EngineInterface $templating = null)
    {
        $this->pool = $pool;
        $this->templateRegistry = $templateRegistry;
        $this->breadcrumbBuilder = $breadcrumbBuilder;
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        // NEXT_MAJOR: Remove templating
        $this->templating = $templating;
    }

    /**
     * Renders a view while passing mandatory parameters on to the template.
     *
     * @param string $view The view name
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = [], Response $response = null): Response
    {
        $admin = $this->getAdmin();

        if (!$this->isXmlHttpRequest()) {
            $parameters['breadcrumbs_builder'] = $this->breadcrumbBuilder;
        }

        $parameters['admin'] = $parameters['admin'] ?? $admin;

        if (!isset($parameters['base_template'])) {
            $parameters['base_template'] = $this->getBaseTemplate();
        }

        $parameters['admin_pool'] = $this->pool;

        if (null !== $this->templating) {
            $content = $this->templating->render($view, $parameters);
        } else {
            $content = $this->twig->render($view, $parameters);
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     */
    public function setFormTheme(FormView $formView, array $theme = null): void
    {
        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $this->twig->getExtension(FormExtension::class)->renderer->setTheme($formView, $theme);

            return;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $this->twig->getRuntime(TwigRenderer::class)->setTheme($formView, $theme);

            return;
        }

        $this->twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin(): AdminInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new InvalidArgumentException('No request found to detect parent admin');
        }

        $adminCode = $request->get('code');

        if (!$adminCode) {
            throw new \RuntimeException(sprintf(
                'There is no `_sonata_admin` defined for the controller `%s` and the current route `%s`',
                \get_class($this),
                $request->get('_route')
            ));
        }

        $admin = $this->pool->getInstance($adminCode);

        if (!$admin) {
            throw new \RuntimeException(sprintf(
                'Unable to find the admin class related to the current controller (%s)',
                \get_class($this)
            ));
        }

        $admin->setRequest($request);

        return $admin;
    }

    /**
     * Returns the base template name.
     *
     * @return string The template name
     */
    private function getBaseTemplate(): string
    {
        if ($this->isXmlHttpRequest()) {
            // NEXT_MAJOR: Remove this line and use commented line below it instead
            return $this->admin->getTemplate('ajax');
            // return $this->templateRegistry->getTemplate('ajax');
        }

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        return $this->admin->getTemplate('layout');
        // return $this->templateRegistry->getTemplate('layout');
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * @return bool True if the request is an XMLHttpRequest, false otherwise
     */
    private function isXmlHttpRequest(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return null !== $request && ($request->isXmlHttpRequest() || $request->get('_xml_http_request'));
    }
}
