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
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
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
     * NEXT_MAJOR: Change var to string and phpstan-var to 'show'|'hide'|'fade'.
     *
     * @var string|null
     *
     * @phpstan-var 'show'|'hide'|'fade'|null
     */
    private $emptyBoxesOption;

    /**
     * NEXT_MAJOR: Change signature for (Environment $twig, Pool $pool, SearchHandler $searchHandler, string $emptyBoxesOption).
     *
     * @param Environment|string        $twigOrName
     * @param Pool|EngineInterface|null $poolOrTemplating
     * @param SearchHandler|Pool        $searchHandlerOrPool
     * @param string|SearchHandler|null $emptyBoxesOptionOrSearchHandler
     *
     * @phpstan-param 'show'|'hide'|'fade'|SearchHandler|null $emptyBoxesOptionOrSearchHandler
     * @phpstan-param 'show'|'hide'|'fade'|null               $emptyBoxesOption
     */
    public function __construct($twigOrName, ?object $poolOrTemplating, object $searchHandlerOrPool, $emptyBoxesOptionOrSearchHandler = null, ?string $emptyBoxesOption = null)
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

            if (!$searchHandlerOrPool instanceof SearchHandler) {
                throw new \TypeError(sprintf(
                    'Argument 3 passed to %s() must be an instance of %s, instance of %s given.',
                    __METHOD__,
                    SearchHandler::class,
                    \get_class($twigOrName)
                ));
            }

            parent::__construct($twigOrName);

            if (!\is_string($emptyBoxesOptionOrSearchHandler)) {
                @trigger_error(sprintf(
                    'Not passing a string as argument 4 to %s() is deprecated since sonata-project/admin-bundle 3.81'
                    .' and will throw a \TypeError in version 4.0.',
                    __METHOD__
                ), \E_USER_DEPRECATED);
            }

            $this->pool = $poolOrTemplating;
            $this->searchHandler = $searchHandlerOrPool;
            $this->emptyBoxesOption = $emptyBoxesOptionOrSearchHandler;
        } elseif (null === $poolOrTemplating || $poolOrTemplating instanceof EngineInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 2 to %s() is deprecated since sonata-project/admin-bundle 3.76'
                .' and will throw a \TypeError in version 4.0. You must pass an instance of %s instead.',
                null === $poolOrTemplating ? 'null' : EngineInterface::class,
                __METHOD__,
                Pool::class
            ), \E_USER_DEPRECATED);

            if (!$searchHandlerOrPool instanceof Pool) {
                throw new \TypeError(sprintf(
                    'Argument 2 passed to %s() must be an instance of %s, instance of %s given.',
                    __METHOD__,
                    Pool::class,
                    \get_class($twigOrName)
                ));
            }

            if (null === $emptyBoxesOptionOrSearchHandler) {
                throw new \TypeError(sprintf(
                    'Passing null as argument 3 to %s() is not allowed when %s is passed as argument 2.'
                    .' You must pass an instance of %s instead.',
                    __METHOD__,
                    EngineInterface::class,
                    SearchHandler::class
                ));
            }

            parent::__construct($twigOrName, $poolOrTemplating);

            $this->pool = $searchHandlerOrPool;
            $this->searchHandler = $emptyBoxesOptionOrSearchHandler;
            $this->emptyBoxesOption = $emptyBoxesOption;
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
        try {
            $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('admin_code'));
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
        }

        if (!$admin instanceof AdminInterface) {
            throw new \RuntimeException('The requested service is not an Admin instance');
        }

        $admin->checkAccess('list');

        $term = $blockContext->getSetting('query');

        $pager = $this->searchHandler->search(
            $admin,
            $term,
            $blockContext->getSetting('page'),
            $blockContext->getSetting('per_page')
        );

        if (false === $pager) {
            $response = $response ?: new Response();

            return $response->setContent('')->setStatusCode(204);
        }

        $filters = array_filter($admin->getDatagrid()->getFilters(), static function (FilterInterface $filter): bool {
            return $filter->isActive();
        });

        return $this->renderPrivateResponse($admin->getTemplate('search_result_block'), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            // NEXT_MAJOR: Remove next line.
            'admin_pool' => $this->pool,
            'pager' => $pager,
            'term' => $term,
            'filters' => $filters,
            'admin' => $admin,
            'show_empty_boxes' => $this->emptyBoxesOption,
        ], $response);
    }

    public function getName()
    {
        return 'Admin Search Result';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'admin_code' => '',
                'query' => '',
                'page' => 0,
                'per_page' => 10,
                'icon' => 'fa fa-list',
            ])
            ->setRequired('admin_code')
            ->setAllowedTypes('admin_code', ['string']);
    }
}
