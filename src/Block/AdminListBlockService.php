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
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminListBlockService extends AbstractBlockService
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @param string $name
     */
    public function __construct(
        $name,
        EngineInterface $templating,
        Pool $pool,
        TemplateRegistryInterface $templateRegistry = null
    ) {
        parent::__construct($name, $templating);

        $this->pool = $pool;
        $this->templateRegistry = $templateRegistry ?: new TemplateRegistry();
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $dashboardGroups = $this->pool->getDashboardGroups();

        $settings = $blockContext->getSettings();

        $visibleGroups = [];
        foreach ($dashboardGroups as $name => $dashboardGroup) {
            if (!$settings['groups'] || \in_array($name, $settings['groups'])) {
                $visibleGroups[] = $dashboardGroup;
            }
        }

        return $this->renderPrivateResponse($this->templateRegistry->getTemplate('list_block'), [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'admin_pool' => $this->pool,
            'groups' => $visibleGroups,
        ], $response);
    }

    public function getName()
    {
        return 'Admin List';
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'groups' => false,
        ]);

        $resolver->setAllowedTypes('groups', ['bool', 'array']);
    }
}
