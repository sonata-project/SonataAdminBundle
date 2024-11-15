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
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Search\SearchableFilterInterface;
use Sonata\AdminBundle\Search\SearchHandlerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AdminSearchBlockService extends AbstractBlockService
{
    /**
     * @phpstan-param 'show'|'hide'|'fade' $emptyBoxesOption
     * @phpstan-param 'show'|'edit'        $adminRoute
     */
    public function __construct(
        Environment $twig,
        private Pool $pool,
        private SearchHandlerInterface $searchHandler,
        private TemplateRegistryInterface $templateRegistry,
        private string $emptyBoxesOption,
        private string $adminRoute,
    ) {
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        try {
            $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('admin_code'));
        } catch (ServiceNotFoundException $exception) {
            throw new \RuntimeException(
                'Unable to find the Admin instance',
                $exception->getCode(),
                $exception
            );
        }

        $admin->checkAccess('list');

        $term = $blockContext->getSetting('query');

        $pager = $this->searchHandler->search(
            $admin,
            $term,
            $blockContext->getSetting('page'),
            $blockContext->getSetting('per_page')
        );

        if (null === $pager) {
            $response ??= new Response();

            return $response->setContent('')->setStatusCode(204);
        }

        $filters = array_filter(
            $admin->getDatagrid()->getFilters(),
            static fn (FilterInterface $filter): bool => $filter instanceof SearchableFilterInterface && $filter->isSearchEnabled()
        );

        return $this->renderResponse($this->templateRegistry->getTemplate('search_result_block'), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'pager' => $pager,
            'term' => $term,
            'filters' => $filters,
            'admin' => $admin,
            'show_empty_boxes' => $this->emptyBoxesOption,
            'admin_route' => $this->adminRoute,
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'admin_code' => '',
                'query' => '',
                'page' => 0,
                'per_page' => 10,
                'icon' => 'fas fa-list',
            ])
            ->setRequired('admin_code')
            ->setAllowedTypes('admin_code', ['string']);
    }
}
