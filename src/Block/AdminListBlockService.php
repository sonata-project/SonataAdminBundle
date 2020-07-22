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
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
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
     * NEXT_MAJOR: Change signature for (Environment $twig, Pool $pool, ?TemplateRegistryInterface $templateRegistry = null).
     *
     * @param Environment|string                  $twigOrName
     * @param EngineInterface|Pool|null           $poolOrTemplating
     * @param Pool|TemplateRegistryInterface|null $templateRegistryOrPool
     */
    public function __construct(
        $twigOrName,
        ?object $poolOrTemplating,
        ?object $templateRegistryOrPool,
        ?TemplateRegistryInterface $templateRegistry = null
    ) {
        if ($poolOrTemplating instanceof Pool) {
            if (!$twigOrName instanceof Environment) {
                throw new \TypeError(sprintf(
                    'Argument 1 passed to %s() must be an instance of %s, %s given.',
                    __METHOD__,
                    Environment::class,
                    \is_object($twigOrName) ? 'instance of '.\get_class($twigOrName) : \gettype($twigOrName)
                ));
            }

            if (null !== $templateRegistryOrPool && !$templateRegistryOrPool instanceof TemplateRegistryInterface) {
                throw new \TypeError(sprintf(
                    'Argument 3 passed to %s() must be either null or an instance of %s, %s given.',
                    __METHOD__,
                    TemplateRegistryInterface::class,
                    \is_object($twigOrName) ? 'instance of '.\get_class($twigOrName) : \gettype($twigOrName)
                ));
            }

            parent::__construct($twigOrName);

            $this->pool = $poolOrTemplating;
            $this->templateRegistry = $templateRegistryOrPool ?: new TemplateRegistry();
        } elseif (null === $poolOrTemplating || $poolOrTemplating instanceof EngineInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 2 to %s() is deprecated since sonata-project/admin-bundle 3.x'
                .' and will throw a \TypeError in version 4.0. You must pass an instance of %s instead.',
                null === $poolOrTemplating ? 'null' : EngineInterface::class,
                __METHOD__,
                Pool::class
            ), E_USER_DEPRECATED);

            if (!$templateRegistryOrPool instanceof Pool) {
                throw new \TypeError(sprintf(
                    'Argument 2 passed to %s() must be an instance of %s, %s given.',
                    __METHOD__,
                    Pool::class,
                    null === $templateRegistryOrPool ? 'null' : 'instance of '.\get_class($templateRegistryOrPool)
                ));
            }

            parent::__construct($twigOrName, $poolOrTemplating);

            $this->pool = $templateRegistryOrPool;
            $this->templateRegistry = $templateRegistry ?: new TemplateRegistry();
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

    public function execute(BlockContextInterface $blockContext, ?Response $response = null)
    {
        $dashboardGroups = $this->pool->getDashboardGroups();

        $settings = $blockContext->getSettings();

        $visibleGroups = [];
        foreach ($dashboardGroups as $name => $dashboardGroup) {
            if (!$settings['groups'] || \in_array($name, $settings['groups'], true)) {
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
