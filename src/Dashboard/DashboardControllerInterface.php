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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for dashboard controllers.
 *
 * Implement this interface in your custom dashboard controller to customize
 * the admin dashboard menu and content.
 */
interface DashboardControllerInterface
{
    /**
     * Handles the dashboard index request.
     */
    public function index(Request $request): Response;

    /**
     * Configure the menu items shown in the sidebar.
     *
     * @return iterable<MenuItem>
     */
    public function configureMenuItems(): iterable;

    /**
     * Configure the dashboard content.
     *
     * @return array<string, mixed>
     */
    public function configureDashboard(): array;

    /**
     * Get all menu items.
     *
     * @return MenuItem[]
     */
    public function getMenuItems(): array;
}
