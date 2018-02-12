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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminSearchBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var SearchHandler
     */
    protected $searchHandler;

    /**
     * @param string $name
     */
    public function __construct($name, EngineInterface $templating, Pool $pool, SearchHandler $searchHandler)
    {
        parent::__construct($name, $templating);

        $this->pool = $pool;
        $this->searchHandler = $searchHandler;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        try {
            $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('admin_code'));
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
        }

        if (!$admin instanceof AdminInterface) {
            throw new \RuntimeException('The requested service is not an Admin instance');
        }

        $admin->checkAccess('list');

        $pager = $this->searchHandler->search(
            $admin,
            $blockContext->getSetting('query'),
            $blockContext->getSetting('page'),
            $blockContext->getSetting('per_page')
        );

        if (false === $pager) {
            $response = $response ?: new Response();

            return $response->setContent('')->setStatusCode(204);
        }

        return $this->renderPrivateResponse($admin->getTemplate('search_result_block'), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin_pool' => $this->pool,
            'pager' => $pager,
            'admin' => $admin,
        ], $response);
    }

    public function getName()
    {
        return 'Admin Search Result';
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'admin_code' => false,
            'query' => '',
            'page' => 0,
            'per_page' => 10,
            'icon' => '<i class="fa fa-list"></i>',
        ]);
    }
}
