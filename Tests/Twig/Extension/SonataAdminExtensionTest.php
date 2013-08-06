<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubFilesystemLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\RequestContext;
use Sonata\AdminBundle\Exception\NoValueException;

/**
 * Test for SonataAdminExtension
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SonataAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SonataAdminExtension
     */
    private $twigExtension;

    public function setUp()
    {
        date_default_timezone_set('Europe/London');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = new Pool($container, '', '');
        $this->twigExtension = new SonataAdminExtension($pool);

        $loader = new StubFilesystemLoader(array(
            __DIR__.'/../../../Resources/views/CRUD',
        ));

        $environment = new \Twig_Environment($loader, array('strict_variables' => true, 'cache' => false, 'autoescape' => true, 'optimizations' => 0));
        $environment->addExtension($this->twigExtension);

        //translation extension
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource('xlf', __DIR__.'/../../../Resources/translations/SonataAdminBundle.en.xliff', 'en', 'SonataAdminBundle');
        $environment->addExtension(new TranslationExtension($translator));

        //routing extension
        $xmlFileLoader = new XmlFileLoader(new FileLocator(array(__DIR__.'/../../../Resources/config/routing')));
        $routeCollection = $xmlFileLoader->load('sonata_admin.xml');
        $requestContext = new RequestContext();
        $urlGenerator = new UrlGenerator($routeCollection, $requestContext);
        $environment->addExtension(new RoutingExtension($urlGenerator));

        $this->twigExtension->initRuntime($environment);
    }

    public function testSlugify()
    {
        $this->assertEquals($this->twigExtension->slugify('test'), 'test');
        $this->assertEquals($this->twigExtension->slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertEquals($this->twigExtension->slugify('Symfony2'), 'symfony2');
        $this->assertEquals($this->twigExtension->slugify('test'), 'test');
        $this->assertEquals($this->twigExtension->slugify('c\'est bientôt l\'été'), 'c-est-bientot-l-ete');
        $this->assertEquals($this->twigExtension->slugify(urldecode('%2Fc\'est+bientôt+l\'été')), 'c-est-bientot-l-ete');
    }

    /**
     * @dataProvider getRenderListElementTests
     */
    public function testRenderListElement($expected, $type, $value, array $options)
    {
        $object = new \stdClass();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $admin->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_field.html.twig'));

        $admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $admin->expects($this->any())
            ->method('getCode')
            ->with($this->equalTo($object))
            ->will($this->returnValue('xyz'));

        $admin->expects($this->any())
            ->method('id')
            ->with($this->equalTo($object))
            ->will($this->returnValue(12345));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('fd_name'));

        $fieldDescription->expects($this->any())
            ->method('getAdmin')
            ->will($this->returnValue($admin));

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($value));

        $fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $fieldDescription->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $fieldDescription->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnCallback(function() use ($type) {
                switch ($type) {
                    case 'string':
                        return 'SonataAdminBundle:CRUD:list_string.html.twig';
                    case 'boolean':
                        return 'SonataAdminBundle:CRUD:list_boolean.html.twig';
                    case 'datetime':
                        return 'SonataAdminBundle:CRUD:list_datetime.html.twig';
                    case 'date':
                        return 'SonataAdminBundle:CRUD:list_date.html.twig';
                    case 'time':
                        return 'SonataAdminBundle:CRUD:list_time.html.twig';
                    case 'currency':
                        return 'SonataAdminBundle:CRUD:list_currency.html.twig';
                    case 'percent':
                        return 'SonataAdminBundle:CRUD:list_percent.html.twig';
                    case 'array':
                        return 'SonataAdminBundle:CRUD:list_array.html.twig';
                    case 'trans':
                        return 'SonataAdminBundle:CRUD:list_trans.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $this->twigExtension->renderListElement($object, $fieldDescription))));
    }

    public function getRenderListElementTests()
    {
        return array(
            array('<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>', 'string', 'Example', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> </td>', 'string', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> Example </td>', 'text', 'Example', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> </td>', 'text', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> Example </td>', 'textarea', 'Example', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> </td>', 'textarea', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> December 24, 2013 10:11 </td>', 'datetime', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>', 'datetime', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> 24.12.2013 10:11:12 </td>', 'datetime', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array('format'=>'d.m.Y H:i:s')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>', 'datetime', null, array('format'=>'d.m.Y H:i:s')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> December 24, 2013 </td>', 'date', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>', 'date', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> 24.12.2013 </td>', 'date', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array('format'=>'d.m.Y')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>', 'date', null, array('format'=>'d.m.Y')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> 10:11:12 </td>', 'time', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> &nbsp; </td>', 'time', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> 10.746135 </td>', 'number', 10.746135, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> </td>', 'number', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> 5678 </td>', 'integer', 5678, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> </td>', 'integer', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 1074.6135 % </td>', 'percent', 10.746135, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 0 % </td>', 'percent', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> EUR 10.746135 </td>', 'currency', 10.746135, array('currency' => 'EUR')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> </td>', 'currency', null, array('currency' => 'EUR')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> GBP 51.23456 </td>', 'currency', 51.23456, array('currency' => 'GBP')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> </td>', 'currency', null, array('currency' => 'GBP')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> [1 => First] [2 => Second] </td>', 'array', array(1 => 'First', 2 => 'Second'), array('safe' => false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> </td>', 'array', null, array('safe' => false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <i class="icon-ok-circle"></i>&nbsp;yes </td>', 'boolean', true, array('editable'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <i class="icon-ban-circle"></i>&nbsp;no </td>', 'boolean', false, array('editable'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <i class="icon-ban-circle"></i>&nbsp;no </td>', 'boolean', null, array('editable'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <a href="http://localhost/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;value=0&amp;code=xyz" class="sonata-ba-action sonata-ba-edit-inline"><i class="icon-ok-circle"></i>&nbsp;yes</a> </td>', 'boolean', true, array('editable'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <a href="http://localhost/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;value=1&amp;code=xyz" class="sonata-ba-action sonata-ba-edit-inline"><i class="icon-ban-circle"></i>&nbsp;no</a> </td>', 'boolean', false, array('editable'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <a href="http://localhost/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;value=1&amp;code=xyz" class="sonata-ba-action sonata-ba-edit-inline"><i class="icon-ban-circle"></i>&nbsp;no</a> </td>', 'boolean', null, array('editable'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>', 'trans', 'action_delete', array('safe'=>false, 'catalogue'=>'SonataAdminBundle')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> </td>', 'trans', null, array('safe'=>false, 'catalogue'=>'SonataAdminBundle')),
        );
    }

    /**
     * @dataProvider getRenderViewElementTests
     */
    public function testRenderViewElement($expected, $type, $value, array $options)
    {
        $object = new \stdClass();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $admin->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_show_field.html.twig'));

        $admin->expects($this->any())
            ->method('id')
            ->with($this->equalTo($object))
            ->will($this->returnValue(12345));

        $admin->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function($id) {
                return $id;
            }));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getAdmin')
            ->will($this->returnValue($admin));

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(function() use ($value) {
                if ($value instanceof NoValueException) {
                    throw  $value;
                }

                return $value;
            }));

        $fieldDescription->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('Data'));

        $fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $fieldDescription->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $fieldDescription->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnCallback(function() use ($type) {
                switch ($type) {
                    case 'boolean':
                        return 'SonataAdminBundle:CRUD:show_boolean.html.twig';
                    case 'datetime':
                        return 'SonataAdminBundle:CRUD:show_datetime.html.twig';
                    case 'date':
                        return 'SonataAdminBundle:CRUD:show_date.html.twig';
                    case 'time':
                        return 'SonataAdminBundle:CRUD:show_time.html.twig';
                    case 'currency':
                        return 'SonataAdminBundle:CRUD:show_currency.html.twig';
                    case 'percent':
                        return 'SonataAdminBundle:CRUD:show_percent.html.twig';
                    case 'array':
                        return 'SonataAdminBundle:CRUD:show_array.html.twig';
                    case 'trans':
                        return 'SonataAdminBundle:CRUD:show_trans.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $this->twigExtension->renderViewElement($fieldDescription, $object))));
    }

    public function getRenderViewElementTests()
    {
        return array(
            array('<th>Data</th> <td>Example</td>', 'string', 'Example', array('safe' => false)),
            array('<th>Data</th> <td>Example</td>', 'text', 'Example', array('safe' => false)),
            array('<th>Data</th> <td>Example</td>', 'textarea', 'Example', array('safe' => false)),
            array('<th>Data</th> <td>December 24, 2013 10:11</td>', 'datetime', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array()),
            array('<th>Data</th> <td>24.12.2013 10:11:12</td>', 'datetime', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array('format'=>'d.m.Y H:i:s')),
            array('<th>Data</th> <td>December 24, 2013</td>', 'date', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array()),
            array('<th>Data</th> <td>24.12.2013</td>', 'date', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array('format'=>'d.m.Y')),
            array('<th>Data</th> <td>10:11:12</td>', 'time', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array()),
            array('<th>Data</th> <td>10.746135</td>', 'number', 10.746135, array('safe' => false)),
            array('<th>Data</th> <td>5678</td>', 'integer', 5678, array('safe' => false)),
            array('<th>Data</th> <td> 1074.6135 % </td>', 'percent', 10.746135, array()),
            array('<th>Data</th> <td> EUR 10.746135 </td>', 'currency', 10.746135, array('currency' => 'EUR')),
            array('<th>Data</th> <td> GBP 51.23456 </td>', 'currency', 51.23456, array('currency' => 'GBP')),
            array('<th>Data</th> <td> [1 => First] [2 => Second] </td>', 'array', array(1 => 'First', 2 => 'Second'), array('safe' => false)),
            array('<th>Data</th> <td><i class="icon-ok-circle"></i>yes</td>', 'boolean', true, array()),
            array('<th>Data</th> <td><i class="icon-ban-circle"></i>no</td>', 'boolean', false, array()),
            array('<th>Data</th> <td> Delete </td>', 'trans', 'action_delete', array('safe'=>false, 'catalogue'=>'SonataAdminBundle')),

            //NoValueException
            array('<th>Data</th> <td></td>', 'string', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td></td>', 'text', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td></td>', 'textarea', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td>&nbsp;</td>', 'datetime', new NoValueException(), array()),
            array('<th>Data</th> <td>&nbsp;</td>', 'datetime', new NoValueException(), array('format'=>'d.m.Y H:i:s')),
            array('<th>Data</th> <td>&nbsp;</td>', 'date', new NoValueException(), array()),
            array('<th>Data</th> <td>&nbsp;</td>', 'date', new NoValueException(), array('format'=>'d.m.Y')),
            array('<th>Data</th> <td>&nbsp;</td>', 'time', new NoValueException(), array()),
            array('<th>Data</th> <td></td>', 'number', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td></td>', 'integer', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td> 0 % </td>', 'percent', new NoValueException(), array()),
            array('<th>Data</th> <td> </td>', 'currency', new NoValueException(), array('currency' => 'EUR')),
            array('<th>Data</th> <td> </td>', 'currency', new NoValueException(), array('currency' => 'GBP')),
            array('<th>Data</th> <td> </td>', 'array', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td><i class="icon-ban-circle"></i>no</td>', 'boolean', new NoValueException(), array()),
            array('<th>Data</th> <td> </td>', 'trans', new NoValueException(), array('safe'=>false, 'catalogue'=>'SonataAdminBundle')),
        );
    }

    public function testGetValueFromFieldDescription()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('test123'));

        $this->assertEquals('test123', $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }

    public function testGetValueFromFieldDescriptionWithNoValueException()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(function() {
                throw new NoValueException();
            }));

        $fieldDescription->expects($this->any())
            ->method('getAssociationAdmin')
            ->will($this->returnValue(null));

        $this->assertEquals(null, $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }
}
