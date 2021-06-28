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
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AdminStatsBlockService extends AbstractBlockService
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
        \assert(null !== $template);

        $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));

        $datagrid = $admin->getDatagrid();

        $filters = $blockContext->getSetting('filters');

        if (!isset($filters[DatagridInterface::PER_PAGE])) {
            $filters[DatagridInterface::PER_PAGE] = ['value' => $blockContext->getSetting('limit')];
        }

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $datagrid->buildPager();

        return $this->renderPrivateResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin' => $admin,
            'pager' => $datagrid->getPager(),
            'datagrid' => $datagrid,
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon' => 'fas fa-chart-line',
            'text' => 'Statistics',
            'translation_domain' => null,
            'color' => 'bg-aqua',
            'code' => false,
            'filters' => [],
            'limit' => 1000,
            'template' => '@SonataAdmin/Block/block_stats.html.twig',
        ]);
    }
}
