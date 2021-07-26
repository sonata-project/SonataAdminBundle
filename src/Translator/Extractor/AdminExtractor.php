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

namespace Sonata\AdminBundle\Translator\Extractor;

use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @internal
 */
final class AdminExtractor implements ExtractorInterface, LabelTranslatorStrategyInterface
{
    private const PUBLIC_ADMIN_METHODS = [
        'getShow',
        'getDatagrid',
        'getList',
        'getForm',
    ];

    private const BREADCRUMB_ACTIONS = [
        'list',
        'edit',
        'create',
        'update',
        'batch',
        'delete',
    ];

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var MessageCatalogue|null
     */
    private $catalogue;

    /**
     * @var Pool
     */
    private $adminPool;

    /**
     * @var LabelTranslatorStrategyInterface|null
     */
    private $labelStrategy;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    public function __construct(Pool $adminPool, BreadcrumbsBuilderInterface $breadcrumbsBuilder)
    {
        $this->adminPool = $adminPool;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
    }

    /**
     * Extracts translation messages from files, a file or a directory to the catalogue.
     *
     * @param string|iterable<string> $resource Files, a file or a directory
     */
    public function extract($resource, MessageCatalogue $catalogue): void
    {
        $this->catalogue = $catalogue;

        foreach ($this->adminPool->getAdminGroups() as $name => $group) {
            $catalogue->set($name, $this->prefix.$name, $group['label_catalogue']);
        }

        foreach ($this->adminPool->getAdminServiceIds() as $id) {
            $admin = $this->adminPool->getInstance($id);

            $this->labelStrategy = $admin->getLabelTranslatorStrategy();
            $this->domain = $admin->getTranslationDomain();

            $label = $admin->getLabel();
            if (null !== $label && '' !== $label) {
                $catalogue->set($label, $this->prefix.$label, $admin->getTranslationDomain());
            }

            $admin->setLabelTranslatorStrategy($this);
            $admin->setSubject($admin->getNewInstance());

            foreach (self::PUBLIC_ADMIN_METHODS as $method) {
                $admin->$method();
            }

            foreach (self::BREADCRUMB_ACTIONS as $action) {
                $this->breadcrumbsBuilder->getBreadcrumbs($admin, $action);
            }
        }
    }

    /**
     * NEXT_MAJOR: Add string type hint when support for Symfony 4 is dropped.
     *
     * Sets the prefix that should be used for new found messages.
     *
     * @param string $prefix The prefix
     */
    public function setPrefix($prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getLabel(string $label, string $context = '', string $type = ''): string
    {
        if (null === $this->catalogue) {
            throw new \LogicException('The catalogue is not set.');
        }
        if (null === $this->labelStrategy) {
            throw new \LogicException('The label strategy is not set.');
        }
        if (null === $this->domain) {
            throw new \LogicException('The domain is not set.');
        }

        $label = $this->labelStrategy->getLabel($label, $context, $type);

        $this->catalogue->set($label, $this->prefix.$label, $this->domain);

        return $label;
    }
}
