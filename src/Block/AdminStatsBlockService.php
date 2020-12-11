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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminStatsBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * NEXT_MAJOR: Change signature for (Environment $twig, Pool $pool).
     *
     * @param Environment|string        $twigOrName
     * @param Pool|EngineInterface|null $poolOrTemplating
     */
    public function __construct($twigOrName, ?object $poolOrTemplating, ?Pool $pool = null)
    {
        if ($poolOrTemplating instanceof Pool) {
            if (!$twigOrName instanceof Environment) {
                throw new \TypeError(sprintf(
                    'Argument 1 passed to %s() must be an instance of %s, %s given.',
                    __METHOD__,
                    Environment::class,
                    \is_object($twigOrName) ? 'instance of '.\get_class($twigOrName) : \gettype($twigOrName)
                ));
            }

            parent::__construct($twigOrName);

            $this->pool = $poolOrTemplating;
        } elseif (null === $poolOrTemplating || $poolOrTemplating instanceof EngineInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 2 to %s() is deprecated since sonata-project/admin-bundle 3.76'
                .' and will throw a \TypeError in version 4.0. You must pass an instance of %s instead.',
                null === $poolOrTemplating ? 'null' : EngineInterface::class,
                __METHOD__,
                Pool::class
            ), E_USER_DEPRECATED);

            if (null === $pool) {
                throw new \TypeError(sprintf(
                    'Passing null as argument 3 to %s() is not allowed when %s is passed as argument 2.'
                    .' You must pass an instance of %s instead.',
                    __METHOD__,
                    EngineInterface::class,
                    Pool::class
                ));
            }

            parent::__construct($twigOrName, $poolOrTemplating);

            $this->pool = $pool;
        } else {
            throw new \TypeError(sprintf(
                'Argument 2 passed to %s() must be either null or an instance of %s or preferably %s, instance of %s given.',
                __METHOD__,
                EngineInterface::class,
                Pool::class,
                \get_class($poolOrTemplating)
            ));
        }
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));

        $datagrid = $admin->getDatagrid();

        $filters = $blockContext->getSetting('filters');

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = ['value' => $blockContext->getSetting('limit')];
        }

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $datagrid->buildPager();

        return $this->renderPrivateResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            // NEXT_MAJOR: Remove next line.
            'admin_pool' => $this->pool,
            'admin' => $admin,
            'pager' => $datagrid->getPager(),
            'datagrid' => $datagrid,
        ], $response);
    }

    public function getName()
    {
        return 'Admin Stats';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon' => 'fa-line-chart',
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
