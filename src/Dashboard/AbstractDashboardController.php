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

namespace SensioLabs\AdminBundle\Dashboard;

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

/**
 * Base class for dashboard controllers.
 *
 * Extend this class and implement configureMenuItems() to create your custom dashboard.
 * Dependencies are injected automatically via setter injection - no constructor needed.
 *
 * Example:
 *
 *     class DashboardController extends AbstractDashboardController
 *     {
 *         public function configureMenuItems(): iterable
 *         {
 *             yield MenuItem::linkToDashboard('Dashboard', 'lucide:home');
 *             yield MenuItem::linkToCrud('Users', 'lucide:users', 'app.admin.user');
 *         }
 *     }
 */
abstract class AbstractDashboardController implements DashboardControllerInterface
{
    private Pool $pool;
    private TemplateRegistryInterface $templateRegistry;
    private Environment $twig;

    #[Required]
    public function setPool(Pool $pool): void
    {
        $this->pool = $pool;
    }

    #[Required]
    public function setTemplateRegistry(TemplateRegistryInterface $templateRegistry): void
    {
        $this->templateRegistry = $templateRegistry;
    }

    #[Required]
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function index(Request $request): Response
    {
        $content = $this->twig->render($this->templateRegistry->getTemplate('dashboard'), [
            'base_template' => $request->isXmlHttpRequest()
                ? $this->templateRegistry->getTemplate('ajax')
                : $this->templateRegistry->getTemplate('layout'),
            'menu_items' => $this->getMenuItems(),
            'dashboard_content' => $this->configureDashboard(),
        ]);

        return new Response($content);
    }

    /**
     * Override this method to configure the menu items shown in the sidebar.
     *
     * @return iterable<MenuItem>
     */
    abstract public function configureMenuItems(): iterable;

    /**
     * Override this method to configure the dashboard content.
     *
     * @return array<string, mixed>
     */
    public function configureDashboard(): array
    {
        return [
            'title' => 'Dashboard',
        ];
    }

    /**
     * @return MenuItem[]
     */
    public function getMenuItems(): array
    {
        $items = [];
        foreach ($this->configureMenuItems() as $item) {
            $items[] = $item;
        }

        return $items;
    }

    protected function getPool(): Pool
    {
        return $this->pool;
    }

    protected function getTemplateRegistry(): TemplateRegistryInterface
    {
        return $this->templateRegistry;
    }

    protected function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Helper to generate URL for an admin's action.
     */
    protected function generateAdminUrl(string $adminCode, string $action = 'list', array $parameters = []): string
    {
        $admin = $this->pool->getInstance($adminCode);

        return $admin->generateUrl($action, $parameters);
    }
}
