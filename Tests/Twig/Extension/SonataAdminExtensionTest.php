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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Psr\Log\LoggerInterface;

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

    /**
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var FieldDescriptionInterface
     */
    private $fieldDescription;

    /**
     * @var \stdClass
     */
    private $object;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        date_default_timezone_set('Europe/London');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->pool = new Pool($container, '', '');
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->twigExtension = new SonataAdminExtension($this->pool, $this->logger);

        $loader = new StubFilesystemLoader(array(
            __DIR__.'/../../../Resources/views/CRUD',
        ));

        $this->environment = new \Twig_Environment($loader, array('strict_variables' => true, 'cache' => false, 'autoescape' => true, 'optimizations' => 0));
        $this->environment->addExtension($this->twigExtension);

        // translation extension
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource('xlf', __DIR__.'/../../../Resources/translations/SonataAdminBundle.en.xliff', 'en', 'SonataAdminBundle');
        $this->environment->addExtension(new TranslationExtension($translator));

        // routing extension
        $xmlFileLoader = new XmlFileLoader(new FileLocator(array(__DIR__.'/../../../Resources/config/routing')));
        $routeCollection = $xmlFileLoader->load('sonata_admin.xml');

        $xmlFileLoader = new XmlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures/Resources/config/routing')));
        $testRouteCollection = $xmlFileLoader->load('routing.xml');

        $routeCollection->addCollection($testRouteCollection);
        $requestContext = new RequestContext();
        $urlGenerator = new UrlGenerator($routeCollection, $requestContext);
        $this->environment->addExtension(new RoutingExtension($urlGenerator));

        $this->twigExtension->initRuntime($this->environment);

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $this->admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('xyz'));

        $this->admin->expects($this->any())
            ->method('id')
            ->with($this->equalTo($this->object))
            ->will($this->returnValue(12345));

        $this->admin->expects($this->any())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($this->object))
            ->will($this->returnValue(12345));

        $this->admin->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id) {
                return $id;
            }));

        // for php5.3 BC
        $admin = $this->admin;

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($admin) {
                if ($id == 'sonata_admin_foo_service') {
                    return $admin;
                }

                return null;
            }));

        // initialize field description
        $this->fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $this->fieldDescription->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('fd_name'));

        $this->fieldDescription->expects($this->any())
            ->method('getAdmin')
            ->will($this->returnValue($this->admin));

        $this->fieldDescription->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('Data'));
    }

    /**
     * @dataProvider getRenderListElementTests
     */
    public function testRenderListElement($expected, $type, $value, array $options)
    {
        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_field.html.twig'));

        $this->fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($value));

        $this->fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $this->fieldDescription->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $this->fieldDescription->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnCallback(function () use ($type) {
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
                    case 'choice':
                        return 'SonataAdminBundle:CRUD:list_choice.html.twig';
                    case 'array':
                        return 'SonataAdminBundle:CRUD:list_array.html.twig';
                    case 'trans':
                        return 'SonataAdminBundle:CRUD:list_trans.html.twig';
                    case 'url':
                        return 'SonataAdminBundle:CRUD:list_url.html.twig';
                    case 'nonexistent':
                        // template doesn`t exist
                        return 'SonataAdminBundle:CRUD:list_nonexistent_template.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $this->twigExtension->renderListElement($this->object, $this->fieldDescription))));
    }

    public function getRenderListElementTests()
    {
        return array(
            array('<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>', 'string', 'Example', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> </td>', 'string', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-nonexistent" objectId="12345"> Example </td>', 'nonexistent', 'Example', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-nonexistent" objectId="12345"> </td>', 'nonexistent', null, array()),
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
            array('<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> [1 => First] [2 => Second] </td>', 'array', array(1 => 'First', 2 => 'Second'), array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> </td>', 'array', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <span class="label label-success">yes</span> </td>', 'boolean', true, array('editable'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <span class="label label-danger">no</span> </td>', 'boolean', false, array('editable'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <span class="label label-danger">no</span> </td>', 'boolean', null, array('editable'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <span class="x-editable" data-type="select" data-value="1" data-title="Data" data-pk="12345" data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz" data-source="[{value: 0, text: \'no\'},{value: 1, text: \'yes\'}]" > <span class="label label-success">yes</span> </span> </td>', 'boolean', true, array('editable'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <span class="x-editable" data-type="select" data-value="" data-title="Data" data-pk="12345" data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz" data-source="[{value: 0, text: \'no\'},{value: 1, text: \'yes\'}]" > <span class="label label-danger">no</span> </span> </td>', 'boolean', false, array('editable'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345"> <span class="x-editable" data-type="select" data-value="" data-title="Data" data-pk="12345" data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz" data-source="[{value: 0, text: \'no\'},{value: 1, text: \'yes\'}]" > <span class="label label-danger">no</span> </span> </td>', 'boolean', null, array('editable'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>', 'trans', 'action_delete', array('catalogue'=>'SonataAdminBundle')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> </td>', 'trans', null, array('catalogue'=>'SonataAdminBundle')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>', 'choice', 'Status1', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>', 'choice', array('Status1'), array('choices'=>array(), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 </td>', 'choice', 'Status1', array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>', 'choice', null, array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> NoValidKeyInChoices </td>', 'choice', 'NoValidKeyInChoices', array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete </td>', 'choice', 'Foo', array('catalogue'=>'SonataAdminBundle', 'choices'=>array('Foo'=>'action_delete', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1, Alias3 </td>', 'choice', array('Status1', 'Status3'), array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 | Alias3 </td>', 'choice', array('Status1', 'Status3'), array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true, 'delimiter'=>' | ')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>', 'choice', null, array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> NoValidKeyInChoices </td>', 'choice', array('NoValidKeyInChoices'), array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> NoValidKeyInChoices, Alias2 </td>', 'choice', array('NoValidKeyInChoices', 'Status2'), array('choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete, Alias3 </td>', 'choice', array('Foo', 'Status3'), array('catalogue'=>'SonataAdminBundle', 'choices'=>array('Foo'=>'action_delete', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> &lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt; </td>', 'choice', array('Status1', 'Status3'), array('choices'=>array('Status1'=>'<b>Alias1</b>', 'Status2'=>'<b>Alias2</b>', 'Status3'=>'<b>Alias3</b>'), 'multiple'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>', 'url', null, array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>', 'url', null, array('url'=>'http://example.com')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>', 'url', null, array('route'=>array('name'=>'sonata_admin_foo'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://example.com">http://example.com</a> </td>', 'url', 'http://example.com', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="https://example.com">https://example.com</a> </td>', 'url', 'https://example.com', array()),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://example.com">example.com</a> </td>', 'url', 'http://example.com', array('hide_protocol'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="https://example.com">example.com</a> </td>', 'url', 'https://example.com', array('hide_protocol'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://example.com">http://example.com</a> </td>', 'url', 'http://example.com', array('hide_protocol'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="https://example.com">https://example.com</a> </td>', 'url', 'https://example.com', array('hide_protocol'=>false)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://example.com">Foo</a> </td>', 'url', 'Foo', array('url'=>'http://example.com')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a> </td>', 'url', '<b>Foo</b>', array('url'=>'http://example.com')),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="/foo">Foo</a> </td>', 'url', 'Foo', array('route'=>array('name'=>'sonata_admin_foo'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://localhost/foo">Foo</a> </td>', 'url', 'Foo', array('route'=>array('name'=>'sonata_admin_foo', 'absolute'=>true))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="/foo">foo/bar?a=b&amp;c=123456789</a> </td>', 'url', 'http://foo/bar?a=b&c=123456789', array('route'=>array('name'=>'sonata_admin_foo'), 'hide_protocol'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a> </td>', 'url', 'http://foo/bar?a=b&c=123456789', array('route'=>array('name'=>'sonata_admin_foo', 'absolute'=>true), 'hide_protocol'=>true)),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="/foo/abcd/efgh?param3=ijkl">Foo</a> </td>', 'url', 'Foo', array('route'=>array('name'=>'sonata_admin_foo_param', 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl')))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a> </td>', 'url', 'Foo', array('route'=>array('name'=>'sonata_admin_foo_param', 'absolute'=>true, 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl')))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a> </td>', 'url', 'Foo', array('route'=>array('name'=>'sonata_admin_foo_object', 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl'), 'identifier_parameter_name'=>'barId'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> <a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a> </td>', 'url', 'Foo', array('route'=>array('name'=>'sonata_admin_foo_object', 'absolute'=>true, 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl'), 'identifier_parameter_name'=>'barId'))),
        );
    }

    public function testRenderListElementNonExistentTemplate()
    {
        $this->admin->expects($this->once())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_field.html.twig'));

        $this->fieldDescription->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('Foo'));

        $this->fieldDescription->expects($this->once())
            ->method('getFieldName')
            ->will($this->returnValue('Foo_name'));

        $this->fieldDescription->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue('nonexistent'));

        $this->fieldDescription->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:list_nonexistent_template.html.twig'));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(($this->stringStartsWith('An error occured trying to load the template "SonataAdminBundle:CRUD:list_nonexistent_template.html.twig" for the field "Foo_name", the default template "SonataAdminBundle:CRUD:base_list_field.html.twig" was used instead: "Unable to find template "list_nonexistent_template.html.twig')));

         $this->twigExtension->renderListElement($this->object, $this->fieldDescription);
    }

    /**
     * @expectedException        Twig_Error_Loader
     * @expectedExceptionMessage Unable to find template "base_list_nonexistent_field.html.twig"
     */
    public function testRenderListElementErrorLoadingTemplate()
    {
        $this->admin->expects($this->once())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_nonexistent_field.html.twig'));

        $this->fieldDescription->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:list_nonexistent_template.html.twig'));

         $this->twigExtension->renderListElement($this->object, $this->fieldDescription);
    }

    /**
     * @dataProvider getRenderViewElementTests
     */
    public function testRenderViewElement($expected, $type, $value, array $options)
    {
        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_show_field.html.twig'));

        $this->fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(function () use ($value) {
                if ($value instanceof NoValueException) {
                    throw  $value;
                }

                return $value;
            }));

        $this->fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $this->fieldDescription->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $this->fieldDescription->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnCallback(function () use ($type) {
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
                    case 'choice':
                        return 'SonataAdminBundle:CRUD:show_choice.html.twig';
                    case 'array':
                        return 'SonataAdminBundle:CRUD:show_array.html.twig';
                    case 'trans':
                        return 'SonataAdminBundle:CRUD:show_trans.html.twig';
                    case 'url':
                        return 'SonataAdminBundle:CRUD:show_url.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $this->twigExtension->renderViewElement($this->fieldDescription, $this->object))));
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
            array('<th>Data</th> <td>Status1</td>', 'choice', 'Status1', array('safe'=>false)),
            array('<th>Data</th> <td>Alias1</td>', 'choice', 'Status1', array('safe'=>false, 'choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<th>Data</th> <td>NoValidKeyInChoices</td>', 'choice', 'NoValidKeyInChoices', array('safe'=>false, 'choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<th>Data</th> <td>Delete</td>', 'choice', 'Foo', array('safe'=>false, 'catalogue'=>'SonataAdminBundle', 'choices'=>array('Foo'=>'action_delete', 'Status2'=>'Alias2', 'Status3'=>'Alias3'))),
            array('<th>Data</th> <td>NoValidKeyInChoices</td>', 'choice', array('NoValidKeyInChoices'), array('safe'=>false, 'choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true,)),
            array('<th>Data</th> <td>NoValidKeyInChoices, Alias2</td>', 'choice', array('NoValidKeyInChoices', 'Status2'), array('safe'=>false, 'choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true,)),
            array('<th>Data</th> <td>Alias1, Alias3</td>', 'choice', array('Status1', 'Status3'), array('safe'=>false, 'choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true,)),
            array('<th>Data</th> <td>Alias1 | Alias3</td>', 'choice', array('Status1', 'Status3'), array('safe'=>false, 'choices'=>array('Status1'=>'Alias1', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true, 'delimiter'=>' | ')),
            array('<th>Data</th> <td>Delete, Alias3</td>', 'choice', array('Foo', 'Status3'), array('safe'=>false, 'catalogue'=>'SonataAdminBundle', 'choices'=>array('Foo'=>'action_delete', 'Status2'=>'Alias2', 'Status3'=>'Alias3'), 'multiple'=>true,)),
            array('<th>Data</th> <td><b>Alias1</b>, <b>Alias3</b></td>', 'choice', array('Status1', 'Status3'), array('safe'=>true, 'choices'=>array('Status1'=>'<b>Alias1</b>', 'Status2'=>'<b>Alias2</b>', 'Status3'=>'<b>Alias3</b>'), 'multiple'=>true,)),
            array('<th>Data</th> <td>&lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt;</td>', 'choice', array('Status1', 'Status3'), array('safe'=>false, 'choices'=>array('Status1'=>'<b>Alias1</b>', 'Status2'=>'<b>Alias2</b>', 'Status3'=>'<b>Alias3</b>'), 'multiple'=>true,)),
            array('<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>', 'url', 'http://example.com', array('safe'=>false)),
            array('<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>', 'url', 'https://example.com', array('safe'=>false)),
            array('<th>Data</th> <td><a href="http://example.com">example.com</a></td>', 'url', 'http://example.com', array('safe'=>false, 'hide_protocol'=>true)),
            array('<th>Data</th> <td><a href="https://example.com">example.com</a></td>', 'url', 'https://example.com', array('safe'=>false, 'hide_protocol'=>true)),
            array('<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>', 'url', 'http://example.com', array('safe'=>false, 'hide_protocol'=>false)),
            array('<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>', 'url', 'https://example.com', array('safe'=>false, 'hide_protocol'=>false)),
            array('<th>Data</th> <td><a href="http://example.com">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'url'=>'http://example.com')),
            array('<th>Data</th> <td><a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a></td>', 'url', '<b>Foo</b>', array('safe'=>false, 'url'=>'http://example.com')),
            array('<th>Data</th> <td><a href="http://example.com"><b>Foo</b></a></td>', 'url', '<b>Foo</b>', array('safe'=>true, 'url'=>'http://example.com')),
            array('<th>Data</th> <td><a href="/foo">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo'))),
            array('<th>Data</th> <td><a href="http://localhost/foo">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo', 'absolute'=>true))),
            array('<th>Data</th> <td><a href="/foo">foo/bar?a=b&amp;c=123456789</a></td>', 'url', 'http://foo/bar?a=b&c=123456789', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo'), 'hide_protocol'=>true)),
            array('<th>Data</th> <td><a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a></td>', 'url', 'http://foo/bar?a=b&c=123456789', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo', 'absolute'=>true), 'hide_protocol'=>true)),
            array('<th>Data</th> <td><a href="/foo/abcd/efgh?param3=ijkl">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo_param', 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl')))),
            array('<th>Data</th> <td><a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo_param', 'absolute'=>true, 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl')))),
            array('<th>Data</th> <td><a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo_object', 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl'), 'identifier_parameter_name'=>'barId'))),
            array('<th>Data</th> <td><a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a></td>', 'url', 'Foo', array('safe'=>false, 'route'=>array('name'=>'sonata_admin_foo_object', 'absolute'=>true, 'parameters'=>array('param1'=>'abcd', 'param2'=>'efgh', 'param3'=>'ijkl'), 'identifier_parameter_name'=>'barId'))),

            // NoValueException
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
            array('<th>Data</th> <td></td>', 'choice', new NoValueException(), array('safe'=>false, 'choices'=>array())),
            array('<th>Data</th> <td></td>', 'choice', new NoValueException(), array('safe'=>false, 'choices'=>array(), 'multiple'=>true)),
            array('<th>Data</th> <td>&nbsp;</td>', 'url', new NoValueException(), array()),
            array('<th>Data</th> <td>&nbsp;</td>', 'url', new NoValueException(), array('url'=>'http://example.com')),
            array('<th>Data</th> <td>&nbsp;</td>', 'url', new NoValueException(), array('route'=>array('name'=>'sonata_admin_foo'))),
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

    public function testGetValueFromFieldDescriptionWithRemoveLoopException()
    {
        $object =  $this->getMock('\ArrayAccess');
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        try {
            $this->assertEquals('anything', $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription, array('loop'=>true)));
        } catch (\RuntimeException $e) {
            $this->assertContains('remove the loop requirement', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testGetValueFromFieldDescriptionWithNoValueException()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(function () {
                throw new NoValueException();
            }));

        $fieldDescription->expects($this->any())
            ->method('getAssociationAdmin')
            ->will($this->returnValue(null));

        $this->assertEquals(null, $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }

    public function testGetValueFromFieldDescriptionWithNoValueExceptionNewAdminInstance()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(function () {
                throw new NoValueException();
            }));

        $fieldDescription->expects($this->any())
            ->method('getAssociationAdmin')
            ->will($this->returnValue($this->admin));

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->will($this->returnValue('foo'));

        $this->assertEquals('foo', $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }

    public function testOutput()
    {
        $this->fieldDescription->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_field.html.twig'));

        $this->fieldDescription->expects($this->any())
            ->method('getFieldName')
            ->will($this->returnValue('fd_name'));

        $this->environment->disableDebug();

        $parameters = array(
            'admin'             => $this->admin,
            'value'             => 'foo',
            'field_description' => $this->fieldDescription,
            'object'            => $this->object,
        );

        $template = $this->environment->loadTemplate('SonataAdminBundle:CRUD:base_list_field.html.twig');

        $this->assertEquals('<td class="sonata-ba-list-field sonata-ba-list-field-" objectId="12345"> foo </td>',
                trim(preg_replace('/\s+/', ' ', $this->twigExtension->output($this->fieldDescription, $template, $parameters))));

        $this->environment->enableDebug();
        $this->assertEquals('<!-- START fieldName: fd_name template: SonataAdminBundle:CRUD:base_list_field.html.twig compiled template: SonataAdminBundle:CRUD:base_list_field.html.twig --> <td class="sonata-ba-list-field sonata-ba-list-field-" objectId="12345"> foo </td> <!-- END - fieldName: fd_name -->',
                trim(preg_replace('/\s+/', ' ', $this->twigExtension->output($this->fieldDescription, $template, $parameters))));
    }

    public function testRenderRelationElementNoObject()
    {
        $this->assertEquals('foo', $this->twigExtension->renderRelationElement('foo', $this->fieldDescription));
    }

    public function testRenderRelationElementToString()
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_property') {
                    return $default;
                }

                if ($value == 'associated_tostring') {
                    return '__toString';
                }
            }));

       $element = $this->getMock('stdClass', array('__toString'));
       $element->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue('bar'));

       $this->assertEquals('bar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    public function testRenderRelationElementCustomToString()
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_property') {
                    return $default;
                }

                if ($value == 'associated_tostring') {
                    return 'customToString';
                }
            }));

       $element = $this->getMock('stdClass', array('customToString'));
       $element->expects($this->any())
            ->method('customToString')
            ->will($this->returnValue('fooBar'));

       $this->assertEquals('fooBar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    public function testRenderRelationElementMethodNotExist()
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')

            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_tostring') {
                    return 'nonExistedMethod';
                }
            }));

        $element = new \stdClass();

        try {
            $this->twigExtension->renderRelationElement($element, $this->fieldDescription);
        } catch (\RuntimeException $e) {
            $this->assertContains('You must define an `associated_property` option or create a `stdClass::__toString', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testRenderRelationElementWithPropertyPath()
    {
        $this->fieldDescription->expects($this->exactly(1))
            ->method('getOption')

            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_property') {
                    return 'foo';
                }
            }));

        $element = new \stdClass();
        $element->foo = "bar";

        $this->assertEquals('bar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));

    }

    public function testGetUrlsafeIdentifier()
    {
        $entity = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds(array('sonata_admin_foo_service'));
        $this->pool->setAdminClasses(array('stdClass'=> array('sonata_admin_foo_service')));

        $this->admin->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->with($this->equalTo($entity))
            ->will($this->returnValue(1234567));

        $this->assertEquals(1234567, $this->twigExtension->getUrlsafeIdentifier($entity));
    }
}
