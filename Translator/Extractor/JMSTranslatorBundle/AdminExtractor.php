<?php

namespace Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;

use JMS\TranslationBundle\Translation\ExtractorInterface;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\FileSource;

class AdminExtractor implements ExtractorInterface, TranslatorInterface, SecurityHandlerInterface, LabelTranslatorStrategyInterface
{
    private $logger;
    private $adminPool;
    private $catalogue;
    private $translator;
    private $labelStrategy;
    private $domain;

    /**
     * @param \Sonata\AdminBundle\Admin\Pool                    $adminPool
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function __construct(Pool $adminPool, LoggerInterface $logger = null)
    {
        $this->logger    = $logger;
        $this->adminPool = $adminPool;

        // state variable
        $this->catalogue     = false;
        $this->translator    = false;
        $this->labelStrategy = false;
        $this->domain        = false;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Extract messages to MessageCatalogue
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

        $this->catalogue = new MessageCatalogue;

        foreach ($this->adminPool->getAdminServiceIds() as $id) {
            $admin = $this->getAdmin($id);

            $this->translator    = $admin->getTranslator();
            $this->labelStrategy = $admin->getLabelTranslatorStrategy();
            $this->domain        = $admin->getTranslationDomain();

            $admin->setTranslator($this);
            $admin->setSecurityHandler($this);
            $admin->setLabelTranslatorStrategy($this);

//            foreach ($admin->getChildren() as $child) {
//                $child->setTranslator($this);
//            }

            // call the different public method
            $methods = array(
                'getShow'         => array(array()),
                'getDatagrid'     => array(array()),
                'getList'         => array(array()),
                'getForm'         => array(array()),
                'getBreadcrumbs'  => array(
                    array('list'),
                    array('edit'),
                    array('create'),
                    array('update'),
                    array('batch'),
                    array('delete'),
                )
            );

            if ($this->logger) {
                $this->logger->info(sprintf('Retrieving message from admin:%s - class: %s', $admin->getCode(), get_class($admin)));
            }

            foreach ($methods as $method => $calls) {
                foreach ($calls as $args) {
                    try {
                        call_user_func_array(array($admin, $method), $args);
                    } catch (\Exception $e) {
                        if ($this->logger) {
                            $this->logger->err(sprintf('ERROR : admin:%s - Raise an exception : %s', $admin->getCode(), $e->getMessage()));
                        }

                        throw $e;
                    }
                }
            }
        }

        $catalogue = $this->catalogue;
        $this->catalogue = false;

        return $catalogue;
    }

    /**
     * @param string $id
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
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

        //        $this->logger->debug(sprintf('extract: %s - domain:%s', $id, $domain));

        $trace = debug_backtrace(false);
        if (isset($trace[1]['file'])) {
            $message->addSource(new FileSource($trace[1]['file']));
        }

        $this->catalogue->add($message);
    }

    /**
     * {@inheritDoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $this->addMessage($id, $domain);

        return $id;
    }

    /**
     * {@inheritDoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $this->addMessage($id, $domain);

        return $id;
    }

    /**
     * {@inheritDoc}
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    /**
     * {@inheritDoc}
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        $label = $this->labelStrategy->getLabel($label, $context, $type);

        $this->addMessage($label, $this->domain);

        return $label;
    }
}
