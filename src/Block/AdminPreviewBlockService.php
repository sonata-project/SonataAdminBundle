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
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class AdminPreviewBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct(Environment $twig, Pool $pool)
    {
        parent::__construct($twig);

        $this->pool = $pool;
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $template = $blockContext->getTemplate();
        \assert(\is_string($template));

        $admin = $this->getAdmin($blockContext->getSetting('code'));
        $this->handleFilters($admin, $blockContext);

        foreach ($blockContext->getSetting('remove_list_fields') as $listField) {
            $admin->getList()->remove($listField);
        }

        $datagrid = $admin->getDatagrid();

        return $this->renderPrivateResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin' => $admin,
            'datagrid' => $datagrid,
        ], $response);
    }

    public function getName(): string
    {
        return 'Admin preview';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'text' => 'Preview',
            'filters' => [],
            'icon' => false,
            'limit' => 10,
            'code' => false,
            'template' => '@SonataAdmin/Block/block_admin_preview.html.twig',
            'remove_list_fields' => [ListMapper::NAME_ACTIONS],
        ]);
    }

    /**
     * @throws \InvalidArgumentException if the provided admin code is invalid
     *
     * @return AdminInterface<object>
     */
    private function getAdmin(string $code): AdminInterface
    {
        $admin = $this->pool->getAdminByAdminCode($code);

        $admin->checkAccess('list');

        return $admin;
    }

    /**
     * Maps the block filters to standard admin filters.
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    private function handleFilters(AdminInterface $admin, BlockContextInterface $blockContext): void
    {
        $filters = $blockContext->getSetting('filters');

        if ($sortBy = $filters[DatagridInterface::SORT_BY] ?? null) {
            $sortFilters = [DatagridInterface::SORT_BY => $sortBy];
            if ($sortOrder = $filters[DatagridInterface::SORT_ORDER] ?? null) {
                $sortFilters[DatagridInterface::SORT_ORDER] = $sortOrder;
                unset($filters[DatagridInterface::SORT_ORDER]);
            }
            // Setting a request to the admin is a workaround since the admin only
            // can handle the "DatagridInterface::SORT_BY" parameter from the query string.
            $admin->setRequest(new Request(['filter' => $sortFilters]));
            unset($filters[DatagridInterface::SORT_BY]);
        }

        if (!isset($filters[DatagridInterface::PER_PAGE])) {
            $filters[DatagridInterface::PER_PAGE] = ['value' => $blockContext->getSetting('limit')];
        }

        $datagrid = $admin->getDatagrid();

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $datagrid->buildPager();
    }
}
