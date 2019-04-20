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

use Sonata\AdminBundle\Admin\Pool;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class AdminPreviewBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @param string $name
     */
    public function __construct($name, EngineInterface $templating, Pool $pool)
    {
        parent::__construct($name, $templating);

        $this->pool = $pool;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));

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

        foreach ($blockContext->getSetting('remove_list_fields') as $listField) {
            $admin->getList()->remove($listField);
        }

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
}
