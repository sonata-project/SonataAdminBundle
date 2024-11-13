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
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AdminListBlockService extends AbstractBlockService
{
    public function __construct(
        Environment $twig,
        private Pool $pool,
        private TemplateRegistryInterface $templateRegistry,
    ) {
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $dashboardGroups = $this->pool->getDashboardGroups();

        $settings = $blockContext->getSettings();

        $visibleGroups = [];
        foreach ($dashboardGroups as $name => $dashboardGroup) {
            if (false === $settings['groups'] || \in_array($name, $settings['groups'], true)) {
                $visibleGroups[] = $dashboardGroup;
            }
        }

        return $this->renderResponse($this->templateRegistry->getTemplate('list_block'), [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'groups' => $visibleGroups,
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'groups' => false,
        ]);

        $resolver->setAllowedTypes('groups', ['bool', 'array']);
    }
}
