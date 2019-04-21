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

namespace Sonata\AdminBundle\Block;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class AdminPreviewBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct(string $name, EngineInterface $templating, Pool $pool)
    {
        parent::__construct($name, $templating);

        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $admin = $this->getAdmin($blockContext->getSetting('code'));
        $this->handleFilters($admin, $blockContext);

        foreach ($blockContext->getSetting('remove_list_fields') as $listField) {
            $admin->getList()->remove($listField);
        }

        $datagrid = $admin->getDatagrid();

        return $this->renderPrivateResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin_pool' => $this->pool,
            'admin' => $admin,
            'pager' => $datagrid->getPager(),
            'datagrid' => $datagrid,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Admin preview';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'text' => 'Preview',
            'filters' => [],
            'limit' => 10,
            'code' => false,
            'template' => '@SonataAdmin/Block/block_admin_preview.html.twig',
            'remove_list_fields' => ['_action'],
        ]);
    }

    /**
     * @throws \RuntimeException if the provided admin code is invalid
     */
    private function getAdmin(string $code): AdminInterface
    {
        try {
            $admin = $this->pool->getAdminByAdminCode($code);
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
        }

        if (!$admin instanceof AdminInterface) {
            throw new \RuntimeException('The requested service is not an Admin instance');
        }

        $admin->checkAccess('list');

        return $admin;
    }

    /**
     * Maps the block filters to standard admin filters.
     */
    private function handleFilters(AdminInterface $admin, BlockContextInterface $blockContext): void
    {
        $filters = $blockContext->getSetting('filters');

        if ($sortBy = $filters['_sort_by'] ?? null) {
            $sortFilters = ['_sort_by' => $sortBy];
            if ($sortOrder = $filters['_sort_order'] ?? null) {
                $sortFilters['_sort_order'] = $sortOrder;
                unset($filters['_sort_order']);
            }
            // setting a request to the admin is a workaround since the admin only can handle the "_sort_by" parameter from the query string
            $request = new Request(['filter' => $sortFilters]);
            $request->setSession(new Session());
            $admin->setRequest($request);
            unset($filters['_sort_by'], $request);
        }

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = ['value' => $blockContext->getSetting('limit')];
        }

        $datagrid = $admin->getDatagrid();

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $datagrid->buildPager();
    }
}
