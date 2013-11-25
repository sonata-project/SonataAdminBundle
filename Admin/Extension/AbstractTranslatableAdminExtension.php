<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\AdminBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Add locale switcher for your models
 * Locale is managed by the url
 *
 * Extends this to fit your database layer
 *
 * @author Nicolas Bastien <nbastien.pro@gmail.com>
 */
abstract class AbstractTranslatableAdminExtension extends AdminExtension
{
    /**
     * Request parameter
     */
    const TRANSLATABLE_LOCALE_PARAMETER = 'tl';

    /**
     * @return ContainerInterface
     */
    protected function getContainer(AdminInterface $admin)
    {
        return $admin->getConfigurationPool()->getContainer();
    }

    /**
     * Return the list of possible locales for your models
     *
     * @return array
     */
    protected function getLocales(AdminInterface $admin)
    {
        return $this->getContainer($admin)->getParameter('locales');
    }

    /**
     * Return the default locales if url parameter is not present
     *
     * @return string
     */
    protected function getDefaultLocale(AdminInterface $admin)
    {
        return $this->getContainer($admin)->getParameter('default_locale');
    }

    /**
     * Return current translatable locale
     * ie: the locale used to load object translations != current request locale
     *
     * @return string
     */
    public function getTranslatableLocale(AdminInterface $admin)
    {
        if ($this->translatableLocale == null) {
            if ($admin->getRequest()) {
                $this->translatableLocale = $admin->getRequest()->get(self::TRANSLATABLE_LOCALE_PARAMETER);
            }
            if ($this->translatableLocale == null) {
                $this->translatableLocale = $this->getDefaultLocale($admin);
            }
        }

        return $this->translatableLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters(AdminInterface $admin)
    {
        return array(self::TRANSLATABLE_LOCALE_PARAMETER => $this->getTranslatableLocale($admin));
    }
}
