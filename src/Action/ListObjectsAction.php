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

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Twig\AdminEnvironment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ListObjectsAction
{
    /**
     * @var AdminEnvironment
     */
    private $environment;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var CsrfTokenManagerInterface|null
     */
    private $tokenManager;

    /**
     * @var AdminExporter|null
     */
    private $exporter;

    public function __construct(
        AdminEnvironment $environment,
        Pool $pool,
        CsrfTokenManagerInterface $tokenManager = null,
        AdminExporter $exporter = null
    ) {
        $this->environment = $environment;
        $this->pool = $pool;
        $this->tokenManager = $tokenManager;
        $this->exporter = $exporter;
    }

    public function __invoke(Request $request): Response
    {
        $admin = $this->environment->getAdmin();

        $admin->checkAccess('list');

        if ($listMode = $request->get('_list_mode')) {
            $admin->setListMode($listMode);
        }

        $datagrid = $admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->environment->setFormTheme($formView, $admin->getFilterTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('list');
        // $template = $this->templateRegistry->getTemplate('list');

        return $this->environment->render($template, [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $this->exporter ?
                $this->exporter->getAvailableFormats($admin) :
                $admin->getExportFormats(),
        ]);
    }

    /**
     * Get CSRF token.
     *
     * @param string $intention
     *
     * @return string|false
     */
    private function getCsrfToken(string $intention)
    {
        if ($this->tokenManager) {
            return $this->tokenManager->getToken($intention)->getValue();
        }

        return false;
    }
}
