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

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\DependencyInjection\TranslationDumperPass;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Test for SonataAdminExtension.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SonataAdminExtensionTest extends TestCase
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

        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        $this->pool = new Pool($container, '', '');
        $this->pool->setAdminServiceIds(['sonata_admin_foo_service']);
        $this->pool->setAdminClasses(['fooClass' => ['sonata_admin_foo_service']]);

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->xEditableTypeMapping = [
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
        ];

        // translation extension
        $translator = new Translator(
            'en',
            // NEXT_MAJOR: simplify this when dropping symfony < 3.4
            class_exists(TranslationDumperPass::class) ? null : new MessageSelector()
        );
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource(
            'xlf',
            __DIR__.'/../../../src/Resources/translations/SonataAdminBundle.en.xliff',
            'en',
            'SonataAdminBundle'
        );

        $this->translator = $translator;

        $this->twigExtension = new SonataAdminExtension($this->pool, $this->logger, $this->translator);
        $this->twigExtension->setXEditableTypeMapping($this->xEditableTypeMapping);

        $loader = new StubFilesystemLoader([
            __DIR__.'/../../../src/Resources/views/CRUD',
        ]);
        $loader->addPath(__DIR__.'/../../../src/Resources/views/', 'SonataAdmin');

        $this->environment = new \Twig_Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $this->environment->addExtension($this->twigExtension);
        $this->environment->addExtension(new TranslationExtension($translator));

        // routing extension
        $xmlFileLoader = new XmlFileLoader(new FileLocator([__DIR__.'/../../../src/Resources/config/routing']));
        $routeCollection = $xmlFileLoader->load('sonata_admin.xml');

        $xmlFileLoader = new XmlFileLoader(new FileLocator([__DIR__.'/../../Fixtures/Resources/config/routing']));
        $testRouteCollection = $xmlFileLoader->load('routing.xml');

        $routeCollection->addCollection($testRouteCollection);
        $requestContext = new RequestContext();
        $urlGenerator = new UrlGenerator($routeCollection, $requestContext);
        $this->environment->addExtension(new RoutingExtension($urlGenerator));
        $this->environment->addExtension(new \Twig_Extensions_Extension_Text());

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->createMock(AbstractAdmin::class);

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
            ->will($this->returnCallback(function ($id, $parameters = [], $domain = null) use ($translator) {
                return $translator->trans($id, $parameters, $domain);
            }));

        $this->adminBar = $this->createMock(AbstractAdmin::class);
        $this->adminBar->expects($this->any())
            ->method('hasAccess')
            ->will($this->returnValue(true));
        $this->adminBar->expects($this->any())
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($this->object))
            ->will($this->returnValue(12345));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) {
                if ('sonata_admin_foo_service' == $id) {
                    return $this->admin;
                } elseif ('sonata_admin_bar_service' == $id) {
                    return $this->adminBar;
                }
            }));

        // initialize field description
        $this->fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

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
            ->method('getPersistentParameters')
            ->will($this->returnValue(['context' => 'foo']));

        $this->admin->expects($this->any())
            ->method('hasAccess')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('@SonataAdmin/CRUD/base_list_field.html.twig'));

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
                        return '@SonataAdmin/CRUD/list_string.html.twig';
                    case 'boolean':
                        return '@SonataAdmin/CRUD/list_boolean.html.twig';
                    case 'datetime':
                        return '@SonataAdmin/CRUD/list_datetime.html.twig';
                    case 'date':
                        return '@SonataAdmin/CRUD/list_date.html.twig';
                    case 'time':
                        return '@SonataAdmin/CRUD/list_time.html.twig';
                    case 'currency':
                        return '@SonataAdmin/CRUD/list_currency.html.twig';
                    case 'percent':
                        return '@SonataAdmin/CRUD/list_percent.html.twig';
                    case 'email':
                        return '@SonataAdmin/CRUD/list_email.html.twig';
                    case 'choice':
                        return '@SonataAdmin/CRUD/list_choice.html.twig';
                    case 'array':
                        return '@SonataAdmin/CRUD/list_array.html.twig';
                    case 'trans':
                        return '@SonataAdmin/CRUD/list_trans.html.twig';
                    case 'url':
                        return '@SonataAdmin/CRUD/list_url.html.twig';
                    case 'html':
                        return '@SonataAdmin/CRUD/list_html.html.twig';
                    case 'nonexistent':
                        // template doesn`t exist
                        return '@SonataAdmin/CRUD/list_nonexistent_template.html.twig';
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
            ->method('hasAccess')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('@SonataAdmin/CRUD/base_list_field.html.twig'));

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
            ->will($this->returnValue('@SonataAdmin/CRUD/list_nonexistent_template.html.twig'));

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
        return [
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-nonexistent" objectId="12345"> Example </td>',
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-nonexistent" objectId="12345"> </td>',
                null,
                [],
            ],
        ];
    }

    public function getRenderListElementTests()
    {
        return [
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>',
                'string',
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> </td>',
                'string',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> Example </td>',
                'text',
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> </td>',
                'text',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> Example </td>',
                'textarea',
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> </td>',
                'textarea',
                null,
                [],
            ],
            'datetime field' => [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    December 24, 2013 10:11
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    December 24, 2013 18:11
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                'datetime',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    24.12.2013 10:11:12
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                'datetime',
                null,
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    24.12.2013 18:11:12
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['format' => 'd.m.Y H:i:s', 'timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                'datetime',
                null,
                ['format' => 'd.m.Y H:i:s', 'timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> December 24, 2013 </td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>',
                'date',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> 24.12.2013 </td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>',
                'date',
                null,
                ['format' => 'd.m.Y'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> 10:11:12 </td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> &nbsp; </td>',
                'time',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> 10.746135 </td>',
                'number', 10.746135,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> </td>',
                'number',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> 5678 </td>',
                'integer',
                5678,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> </td>',
                'integer',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 1074.6135 % </td>',
                'percent',
                10.746135,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 0 % </td>',
                'percent',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> EUR 10.746135 </td>',
                'currency',
                10.746135,
                ['currency' => 'EUR'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> </td>',
                'currency',
                null,
                ['currency' => 'EUR'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> GBP 51.23456 </td>',
                'currency',
                51.23456,
                ['currency' => 'GBP'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> </td>',
                'currency',
                null,
                ['currency' => 'GBP'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> &nbsp; </td>',
                'email',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> <a href="mailto:admin@admin.com">admin@admin.com</a> </td>',
                'email',
                'admin@admin.com',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com">admin@admin.com</a> </td>',
                'email',
                'admin@admin.com',
                ['as_string' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                ['as_string' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme', 'body' => 'Message Body']).'">admin@admin.com</a>  </td>',
                'email',
                'admin@admin.com',
                ['subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme']).'">admin@admin.com</a>  </td>',
                'email',
                'admin@admin.com',
                ['subject' => 'Main Theme'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['body' => 'Message Body']).'">admin@admin.com</a>  </td>',
                'email',
                'admin@admin.com',
                ['body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                ['as_string' => true, 'body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                'email',
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345">
                    [1 => First] [2 => Second]
                </td>',
                'array',
                [1 => 'First', 2 => 'Second'],
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> </td>',
                'array',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-success">yes</span>
                </td>',
                'boolean',
                true,
                ['editable' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-danger">no</span>
                </td>',
                'boolean',
                false,
                ['editable' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-danger">no</span>
                </td>',
                'boolean',
                null,
                ['editable' => false],
            ],
            [
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
                ['editable' => true],
            ],
            [
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="0"
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
                ['editable' => true],
            ],
            [
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="0"
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
                ['editable' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>',
                'trans',
                'action_delete',
                ['catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> </td>',
                'trans',
                null,
                ['catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>',
                'trans',
                'action_delete',
                ['format' => '%s', 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345">
                action.action_delete
                </td>',
                'trans',
                'action_delete',
                ['format' => 'action.%s'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345">
                action.action_delete
                </td>',
                'trans',
                'action_delete',
                ['format' => 'action.%s', 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>',
                'choice',
                'Status1',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>',
                'choice',
                ['Status1'],
                ['choices' => [], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 </td>',
                'choice',
                'Status1',
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>',
                'choice',
                null,
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices
                </td>',
                'choice',
                'NoValidKeyInChoices',
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete </td>',
                'choice',
                'Foo',
                ['catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1, Alias3 </td>',
                'choice',
                ['Status1', 'Status3'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true], ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 | Alias3 </td>',
                'choice',
                ['Status1', 'Status3'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true, 'delimiter' => ' | '], ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>',
                'choice',
                null,
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices
                </td>',
                'choice',
                ['NoValidKeyInChoices'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices, Alias2
                </td>',
                'choice',
                ['NoValidKeyInChoices', 'Status2'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete, Alias3 </td>',
                'choice',
                ['Foo', 'Status3'],
                ['catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                &lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt;
            </td>',
                'choice',
                ['Status1', 'Status3'],
                ['choices' => [
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ], 'multiple' => true], ],
            [
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
                ['editable' => true],
            ],
            [
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
                [
                    'editable' => true,
                    'choices' => [
                        'Status1' => 'Alias1',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ],
                ],
            ],
            [
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
                [
                    'editable' => true,
                    'choices' => [
                        'Status1' => 'Alias1',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ],
                ],
            ],
            [
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
                [
                    'editable' => true,
                    'choices' => [
                        'Status1' => 'Alias1',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ],
                ],
            ],
            [
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="select"
        data-value="Foo"
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=xyz"
        data-source="[{&quot;value&quot;:&quot;Foo&quot;,&quot;text&quot;:&quot;Delete&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
         Delete
    </span>
</td>
EOT
                ,
                'choice',
                'Foo',
                [
                    'editable' => true,
                    'catalogue' => 'SonataAdminBundle',
                    'choices' => [
                        'Foo' => 'action_delete',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                'url',
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                'url',
                null,
                ['url' => 'http://example.com'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                'url',
                null,
                ['route' => ['name' => 'sonata_admin_foo']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">http://example.com</a>
                </td>',
                'url',
                'http://example.com',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                'url',
                'https://example.com',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com" target="_blank">https://example.com</a>
                </td>',
                'url',
                'https://example.com',
                ['attributes' => ['target' => '_blank']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com" target="_blank" class="fooLink">https://example.com</a>
                </td>',
                'url',
                'https://example.com',
                ['attributes' => ['target' => '_blank', 'class' => 'fooLink']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">example.com</a>
                </td>',
                'url',
                'http://example.com',
                ['hide_protocol' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">example.com</a>
                </td>',
                'url',
                'https://example.com',
                ['hide_protocol' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">http://example.com</a>
                </td>',
                'url',
                'http://example.com',
                ['hide_protocol' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                'url',
                'https://example.com',
                ['hide_protocol' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">Foo</a>
                </td>',
                'url',
                'Foo',
                ['url' => 'http://example.com'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a>
                </td>',
                'url',
                '<b>Foo</b>',
                ['url' => 'http://example.com'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo">Foo</a>
                </td>',
                'url',
                'Foo',
                ['route' => ['name' => 'sonata_admin_foo']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo">Foo</a>
                </td>',
                'url',
                'Foo',
                ['route' => ['name' => 'sonata_admin_foo', 'absolute' => true]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo">foo/bar?a=b&amp;c=123456789</a>
                </td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                ['route' => ['name' => 'sonata_admin_foo'],
                'hide_protocol' => true, ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a>
                </td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                [
                    'route' => ['name' => 'sonata_admin_foo', 'absolute' => true],
                    'hide_protocol' => true,
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo/abcd/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                [
                    'route' => ['name' => 'sonata_admin_foo_param',
                    'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'], ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                [
                    'route' => ['name' => 'sonata_admin_foo_param',
                    'absolute' => true,
                    'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'], ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                [
                    'route' => ['name' => 'sonata_admin_foo_object',
                    'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                    'identifier_parameter_name' => 'barId', ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a>
                </td>',
                'url',
                'Foo',
                [
                    'route' => ['name' => 'sonata_admin_foo_object',
                    'absolute' => true,
                    'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                    'identifier_parameter_name' => 'barId', ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                <p><strong>Creating a Template for the Field</strong> and form</p>
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Field and form
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['strip' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Fi...
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345"> Creating a... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['length' => 10]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Field...
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['preserve' => true]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Fi etc.
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['separator' => ' etc.']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for[...]
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [
                    'truncate' => [
                        'length' => 20,
                        'preserve' => true,
                        'separator' => '[...]',
                    ],
                ],
            ],

            [
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345">
<div
    class="sonata-readmore"
    data-readmore-height="40"
    data-readmore-more="Read more"
    data-readmore-less="Close">A very long string</div>
</td>
EOT
                ,
                'text',
                'A very long string',
                [
                    'collapse' => true,
                ],
            ],
            [
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345">
<div
    class="sonata-readmore"
    data-readmore-height="10"
    data-readmore-more="More"
    data-readmore-less="Less">A very long string</div>
</td>
EOT
                ,
                'text',
                'A very long string',
                [
                    'collapse' => [
                        'height' => 10,
                        'more' => 'More',
                        'less' => 'Less',
                    ],
                ],
            ],
        ];
    }

    /**
     * @group legacy
     */
    public function testRenderListElementNonExistentTemplate()
    {
        $this->admin->expects($this->once())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('@SonataAdmin/CRUD/base_list_field.html.twig'));

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
            ->will($this->returnValue('@SonataAdmin/CRUD/list_nonexistent_template.html.twig'));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(($this->stringStartsWith($this->removeExtraWhitespace(
                'An error occured trying to load the template
                "@SonataAdmin/CRUD/list_nonexistent_template.html.twig"
                for the field "Foo_name", the default template
                    "@SonataAdmin/CRUD/base_list_field.html.twig" was used
                    instead.'
            ))));

        $this->twigExtension->renderListElement($this->environment, $this->object, $this->fieldDescription);
    }

    /**
     * @group                    legacy
     */
    public function testRenderListElementErrorLoadingTemplate()
    {
        $this->expectException(\Twig_Error_Loader::class);
        $this->expectExceptionMessage('Unable to find template "@SonataAdmin/CRUD/base_list_nonexistent_field.html.twig"');

        $this->admin->expects($this->once())
            ->method('getTemplate')
            ->with($this->equalTo('base_list_field'))
            ->will($this->returnValue('@SonataAdmin/CRUD/base_list_nonexistent_field.html.twig'));

        $this->fieldDescription->expects($this->once())
            ->method('getTemplate')
            ->will($this->returnValue('@SonataAdmin/CRUD/list_nonexistent_template.html.twig'));

        $this->twigExtension->renderListElement($this->environment, $this->object, $this->fieldDescription);
    }

    /**
     * @dataProvider getRenderViewElementTests
     */
    public function testRenderViewElement($expected, $type, $value, array $options)
    {
        $this->admin->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('@SonataAdmin/CRUD/base_show_field.html.twig'));

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
                        return '@SonataAdmin/CRUD/show_boolean.html.twig';
                    case 'datetime':
                        return '@SonataAdmin/CRUD/show_datetime.html.twig';
                    case 'date':
                        return '@SonataAdmin/CRUD/show_date.html.twig';
                    case 'time':
                        return '@SonataAdmin/CRUD/show_time.html.twig';
                    case 'currency':
                        return '@SonataAdmin/CRUD/show_currency.html.twig';
                    case 'percent':
                        return '@SonataAdmin/CRUD/show_percent.html.twig';
                    case 'email':
                        return '@SonataAdmin/CRUD/show_email.html.twig';
                    case 'choice':
                        return '@SonataAdmin/CRUD/show_choice.html.twig';
                    case 'array':
                        return '@SonataAdmin/CRUD/show_array.html.twig';
                    case 'trans':
                        return '@SonataAdmin/CRUD/show_trans.html.twig';
                    case 'url':
                        return '@SonataAdmin/CRUD/show_url.html.twig';
                    case 'html':
                        return '@SonataAdmin/CRUD/show_html.html.twig';
                    default:
                        return false;
                }
            }));

        $this->assertSame(
                $this->removeExtraWhitespace($expected),
                $this->removeExtraWhitespace(
                    $this->twigExtension->renderViewElement(
                        $this->environment,
                        $this->fieldDescription,
                        $this->object
                    )
                )
            );
    }

    public function getRenderViewElementTests()
    {
        return [
            ['<th>Data</th> <td>Example</td>', 'string', 'Example', ['safe' => false]],
            ['<th>Data</th> <td>Example</td>', 'text', 'Example', ['safe' => false]],
            ['<th>Data</th> <td>Example</td>', 'textarea', 'Example', ['safe' => false]],
            [
                '<th>Data</th> <td>December 24, 2013 10:11</td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), [],
            ],
            [
                '<th>Data</th> <td>24.12.2013 10:11:12</td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<th>Data</th> <td>December 24, 2013</td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<th>Data</th> <td>24.12.2013</td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y'],
            ],
            [
                '<th>Data</th> <td>10:11:12</td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            ['<th>Data</th> <td>10.746135</td>', 'number', 10.746135, ['safe' => false]],
            ['<th>Data</th> <td>5678</td>', 'integer', 5678, ['safe' => false]],
            ['<th>Data</th> <td> 1074.6135 % </td>', 'percent', 10.746135, []],
            ['<th>Data</th> <td> EUR 10.746135 </td>', 'currency', 10.746135, ['currency' => 'EUR']],
            ['<th>Data</th> <td> GBP 51.23456 </td>', 'currency', 51.23456, ['currency' => 'GBP']],
            [
                '<th>Data</th> <td> [1 => First] <br> [2 => Second] </td>',
                'array',
                [1 => 'First', 2 => 'Second'],
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td> [1 => First] [2 => Second] </td>',
                'array',
                [1 => 'First', 2 => 'Second'],
                ['safe' => false, 'inline' => true],
            ],
            [
                '<th>Data</th> <td><span class="label label-success">yes</span></td>',
                'boolean',
                true,
                [],
            ],
            [
                '<th>Data</th> <td><span class="label label-danger">yes</span></td>',
                'boolean',
                true,
                ['inverse' => true],
            ],
            ['<th>Data</th> <td><span class="label label-danger">no</span></td>', 'boolean', false, []],
            [
                '<th>Data</th> <td><span class="label label-success">no</span></td>',
                'boolean',
                false,
                ['inverse' => true],
            ],
            [
                '<th>Data</th> <td> Delete </td>',
                'trans',
                'action_delete',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle'],
            ],
            ['<th>Data</th> <td>Status1</td>', 'choice', 'Status1', ['safe' => false]],
            [
                '<th>Data</th> <td>Alias1</td>',
                'choice',
                'Status1',
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<th>Data</th> <td>NoValidKeyInChoices</td>',
                'choice',
                'NoValidKeyInChoices',
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<th>Data</th> <td>Delete</td>',
                'choice',
                'Foo',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<th>Data</th> <td>NoValidKeyInChoices</td>',
                'choice',
                ['NoValidKeyInChoices'],
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>NoValidKeyInChoices, Alias2</td>',
                'choice',
                ['NoValidKeyInChoices', 'Status2'],
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>Alias1, Alias3</td>',
                'choice',
                ['Status1', 'Status3'],
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>Alias1 | Alias3</td>',
                'choice',
                ['Status1', 'Status3'], ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true, 'delimiter' => ' | '],
            ],
            [
                '<th>Data</th> <td>Delete, Alias3</td>',
                'choice',
                ['Foo', 'Status3'],
                ['safe' => false, 'catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td><b>Alias1</b>, <b>Alias3</b></td>',
                'choice',
                ['Status1', 'Status3'],
                ['safe' => true, 'choices' => [
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>&lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt;</td>',
                'choice',
                ['Status1', 'Status3'],
                ['safe' => false, 'choices' => [
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>',
                'url',
                'http://example.com',
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com" target="_blank">http://example.com</a></td>',
                'url',
                'http://example.com',
                ['safe' => false, 'attributes' => ['target' => '_blank']],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com" target="_blank" class="fooLink">http://example.com</a></td>',
                'url',
                'http://example.com',
                ['safe' => false, 'attributes' => ['target' => '_blank', 'class' => 'fooLink']],
            ],
            [
                '<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>',
                'url',
                'https://example.com',
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">example.com</a></td>',
                'url',
                'http://example.com',
                ['safe' => false, 'hide_protocol' => true],
            ],
            [
                '<th>Data</th> <td><a href="https://example.com">example.com</a></td>',
                'url',
                'https://example.com',
                ['safe' => false, 'hide_protocol' => true],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>',
                'url',
                'http://example.com',
                ['safe' => false, 'hide_protocol' => false],
            ],
            [
                '<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>',
                'url',
                'https://example.com',
                ['safe' => false,
                'hide_protocol' => false, ],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a></td>',
                'url',
                '<b>Foo</b>',
                ['safe' => false, 'url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com"><b>Foo</b></a></td>',
                'url',
                '<b>Foo</b>',
                ['safe' => true, 'url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td><a href="/foo">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'route' => ['name' => 'sonata_admin_foo']],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo',
                    'absolute' => true,
                ]],
            ],
            [
                '<th>Data</th> <td><a href="/foo">foo/bar?a=b&amp;c=123456789</a></td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                [
                    'safe' => false,
                    'route' => ['name' => 'sonata_admin_foo'],
                    'hide_protocol' => true,
                ],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a></td>',
                'url',
                'http://foo/bar?a=b&c=123456789',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo',
                    'absolute' => true,
                ], 'hide_protocol' => true],
            ],
            [
                '<th>Data</th> <td><a href="/foo/abcd/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo_param',
                    'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                ]],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo_param',
                    'absolute' => true,
                    'parameters' => [
                        'param1' => 'abcd',
                        'param2' => 'efgh',
                        'param3' => 'ijkl',
                    ],
                ]],
            ],
            [
                '<th>Data</th> <td><a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo_object',
                    'parameters' => [
                        'param1' => 'abcd',
                        'param2' => 'efgh',
                        'param3' => 'ijkl',
                    ],
                    'identifier_parameter_name' => 'barId',
                ]],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a></td>',
                'url',
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo_object',
                    'absolute' => true,
                    'parameters' => [
                        'param1' => 'abcd',
                        'param2' => 'efgh',
                        'param3' => 'ijkl',
                    ],
                    'identifier_parameter_name' => 'barId',
                ]],
            ],
            [
                '<th>Data</th> <td> &nbsp;</td>',
                'email',
                null,
                [],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                [],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme', 'body' => 'Message Body']).'">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                ['subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme']).'">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                ['subject' => 'Main Theme'],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['body' => 'Message Body']).'">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                ['body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme'],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                ['as_string' => true, 'body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com">admin@admin.com</a></td>',
                'email',
                'admin@admin.com',
                ['as_string' => false],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                'email',
                'admin@admin.com',
                ['as_string' => true],
            ],
            [
                '<th>Data</th> <td><p><strong>Creating a Template for the Field</strong> and form</p> </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [],
            ],
            [
                '<th>Data</th> <td>Creating a Template for the Field and form </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['strip' => true],
            ],
            [
                '<th>Data</th> <td> Creating a Template for the Fi... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => true],
            ],
            [
                '<th>Data</th> <td> Creating a... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['length' => 10]],
            ],
            [
                '<th>Data</th> <td> Creating a Template for the Field... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['preserve' => true]],
            ],
            [
                '<th>Data</th> <td> Creating a Template for the Fi etc. </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['separator' => ' etc.']],
            ],
            [
                '<th>Data</th> <td> Creating a Template for[...] </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [
                    'truncate' => [
                        'length' => 20,
                        'preserve' => true,
                        'separator' => '[...]',
                    ],
                ],
            ],

            // NoValueException
            ['<th>Data</th> <td></td>', 'string', new NoValueException(), ['safe' => false]],
            ['<th>Data</th> <td></td>', 'text', new NoValueException(), ['safe' => false]],
            ['<th>Data</th> <td></td>', 'textarea', new NoValueException(), ['safe' => false]],
            ['<th>Data</th> <td>&nbsp;</td>', 'datetime', new NoValueException(), []],
            [
                '<th>Data</th> <td>&nbsp;</td>',
                'datetime',
                new NoValueException(),
                ['format' => 'd.m.Y H:i:s'],
            ],
            ['<th>Data</th> <td>&nbsp;</td>', 'date', new NoValueException(), []],
            ['<th>Data</th> <td>&nbsp;</td>', 'date', new NoValueException(), ['format' => 'd.m.Y']],
            ['<th>Data</th> <td>&nbsp;</td>', 'time', new NoValueException(), []],
            ['<th>Data</th> <td></td>', 'number', new NoValueException(), ['safe' => false]],
            ['<th>Data</th> <td></td>', 'integer', new NoValueException(), ['safe' => false]],
            ['<th>Data</th> <td> 0 % </td>', 'percent', new NoValueException(), []],
            ['<th>Data</th> <td> </td>', 'currency', new NoValueException(), ['currency' => 'EUR']],
            ['<th>Data</th> <td> </td>', 'currency', new NoValueException(), ['currency' => 'GBP']],
            ['<th>Data</th> <td> </td>', 'array', new NoValueException(), ['safe' => false]],
            [
                '<th>Data</th> <td><span class="label label-danger">no</span></td>',
                'boolean',
                new NoValueException(),
                [],
            ],
            [
                '<th>Data</th> <td> </td>',
                'trans',
                new NoValueException(),
                ['safe' => false, 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<th>Data</th> <td></td>',
                'choice',
                new NoValueException(),
                ['safe' => false, 'choices' => []],
            ],
            [
                '<th>Data</th> <td></td>',
                'choice',
                new NoValueException(),
                ['safe' => false, 'choices' => [], 'multiple' => true],
            ],
            ['<th>Data</th> <td>&nbsp;</td>', 'url', new NoValueException(), []],
            [
                '<th>Data</th> <td>&nbsp;</td>',
                'url',
                new NoValueException(),
                ['url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td>&nbsp;</td>',
                'url',
                new NoValueException(),
                ['route' => ['name' => 'sonata_admin_foo']],
            ],

            [
                <<<'EOT'
<th>Data</th> <td><div
        class="sonata-readmore"
        data-readmore-height="40"
        data-readmore-more="Read more"
        data-readmore-less="Close">
            A very long string
</div></td>
EOT
                ,
                'text',
                ' A very long string ',
                [
                    'collapse' => true,
                    'safe' => false,
                ],
            ],
            [
                <<<'EOT'
<th>Data</th> <td><div
        class="sonata-readmore"
        data-readmore-height="10"
        data-readmore-more="More"
        data-readmore-less="Less">
            A very long string
</div></td>
EOT
                ,
                'text',
                ' A very long string ',
                [
                    'collapse' => [
                        'height' => 10,
                        'more' => 'More',
                        'less' => 'Less',
                    ],
                    'safe' => false,
                ],
            ],
        ];
    }

    public function testGetValueFromFieldDescription()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('test123'));

        $this->assertSame('test123', $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }

    public function testGetValueFromFieldDescriptionWithRemoveLoopException()
    {
        $object = $this->createMock(\ArrayAccess::class);
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $this->expectException(\RuntimeException::class, 'remove the loop requirement');

        $this->assertSame(
            'anything',
            $this->twigExtension->getValueFromFieldDescription($object, $fieldDescription, ['loop' => true])
        );
    }

    public function testGetValueFromFieldDescriptionWithNoValueException()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $fieldDescription->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(function () {
                throw new NoValueException();
            }));

        $fieldDescription->expects($this->any())
            ->method('getAssociationAdmin')
            ->will($this->returnValue(null));

        $this->assertNull($this->twigExtension->getValueFromFieldDescription($object, $fieldDescription));
    }

    public function testGetValueFromFieldDescriptionWithNoValueExceptionNewAdminInstance()
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

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
            ->will($this->returnValue('@SonataAdmin/CRUD/base_list_field.html.twig'));

        $this->fieldDescription->expects($this->any())
            ->method('getFieldName')
            ->will($this->returnValue('fd_name'));

        $this->environment->disableDebug();

        $parameters = [
            'admin' => $this->admin,
            'value' => 'foo',
            'field_description' => $this->fieldDescription,
            'object' => $this->object,
        ];

        $template = $this->environment->loadTemplate('@SonataAdmin/CRUD/base_list_field.html.twig');

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
    template: @SonataAdmin/CRUD/base_list_field.html.twig
    compiled template: @SonataAdmin/CRUD/base_list_field.html.twig
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
                if ('associated_property' == $value) {
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
                if ('associated_tostring' == $value) {
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
                if ('associated_property' == $value) {
                    return $default;
                }

                if ('associated_tostring' == $value) {
                    return 'customToString';
                }
            }));

        $element = $this->getMockBuilder('stdClass')
            ->setMethods(['customToString'])
            ->getMock();
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
                if ('associated_tostring' == $value) {
                    return 'nonExistedMethod';
                }
            }));

        $element = new \stdClass();
        $this->expectException(\RuntimeException::class, 'You must define an `associated_property` option or create a `stdClass::__toString');

        $this->twigExtension->renderRelationElement($element, $this->fieldDescription);
    }

    public function testRenderRelationElementWithPropertyPath()
    {
        $this->fieldDescription->expects($this->exactly(1))
            ->method('getOption')

            ->will($this->returnCallback(function ($value, $default = null) {
                if ('associated_property' == $value) {
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
                if ('associated_property' == $value) {
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
        $this->pool->setAdminServiceIds(['sonata_admin_foo_service']);
        $this->pool->setAdminClasses(['stdClass' => ['sonata_admin_foo_service']]);

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
        $this->pool->setAdminServiceIds([
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ]);
        $this->pool->setAdminClasses(['stdClass' => [
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ]]);

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
        $this->pool->setAdminServiceIds(['sonata_admin_foo_service', 'sonata_admin_bar_service']);
        $this->pool->setAdminClasses(['stdClass' => [
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ]]);

        $this->admin->expects($this->never())
            ->method('getUrlsafeIdentifier');

        $this->adminBar->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->with($this->equalTo($entity))
            ->will($this->returnValue(1234567));

        $this->assertSame(1234567, $this->twigExtension->getUrlsafeIdentifier($entity, $this->adminBar));
    }

    public function xEditableChoicesProvider()
    {
        return [
            'needs processing' => [['Status1' => 'Alias1', 'Status2' => 'Alias2']],
            'already processed' => [[
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ]],
        ];
    }

    /**
     * @dataProvider xEditablechoicesProvider
     */
    public function testGetXEditableChoicesIsIdempotent(array $input)
    {
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->withConsecutive(
                ['choices', []],
                ['catalogue']
            )
            ->will($this->onConsecutiveCalls(
                $input,
                'MyCatalogue'
            ));

        $this->assertSame(
            [
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ],
            $this->twigExtension->getXEditableChoices($fieldDescription)
        );
    }

    /**
     * This method generates url part for Twig layout.
     *
     * @param array $url
     *
     * @return string
     */
    private function buildTwigLikeUrl($url)
    {
        return htmlspecialchars(http_build_query($url, '', '&', PHP_QUERY_RFC3986));
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
