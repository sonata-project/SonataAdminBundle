<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * NEXT_MAJOR: do not implement TranslatorInterface anymore.
 */
class AdminExtractor implements ExtractorInterface, TranslatorInterface, SecurityHandlerInterface, LabelTranslatorStrategyInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Pool
     */
    private $adminPool;

    /**
     * @var string|bool
     */
    private $catalogue;

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @var string|bool
     */
    private $translator;

    /**
     * @var string|bool
     */
    private $labelStrategy;

    /**
     * @var string|bool
     */
    private $domain;

    /**
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    /**
     * @param Pool            $adminPool
     * @param LoggerInterface $logger
     */
    public function __construct(Pool $adminPool, LoggerInterface $logger = null)
    {
        @trigger_error(
            'Implementing the Symfony\Component\Translation\TranslatorInterface for '.__CLASS__
            .' is deprecated since version 3.x and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->logger = $logger;
        $this->adminPool = $adminPool;

        // state variable
        $this->catalogue = false;
        $this->translator = false; // NEXT_MAJOR: remove this line
        $this->labelStrategy = false;
        $this->domain = false;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * NEXT_MAJOR : use a constructor argument instead.
     */
    final public function setBreadcrumbsBuilder(BreadcrumbsBuilderInterface $breadcrumbsBuilder)
    {
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
    }

    /**
     * Extract messages to MessageCatalogue.
     *
     * @return MessageCatalogue
     *
     * @throws \Exception|\RuntimeException
     */
    public function extract()
    {
        if ($this->catalogue) {
            throw new \RuntimeException('Invalid state');
        }

        $this->catalogue = new MessageCatalogue();

        foreach ($this->adminPool->getAdminGroups() as $name => $group) {
            $this->trans($name, array(), $group['label_catalogue']);
        }

        foreach ($this->adminPool->getAdminServiceIds() as $id) {
            $admin = $this->getAdmin($id);

            $label = $admin->getLabel();
            if (!empty($label)) {
                $this->trans($label, array(), $admin->getTranslationDomain());
            }

            $this->translator = $admin->getTranslator();
            $this->labelStrategy = $admin->getLabelTranslatorStrategy();
            $this->domain = $admin->getTranslationDomain();

            $admin->setTranslator($this); // NEXT_MAJOR: remove this line
            $admin->setSecurityHandler($this);
            $admin->setLabelTranslatorStrategy($this);

            // call the different public method
            $methods = array(
                'getShow',
                'getDatagrid',
                'getList',
                'getForm',
            );

            $actions = array(
                'list',
                'edit',
                'create',
                'update',
                'batch',
                'delete',
            );

            if ($this->logger) {
                $this->logger->info(sprintf(
                    'Retrieving message from admin:%s - class: %s',
                        $admin->getCode(),
                        get_class($admin)
                ));
            }

            foreach ($methods as $method) {
                try {
                    $admin->$method();
                } catch (\Exception $e) {
                    if ($this->logger) {
                        $this->logger->error(sprintf(
                            'ERROR : admin:%s - Raise an exception : %s',
                                $admin->getCode(),
                                $e->getMessage()
                        ));
                    }

                    throw $e;
                }
            }

            foreach ($actions as $action) {
                try {
                    $this->breadcrumbsBuilder->getBreadcrumbs($admin, $action);
                } catch (\Exception $e) {
                    if ($this->logger) {
                        $this->logger->error(
                            sprintf(
                                'ERROR : admin:%s - Raises an exception : %s',
                                $admin->getCode(),
                                $e->getMessage()
                            ),
                            array('exception' => $e)
                        );
                    }

                    throw $e;
                }
            }
        }

        $catalogue = $this->catalogue;
        $this->catalogue = false;

        return $catalogue;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->addMessage($id, $domain);

        return $id;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->addMessage($id, $domain);

        return $id;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->translator->setLocale($locale);
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * {@inheritdoc}
     */
    public function getLocale()
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        return $this->translator->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        $label = $this->labelStrategy->getLabel($label, $context, $type);

        $this->addMessage($label, $this->domain);

        return $label;
    }

    /**
     * @param string $id
     *
     * @return AdminInterface
     */
    private function getAdmin($id)
    {
        return $this->adminPool->getContainer()->get($id);
    }

    /**
     * @param string $id
     * @param string $domain
     */
    private function addMessage($id, $domain)
    {
        $message = new Message($id, $domain);

        $trace = debug_backtrace(false);
        if (isset($trace[1]['file'])) {
            $message->addSource(new FileSource($trace[1]['file']));
        }

        $this->catalogue->add($message);
    }
}
