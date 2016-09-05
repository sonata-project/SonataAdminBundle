<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToString;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubFilesystemLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Test for SonataAdminExtension.
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
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var AdminInterface
     */
    private $adminBar;

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

    /**
     * @var string[]
     */
    private $xEditableTypeMapping;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setUp()
    {
        date_default_timezone_set('Europe/London');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->pool = new Pool($container, '', '');
        $this->pool->setAdminServiceIds(array('sonata_admin_foo_service'));
        $this->pool->setAdminClasses(array('fooClass' => array('sonata_admin_foo_service')));

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->xEditableTypeMapping = array(
            'choice' => 'select',
            'boolean' => 'select',
            'text' => 'text',
            'textarea' => 'textarea',
            'html' => 'textarea',
            'email' => 'email',
            'string' => 'text',
            'smallint' => 'text',
            'bigint' => 'text',
            'integer' => 'number',
            'decimal' => 'number',
            'currency' => 'number',
            'percent' => 'number',
            'url' => 'url',
        );

        // translation extension
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource(
            'xlf',
            __DIR__.'/../../../Resources/translations/SonataAdminBundle.en.xliff',
            'en',
            'SonataAdminBundle'
        );

        $this->translator = $translator;

        $this->twigExtension = new SonataAdminExtension($this->pool, $this->logger, $this->translator);
        $this->twigExtension->setXEditableTypeMapping($this->xEditableTypeMapping);

        $loader = new StubFilesystemLoader(array(
            __DIR__.'/../../../Resources/views/CRUD',
        ));

        $this->environment = new \Twig_Environment($loader, array(
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ));
        $this->environment->addExtension($this->twigExtension);
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
        $this->environment->addExtension(new \Twig_Extensions_Extension_Text());

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

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
            ->will($this->returnCallback(function ($id, $parameters = array(), $domain = null) use ($translator) {
                return $translator->trans($id, $parameters, $domain);
            }));

        $this->adminBar = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->adminBar->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));
        $this->adminBar->expects($this->any())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($this->object))
            ->will($this->returnValue(12345));

        // for php5.3 BC
        $admin = $this->admin;
        $adminBar = $this->adminBar;

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($admin, $adminBar) {
                if ($id == 'sonata_admin_foo_service') {
                    return $admin;
                } elseif ($id == 'sonata_admin_bar_service') {
                    return $adminBar;
                }

                return;
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
            ->method('isGranted')
            ->will($this->returnValue(true));

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
            ->method('getOption')
            ->will($this->returnCallback(function ($name, $default = null) use ($options) {
                return isset($options[$name]) ? $options[$name] : $default;
            }));

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
                    case 'email':
                        return 'SonataAdminBundle:CRUD:list_email.html.twig';
                    case 'choice':
                        return 'SonataAdminBundle:CRUD:list_choice.html.twig';
                    case 'array':
                        return 'SonataAdminBundle:CRUD:list_array.html.twig';
                    case 'trans':
                        return 'SonataAdminBundle:CRUD:list_trans.html.twig';
                    case 'url':
                        return 'SonataAdminBundle:CRUD:list_url.html.twig';
                    case 'html':
                        return 'SonataAdminBundle:CRUD:list_html.html.twig';
                    case 'nonexistent':
                        // template doesn`t exist
                        return 'SonataAdminBundle:CRUD:list_nonexistent_template.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertSame(
            $this->removeExtraWhitespace($expected),
            $this->removeExtraWhitespace($this->twigExtension->renderListElement(
                $this->environment,
                $this->object,
                $this->fieldDescription
            ))
        );
    }

    /**
     * @dataProvider getDeprecatedRenderListElementTests
     * @group legacy
     */
    public function testDeprecatedRenderListElement($expected, $value, array $options)
    {
        $this->admin->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_field.html.twig'));

        $this->fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($value));

        $this->fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('nonexistent'));

        $this->fieldDescription->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $this->fieldDescription->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function ($name, $default = null) use ($options) {
                return isset($options[$name]) ? $options[$name] : $default;
            }));

        $this->fieldDescription->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:list_nonexistent_template.html.twig'));

        $this->assertSame(
            $this->removeExtraWhitespace($expected),
            $this->removeExtraWhitespace($this->twigExtension->renderListElement(
                $this->environment,
                $this->object,
                $this->fieldDescription
            ))
        );
    }

    public function getDeprecatedRenderListElementTests()
    {
        return array(
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-nonexistent" objectId="12345"> Example </td>',
                'Example',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-nonexistent" objectId="12345"> </td>',
                null,
                array(),
            ),
        );
    }

    public function getRenderListElementTests()
    {
        return array(
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>',
                'string',
                'Example',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> </td>',
                'string',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> Example </td>',
                'text',
                'Example',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> </td>',
                'text',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> Example </td>',
                'textarea',
                'Example',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> </td>',
                'textarea',
                null,
                array(),
            ),
            'datetime field' => array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    December 24, 2013 10:11
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    December 24, 2013 18:11
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                array('timezone' => 'Asia/Hong_Kong'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                'datetime',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    24.12.2013 10:11:12
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array('format' => 'd.m.Y H:i:s'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                'datetime',
                null,
                array('format' => 'd.m.Y H:i:s'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    24.12.2013 18:11:12
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                array('format' => 'd.m.Y H:i:s', 'timezone' => 'Asia/Hong_Kong'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                'datetime',
                null,
                array('format' => 'd.m.Y H:i:s', 'timezone' => 'Asia/Hong_Kong'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> December 24, 2013 </td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>',
                'date',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> 24.12.2013 </td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array('format' => 'd.m.Y'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>',
                'date',
                null,
                array('format' => 'd.m.Y'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> 10:11:12 </td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> &nbsp; </td>',
                'time',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> 10.746135 </td>',
                'number', 10.746135,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> </td>',
                'number',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> 5678 </td>',
                'integer',
                5678,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> </td>',
                'integer',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 1074.6135 % </td>',
                'percent',
                10.746135,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 0 % </td>',
                'percent',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> EUR 10.746135 </td>',
                'currency',
                10.746135,
                array('currency' => 'EUR'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> </td>',
                'currency',
                null,
                array('currency' => 'EUR'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> GBP 51.23456 </td>',
                'currency',
                51.23456,
                array('currency' => 'GBP'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> </td>',
                'currency',
                null,
                array('currency' => 'GBP'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> &nbsp; </td>',
                'email',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> <a href="mailto:admin@admin.com">admin@admin.com</a> </td>',
                'email',
                'admin@admin.com',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> 
                    <a href="mailto:admin@admin.com">admin@admin.com</a> </td>',
                'email',
                'admin@admin.com',
                array('as_string' => false),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                array('as_string' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">  
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(array('subject' => 'Main Theme', 'body' => 'Message Body')).'">admin@admin.com</a>  </td>',
                'email',
                'admin@admin.com',
                array('subject' => 'Main Theme', 'body' => 'Message Body'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">  
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(array('subject' => 'Main Theme')).'">admin@admin.com</a>  </td>',
                'email',
                'admin@admin.com',
                array('subject' => 'Main Theme'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">  
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(array('body' => 'Message Body')).'">admin@admin.com</a>  </td>',
                'email',
                'admin@admin.com',
                array('body' => 'Message Body'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                array('as_string' => true, 'subject' => 'Main Theme', 'body' => 'Message Body'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                array('as_string' => true, 'body' => 'Message Body'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                array('as_string' => true, 'subject' => 'Main Theme'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345">
                    [1 => First] [2 => Second]
                </td>',
                'array',
                array(1 => 'First', 2 => 'Second'),
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> </td>',
                'array',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-success">yes</span>
                </td>',
                'boolean',
                true,
                array('editable' => false),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-danger">no</span>
                </td>',
                'boolean',
                false,
                array('editable' => false),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-danger">no</span>
                </td>',
                'boolean',
                null,
                array('editable' => false),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="1"
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{value: 0, text: 'no'},{value: 1, text: 'yes'}]"
    >
        <span class="label label-success">yes</span>
    </span>
</td>
EOT
            ,
                'boolean',
                true,
                array('editable' => true),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value=""
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{value: 0, text: 'no'},{value: 1, text: 'yes'}]"
    >
    <span class="label label-danger">no</span> </span>
</td>
EOT
                ,
                'boolean',
                false,
                array('editable' => true),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value=""
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{value: 0, text: 'no'},{value: 1, text: 'yes'}]" >
        <span class="label label-danger">no</span> </span>
</td>
EOT
                ,
                'boolean',
                null,
                array('editable' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>',
                'trans',
                'action_delete',
                array('catalogue' => 'SonataAdminBundle'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> </td>',
                'trans',
                null,
                array('catalogue' => 'SonataAdminBundle'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>',
                'trans',
                'action_delete',
                array('format' => '%s', 'catalogue' => 'SonataAdminBundle'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345">
                action.action_delete
                </td>',
                'trans',
                'action_delete',
                array('format' => 'action.%s'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345">
                action.action_delete
                </td>',
                'trans',
                'action_delete',
                array('format' => 'action.%s', 'catalogue' => 'SonataAdminBundle'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>',
                'choice',
                'Status1',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>',
                'choice',
                array('Status1'),
                array('choices' => array(), 'multiple' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 </td>',
                'choice',
                'Status1',
                array('choices' => array('Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3')),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>',
                'choice',
                null,
                array('choices' => array('Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3')),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices
                </td>',
                'choice',
                'NoValidKeyInChoices',
                array('choices' => array('Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3')),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete </td>',
                'choice',
                'Foo',
                array('catalogue' => 'SonataAdminBundle', 'choices' => array(
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                )),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1, Alias3 </td>',
                'choice',
                array('Status1', 'Status3'),
                array('choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true), ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 | Alias3 </td>',
                'choice',
                array('Status1', 'Status3'),
                array('choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true, 'delimiter' => ' | '), ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>',
                'choice',
                null,
                array('choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices
                </td>',
                'choice',
                array('NoValidKeyInChoices'),
                array('choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices, Alias2
                </td>',
                'choice',
                array('NoValidKeyInChoices', 'Status2'),
                array('choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete, Alias3 </td>',
                'choice',
                array('Foo', 'Status3'),
                array('catalogue' => 'SonataAdminBundle', 'choices' => array(
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                &lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt;
            </td>',
                'choice',
                array('Status1', 'Status3'),
                array('choices' => array(
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ), 'multiple' => true), ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="Status1"
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[]"
    >
        Status1
    </span>
</td>
EOT
                ,
                'choice',
                'Status1',
                array('editable' => true),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="Status1"
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Alias1&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
        Alias1 </span>
</td>
EOT
                ,
                'choice',
                'Status1',
                array(
                    'editable' => true,
                    'choices' => array(
                        'Status1' => 'Alias1',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ),
                ),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value=""
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Alias1&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >

    </span>
</td>
EOT
                ,
                'choice',
                null,
                array(
                    'editable' => true,
                    'choices' => array(
                        'Status1' => 'Alias1',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ),
                ),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="NoValidKeyInChoices"
        data-title="Data" data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Alias1&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
        NoValidKeyInChoices
    </span>
</td>
EOT
                ,
                'choice',
                'NoValidKeyInChoices',
                array(
                    'editable' => true,
                    'choices' => array(
                        'Status1' => 'Alias1',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ),
                ),
            ),
            array(
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="Foo"
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{&quot;value&quot;:&quot;Foo&quot;,&quot;text&quot;:&quot;action_delete&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
         Delete
    </span>
</td>
EOT
                ,
                'choice',
                'Foo',
                array(
                    'editable' => true,
                    'catalogue' => 'SonataAdminBundle',
                    'choices' => array(
                        'Foo' => 'action_delete',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ),
                ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                'url',
                null,
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                'url',
                null,
                array('url' => 'http://example.com'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                'url',
                null,
                array('route' => array('name' => 'sonata_admin_foo')),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">http://example.com</a>
                </td>',
                'url',
                'http://example.com',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                'url',
                'https://example.com',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">example.com</a>
                </td>',
                'url',
                'http://example.com',
                array('hide_protocol' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">example.com</a>
                </td>',
                'url',
                'https://example.com',
                array('hide_protocol' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">http://example.com</a>
                </td>',
                'url',
                'http://example.com',
                array('hide_protocol' => false),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                'url',
                'https://example.com',
                array('hide_protocol' => false),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">Foo</a>
                </td>',
                'url',
                'Foo',
                array('url' => 'http://example.com'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a>
                </td>',
                'url',
                '<b>Foo</b>',
                array('url' => 'http://example.com'),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo">Foo</a>
                </td>',
                'url',
                'Foo',
                array('route' => array('name' => 'sonata_admin_foo')),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo">Foo</a>
                </td>',
                'url',
                'Foo',
                array('route' => array('name' => 'sonata_admin_foo', 'absolute' => true)),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo">foo/bar?a=b&amp;c=123456789</a>
                </td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                array('route' => array('name' => 'sonata_admin_foo'),
                'hide_protocol' => true, ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a>
                </td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                array(
                    'route' => array('name' => 'sonata_admin_foo', 'absolute' => true),
                    'hide_protocol' => true,
                ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo/abcd/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                array(
                    'route' => array('name' => 'sonata_admin_foo_param',
                    'parameters' => array('param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'), ),
                ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                array(
                    'route' => array('name' => 'sonata_admin_foo_param',
                    'absolute' => true,
                    'parameters' => array('param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'), ),
                ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                array(
                    'route' => array('name' => 'sonata_admin_foo_object',
                    'parameters' => array('param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'),
                    'identifier_parameter_name' => 'barId', ),
                ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                array(
                    'route' => array('name' => 'sonata_admin_foo_object',
                    'absolute' => true,
                    'parameters' => array('param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'),
                    'identifier_parameter_name' => 'barId', ),
                ),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                <p><strong>Creating a Template for the Field</strong> and form</p>
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array(),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Field and form
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('strip' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Fi...
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => true),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345"> Creating a... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => array('length' => 10)),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Field...
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => array('preserve' => true)),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Fi etc.
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => array('separator' => ' etc.')),
            ),
            array(
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for[...]
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array(
                    'truncate' => array(
                        'length' => 20,
                        'preserve' => true,
                        'separator' => '[...]',
                    ),
                ),
            ),
        );
    }

    /**
     * @group legacy
     */
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
            ->with(($this->stringStartsWith($this->removeExtraWhitespace(
                'An error occured trying to load the template
                "SonataAdminBundle:CRUD:list_nonexistent_template.html.twig"
                for the field "Foo_name", the default template
                    "SonataAdminBundle:CRUD:base_list_field.html.twig" was used
                    instead.'
            ))));

        $this->twigExtension->renderListElement($this->environment, $this->object, $this->fieldDescription);
    }

    /**
     * @expectedException        Twig_Error_Loader
     * @expectedExceptionMessage Unable to find template "base_list_nonexistent_field.html.twig"
     * @group                    legacy
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

        $this->twigExtension->renderListElement($this->environment, $this->object, $this->fieldDescription);
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
                    case 'email':
                        return 'SonataAdminBundle:CRUD:show_email.html.twig';
                    case 'choice':
                        return 'SonataAdminBundle:CRUD:show_choice.html.twig';
                    case 'array':
                        return 'SonataAdminBundle:CRUD:show_array.html.twig';
                    case 'trans':
                        return 'SonataAdminBundle:CRUD:show_trans.html.twig';
                    case 'url':
                        return 'SonataAdminBundle:CRUD:show_url.html.twig';
                    case 'html':
                        return 'SonataAdminBundle:CRUD:show_html.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertSame($expected, trim(preg_replace(
            '/\s+/',
            ' ',
            $this->twigExtension->renderViewElement(
                $this->environment,
                $this->fieldDescription,
                $this->object
            )
        )));
    }

    public function getRenderViewElementTests()
    {
        return array(
            array('<th>Data</th> <td>Example</td>', 'string', 'Example', array('safe' => false)),
            array('<th>Data</th> <td>Example</td>', 'text', 'Example', array('safe' => false)),
            array('<th>Data</th> <td>Example</td>', 'textarea', 'Example', array('safe' => false)),
            array(
                '<th>Data</th> <td>December 24, 2013 10:11</td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), array(),
            ),
            array(
                '<th>Data</th> <td>24.12.2013 10:11:12</td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array('format' => 'd.m.Y H:i:s'),
            ),
            array(
                '<th>Data</th> <td>December 24, 2013</td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array(),
            ),
            array(
                '<th>Data</th> <td>24.12.2013</td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array('format' => 'd.m.Y'),
            ),
            array(
                '<th>Data</th> <td>10:11:12</td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                array(),
            ),
            array('<th>Data</th> <td>10.746135</td>', 'number', 10.746135, array('safe' => false)),
            array('<th>Data</th> <td>5678</td>', 'integer', 5678, array('safe' => false)),
            array('<th>Data</th> <td> 1074.6135 % </td>', 'percent', 10.746135, array()),
            array('<th>Data</th> <td> EUR 10.746135 </td>', 'currency', 10.746135, array('currency' => 'EUR')),
            array('<th>Data</th> <td> GBP 51.23456 </td>', 'currency', 51.23456, array('currency' => 'GBP')),
            array(
                '<th>Data</th> <td> [1 => First] <br> [2 => Second] </td>',
                'array',
                array(1 => 'First', 2 => 'Second'),
                array('safe' => false),
            ),
            array(
                '<th>Data</th> <td> [1 => First] [2 => Second] </td>',
                'array',
                array(1 => 'First', 2 => 'Second'),
                array('safe' => false, 'inline' => true),
            ),
            array(
                '<th>Data</th> <td><span class="label label-success">yes</span></td>',
                'boolean',
                true,
                array(),
            ),
            array('<th>Data</th> <td><span class="label label-danger">no</span></td>', 'boolean', false, array()),
            array(
                '<th>Data</th> <td> Delete </td>',
                'trans',
                'action_delete',
                array('safe' => false, 'catalogue' => 'SonataAdminBundle'),
            ),
            array('<th>Data</th> <td>Status1</td>', 'choice', 'Status1', array('safe' => false)),
            array(
                '<th>Data</th> <td>Alias1</td>',
                'choice',
                'Status1',
                array('safe' => false, 'choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                )),
            ),
            array(
                '<th>Data</th> <td>NoValidKeyInChoices</td>',
                'choice',
                'NoValidKeyInChoices',
                array('safe' => false, 'choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                )),
            ),
            array(
                '<th>Data</th> <td>Delete</td>',
                'choice',
                'Foo',
                array('safe' => false, 'catalogue' => 'SonataAdminBundle', 'choices' => array(
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                )),
            ),
            array(
                '<th>Data</th> <td>NoValidKeyInChoices</td>',
                'choice',
                array('NoValidKeyInChoices'),
                array('safe' => false, 'choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<th>Data</th> <td>NoValidKeyInChoices, Alias2</td>',
                'choice',
                array('NoValidKeyInChoices', 'Status2'),
                array('safe' => false, 'choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<th>Data</th> <td>Alias1, Alias3</td>',
                'choice',
                array('Status1', 'Status3'),
                array('safe' => false, 'choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<th>Data</th> <td>Alias1 | Alias3</td>',
                'choice',
                array('Status1', 'Status3'), array('safe' => false, 'choices' => array(
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true, 'delimiter' => ' | '),
            ),
            array(
                '<th>Data</th> <td>Delete, Alias3</td>',
                'choice',
                array('Foo', 'Status3'),
                array('safe' => false, 'catalogue' => 'SonataAdminBundle', 'choices' => array(
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ), 'multiple' => true),
            ),
            array(
                '<th>Data</th> <td><b>Alias1</b>, <b>Alias3</b></td>',
                'choice',
                array('Status1', 'Status3'),
                array('safe' => true, 'choices' => array(
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ), 'multiple' => true),
            ),
            array(
                '<th>Data</th> <td>&lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt;</td>',
                'choice',
                array('Status1', 'Status3'),
                array('safe' => false, 'choices' => array(
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ), 'multiple' => true),
            ),
            array(
                '<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>',
                'url',
                'http://example.com',
                array('safe' => false),
            ),
            array(
                '<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>',
                'url',
                'https://example.com',
                array('safe' => false),
            ),
            array(
                '<th>Data</th> <td><a href="http://example.com">example.com</a></td>',
                'url',
                'http://example.com',
                array('safe' => false, 'hide_protocol' => true),
            ),
            array(
                '<th>Data</th> <td><a href="https://example.com">example.com</a></td>',
                'url',
                'https://example.com',
                array('safe' => false, 'hide_protocol' => true),
            ),
            array(
                '<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>',
                'url',
                'http://example.com',
                array('safe' => false, 'hide_protocol' => false),
            ),
            array(
                '<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>',
                'url',
                'https://example.com',
                array('safe' => false,
                'hide_protocol' => false, ),
            ),
            array(
                '<th>Data</th> <td><a href="http://example.com">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'url' => 'http://example.com'),
            ),
            array(
                '<th>Data</th> <td><a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a></td>',
                'url',
                '<b>Foo</b>',
                array('safe' => false, 'url' => 'http://example.com'),
            ),
            array(
                '<th>Data</th> <td><a href="http://example.com"><b>Foo</b></a></td>',
                'url',
                '<b>Foo</b>',
                array('safe' => true, 'url' => 'http://example.com'),
            ),
            array(
                '<th>Data</th> <td><a href="/foo">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'route' => array('name' => 'sonata_admin_foo')),
            ),
            array(
                '<th>Data</th> <td><a href="http://localhost/foo">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'route' => array(
                    'name' => 'sonata_admin_foo',
                    'absolute' => true,
                )),
            ),
            array(
                '<th>Data</th> <td><a href="/foo">foo/bar?a=b&amp;c=123456789</a></td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                array(
                    'safe' => false,
                    'route' => array('name' => 'sonata_admin_foo'),
                    'hide_protocol' => true,
                ),
            ),
            array(
                '<th>Data</th> <td><a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a></td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                array('safe' => false, 'route' => array(
                    'name' => 'sonata_admin_foo',
                    'absolute' => true,
                ), 'hide_protocol' => true),
            ),
            array(
                '<th>Data</th> <td><a href="/foo/abcd/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'route' => array(
                    'name' => 'sonata_admin_foo_param',
                    'parameters' => array('param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'),
                )),
            ),
            array(
                '<th>Data</th> <td><a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'route' => array(
                    'name' => 'sonata_admin_foo_param',
                    'absolute' => true,
                    'parameters' => array(
                        'param1' => 'abcd',
                        'param2' => 'efgh',
                        'param3' => 'ijkl',
                    ),
                )),
            ),
            array(
                '<th>Data</th> <td><a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'route' => array(
                    'name' => 'sonata_admin_foo_object',
                    'parameters' => array(
                        'param1' => 'abcd',
                        'param2' => 'efgh',
                        'param3' => 'ijkl',
                    ),
                    'identifier_parameter_name' => 'barId',
                )),
            ),
            array(
                '<th>Data</th> <td><a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                array('safe' => false, 'route' => array(
                    'name' => 'sonata_admin_foo_object',
                    'absolute' => true,
                    'parameters' => array(
                        'param1' => 'abcd',
                        'param2' => 'efgh',
                        'param3' => 'ijkl',
                    ),
                    'identifier_parameter_name' => 'barId',
                )),
            ),
            array(
                '<th>Data</th> <td> &nbsp;</td>',
                'email',
                null,
                array(),
            ),
            array(
                '<th>Data</th> <td> <a href="mailto:admin@admin.com">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                array(),
            ),
            array(
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(array('subject' => 'Main Theme', 'body' => 'Message Body')).'">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                array('subject' => 'Main Theme', 'body' => 'Message Body'),
            ),
            array(
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(array('subject' => 'Main Theme')).'">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                array('subject' => 'Main Theme'),
            ),
            array(
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(array('body' => 'Message Body')).'">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                array('body' => 'Message Body'),
            ),
            array(
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                array('as_string' => true, 'subject' => 'Main Theme', 'body' => 'Message Body'),
            ),
            array(
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                array('as_string' => true, 'subject' => 'Main Theme'),
            ),
            array(
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                array('as_string' => true, 'body' => 'Message Body'),
            ),
            array(
                '<th>Data</th> <td> <a href="mailto:admin@admin.com">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                array('as_string' => false),
            ),
            array(
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                array('as_string' => true),
            ),
            array(
                '<th>Data</th> <td><p><strong>Creating a Template for the Field</strong> and form</p> </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array(),
            ),
            array(
                '<th>Data</th> <td>Creating a Template for the Field and form </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('strip' => true),
            ),
            array(
                '<th>Data</th> <td> Creating a Template for the Fi... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => true),
            ),
            array(
                '<th>Data</th> <td> Creating a... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => array('length' => 10)),
            ),
            array(
                '<th>Data</th> <td> Creating a Template for the Field... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => array('preserve' => true)),
            ),
            array(
                '<th>Data</th> <td> Creating a Template for the Fi etc. </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array('truncate' => array('separator' => ' etc.')),
            ),
            array(
                '<th>Data</th> <td> Creating a Template for[...] </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                array(
                    'truncate' => array(
                        'length' => 20,
                        'preserve' => true,
                        'separator' => '[...]',
                    ),
                ),
            ),

            // NoValueException
            array('<th>Data</th> <td></td>', 'string', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td></td>', 'text', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td></td>', 'textarea', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td>&nbsp;</td>', 'datetime', new NoValueException(), array()),
            array(
                '<th>Data</th> <td>&nbsp;</td>',
                'datetime',
                new NoValueException(),
                array('format' => 'd.m.Y H:i:s'),
            ),
            array('<th>Data</th> <td>&nbsp;</td>', 'date', new NoValueException(), array()),
            array('<th>Data</th> <td>&nbsp;</td>', 'date', new NoValueException(), array('format' => 'd.m.Y')),
            array('<th>Data</th> <td>&nbsp;</td>', 'time', new NoValueException(), array()),
            array('<th>Data</th> <td></td>', 'number', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td></td>', 'integer', new NoValueException(), array('safe' => false)),
            array('<th>Data</th> <td> 0 % </td>', 'percent', new NoValueException(), array()),
            array('<th>Data</th> <td> </td>', 'currency', new NoValueException(), array('currency' => 'EUR')),
            array('<th>Data</th> <td> </td>', 'currency', new NoValueException(), array('currency' => 'GBP')),
            array('<th>Data</th> <td> </td>', 'array', new NoValueException(), array('safe' => false)),
            array(
                '<th>Data</th> <td><span class="label label-danger">no</span></td>',
                'boolean',
                new NoValueException(),
                array(),
            ),
            array(
                '<th>Data</th> <td> </td>',
                'trans',
                new NoValueException(),
                array('safe' => false, 'catalogue' => 'SonataAdminBundle'),
            ),
            array(
                '<th>Data</th> <td></td>',
                'choice',
                new NoValueException(),
                array('safe' => false, 'choices' => array()),
            ),
            array(
                '<th>Data</th> <td></td>',
                'choice',
                new NoValueException(),
                array('safe' => false, 'choices' => array(), 'multiple' => true),
            ),
            array('<th>Data</th> <td>&nbsp;</td>', 'url', new NoValueException(), array()),
            array(
                '<th>Data</th> <td>&nbsp;</td>',
                'url',
                new NoValueException(),
                array('url' => 'http://example.com'),
            ),
            array(
                '<th>Data</th> <td>&nbsp;</td>',
                'url',
                new NoValueException(),
                array('route' => array('name' => 'sonata_admin_foo')),
            ),
        );
    }

    public function testGetValueFromFieldDescription()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('test123'));

        $this->assertSame('test123', $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }

    public function testGetValueFromFieldDescriptionWithRemoveLoopException()
    {
        $object = $this->getMock('\ArrayAccess');
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        try {
            $this->assertSame(
                'anything',
                $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription, array('loop' => true))
            );
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

        $this->assertSame(null, $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
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

        $this->assertSame('foo', $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
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
            'admin' => $this->admin,
            'value' => 'foo',
            'field_description' => $this->fieldDescription,
            'object' => $this->object,
        );

        $template = $this->environment->loadTemplate('SonataAdminBundle:CRUD:base_list_field.html.twig');

        $this->assertSame(
            '<td class="sonata-ba-list-field sonata-ba-list-field-" objectId="12345"> foo </td>',
            $this->removeExtraWhitespace($this->twigExtension->output(
                $this->fieldDescription,
                $template,
                $parameters,
                $this->environment
            ))
        );

        $this->environment->enableDebug();
        $this->assertSame(
            $this->removeExtraWhitespace(<<<'EOT'
<!-- START
    fieldName: fd_name
    template: SonataAdminBundle:CRUD:base_list_field.html.twig
    compiled template: SonataAdminBundle:CRUD:base_list_field.html.twig
-->
    <td class="sonata-ba-list-field sonata-ba-list-field-" objectId="12345"> foo </td>
<!-- END - fieldName: fd_name -->
EOT
            ),
            $this->removeExtraWhitespace(
                $this->twigExtension->output($this->fieldDescription, $template, $parameters, $this->environment)
            )
        );
    }

    public function testRenderRelationElementNoObject()
    {
        $this->assertSame('foo', $this->twigExtension->renderRelationElement('foo', $this->fieldDescription));
    }

    public function testRenderRelationElementToString()
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_property') {
                    return $default;
                }
            }));

        $element = new FooToString();
        $this->assertSame('salut', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @group legacy
     */
    public function testDeprecatedRelationElementToString()
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_tostring') {
                    return '__toString';
                }
            }));

        $element = new FooToString();
        $this->assertSame(
            'salut',
            $this->twigExtension->renderRelationElement($element, $this->fieldDescription)
        );
    }

    /**
     * @group legacy
     */
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

        $this->assertSame('fooBar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @group legacy
     */
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
            $this->assertContains(
                'You must define an `associated_property` option or create a `stdClass::__toString',
                $e->getMessage()
            );

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
        $element->foo = 'bar';

        $this->assertSame('bar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    public function testRenderRelationElementWithClosure()
    {
        $this->fieldDescription->expects($this->exactly(1))
            ->method('getOption')

            ->will($this->returnCallback(function ($value, $default = null) {
                if ($value == 'associated_property') {
                    return function ($element) {
                        return 'closure '.$element->foo;
                    };
                }
            }));

        $element = new \stdClass();
        $element->foo = 'bar';

        $this->assertSame(
            'closure bar',
            $this->twigExtension->renderRelationElement($element, $this->fieldDescription)
        );
    }

    public function testGetUrlsafeIdentifier()
    {
        $entity = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds(array('sonata_admin_foo_service'));
        $this->pool->setAdminClasses(array('stdClass' => array('sonata_admin_foo_service')));

        $this->admin->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->with($this->equalTo($entity))
            ->will($this->returnValue(1234567));

        $this->assertSame(1234567, $this->twigExtension->getUrlsafeIdentifier($entity));
    }

    public function testGetUrlsafeIdentifier_GivenAdmin_Foo()
    {
        $entity = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds(array(
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ));
        $this->pool->setAdminClasses(array('stdClass' => array(
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        )));

        $this->admin->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->with($this->equalTo($entity))
            ->will($this->returnValue(1234567));

        $this->adminBar->expects($this->never())
            ->method('getUrlsafeIdentifier');

        $this->assertSame(1234567, $this->twigExtension->getUrlsafeIdentifier($entity, $this->admin));
    }

    public function testGetUrlsafeIdentifier_GivenAdmin_Bar()
    {
        $entity = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds(array('sonata_admin_foo_service', 'sonata_admin_bar_service'));
        $this->pool->setAdminClasses(array('stdClass' => array(
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        )));

        $this->admin->expects($this->never())
            ->method('getUrlsafeIdentifier');

        $this->adminBar->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->with($this->equalTo($entity))
            ->will($this->returnValue(1234567));

        $this->assertSame(1234567, $this->twigExtension->getUrlsafeIdentifier($entity, $this->adminBar));
    }

    /**
     * This method generates url part for Twig layout. Allows to keep BC for PHP 5.3.
     *
     * Remove this method for next major release only if PHP 5.3 support will be dropped.
     *
     * @param array $url
     *
     * @return string
     */
    private function buildTwigLikeUrl($url)
    {
        if (defined('PHP_QUERY_RFC3986')) {
            // add htmlspecialchars because twig add it auto
            return htmlspecialchars(http_build_query($url, '', '&', PHP_QUERY_RFC3986));
        }

        return htmlspecialchars(http_build_query($url, '', '&'));
    }

    private function removeExtraWhitespace($string)
    {
        return trim(preg_replace(
            '/\s+/',
            ' ',
            $string
        ));
    }
}
