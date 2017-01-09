<?php

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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminStatsBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @param string          $name
     * @param EngineInterface $templating
     * @param Pool            $pool
     */
    public function __construct($name, EngineInterface $templating, Pool $pool)
    {
        parent::__construct($name, $templating);

        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));

        $datagrid = $admin->getDatagrid();

        $filters = $blockContext->getSetting('filters');

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = array('value' => $blockContext->getSetting('limit'));
        }

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, isset($data['type']) ? $data['type'] : null, $data['value']);
        }

        $datagrid->buildPager();

        return $this->renderPrivateResponse($blockContext->getTemplate(), array(
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin_pool' => $this->pool,
            'admin' => $admin,
            'pager' => $datagrid->getPager(),
            'datagrid' => $datagrid,
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Admin Stats';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'icon' => 'fa-line-chart',
            'text' => 'Statistics',
            'color' => 'bg-aqua',
            'code' => false,
            'filters' => array(),
            'limit' => 1000,
            'template' => 'SonataAdminBundle:Block:block_stats.html.twig',
        ));
    }
}
