<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Sonata\CoreBundle\Test\AbstractWidgetTestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTranslator;

/**
 * Class BaseWidgetTest.
 *
 * Base class for tests checking rendering of form widgets with form_admin_fields.html.twig and
 * filter_admin_fields.html.twig. Template to use is defined by $this->type variable, that needs to be overridden in
 * child classes.
 */
abstract class BaseWidgetTest extends AbstractWidgetTestCase
{
    /**
     * Current template type, form or filter.
     *
     * @var string
     */
    protected $type = null;

    /**
     * @var array
     */
    protected $sonataAdmin = array(
        'name' => null,
        'admin' => null,
        'value' => null,
        'edit' => 'standard',
        'inline' => 'natural',
        'field_description' => null,
        'block_name' => false,
        'options' => array(
            'form_type' => 'vertical',
            'use_icheck' => true,
        ),
    );

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment()
    {
        $environment = parent::getEnvironment();
        $environment->addGlobal('sonata_admin', $this->getSonataAdmin());
        if (!$environment->hasExtension('translator')) {
            $environment->addExtension(new TranslationExtension(new StubTranslator()));
        }

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRenderingEngine()
    {
        if (!in_array($this->type, array('form', 'filter'))) {
            throw new \Exception('Please override $this->type in your test class specifying template to use (either form or filter)');
        }

        return new TwigRendererEngine(array(
            $this->type.'_admin_fields.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSonataAdmin()
    {
        return $this->sonataAdmin;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplatePaths()
    {
        return array_merge(parent::getTemplatePaths(), array(
            __DIR__.'/../../../Resources/views/Form',
        ));
    }
}
