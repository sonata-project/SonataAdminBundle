<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests;

use Sonata\AdminBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testOptions()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array());

        $this->assertTrue($config['options']['html5_validate']);
        $this->assertNull($config['options']['pager_links']);
        $this->assertTrue($config['options']['confirm_exit']);
        $this->assertTrue($config['options']['use_icheck']);
    }

    public function testOptionsWithInvalidFormat()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidTypeException');

        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'options' => array(
                'html5_validate' => '1'
            )
        )));
    }

    public function testCustomTemplatesPerAdmin()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'admin_services' => array(
                'my_admin_id' => array(
                    'templates' => array(
                        'form' => array('form.twig.html', 'form_extra.twig.html'),
                        'view' => array('user_block' => 'SonataAdminBundle:mycustomtemplate.html.twig'),
                        'filter' => array()
                    )
                )
            )
        )));

        $this->assertEquals('SonataAdminBundle:mycustomtemplate.html.twig', $config['admin_services']['my_admin_id']['templates']['view']['user_block']);
    }

    public function testAdminServicesDefault()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'admin_services' => array('my_admin_id' => array())
        )));

        $this->assertEquals(array(
            'model_manager' => null,
            'form_contractor' => null,
            'show_builder' => null,
            'list_builder' => null,
            'datagrid_builder' => null,
            'translator' => null,
            'configuration_pool' => null,
            'validator' => null,
            'security_handler' => null,
            'label' => null,
            'templates' => array(),
            'route_generator' => null,
            'menu_factory' => null,
            'route_builder' => null,
            'label_translator_strategy' => null,
            'templates' => array(
                'form' => array(),
                'filter' => array(),
                'view' => array(),
            )
        ), $config['admin_services']['my_admin_id']);
    }
}
