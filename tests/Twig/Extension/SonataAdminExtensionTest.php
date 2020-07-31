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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToString;
use Sonata\AdminBundle\Tests\Fixtures\StubFilesystemLoader;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;

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
     * @var Environment
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

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $securityChecker;

    protected function setUp(): void
    {
        date_default_timezone_set('Europe/London');

        $container = $this->createMock(ContainerInterface::class);

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
        $translator = new Translator('en');
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource(
            'xlf',
            sprintf('%s/../../../src/Resources/translations/SonataAdminBundle.en.xliff', __DIR__),
            'en',
            'SonataAdminBundle'
        );

        $this->translator = $translator;

        $this->templateRegistry = new TemplateRegistry();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->container
            ->method('get')
            ->with('sonata_admin_foo_service.template_registry')
            ->willReturn($this->templateRegistry);

        $this->securityChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->securityChecker->method('isGranted')->willReturn(true);

        $this->twigExtension = new SonataAdminExtension(
            $this->pool,
            $this->logger,
            $this->translator,
            $this->container,
            $this->securityChecker
        );
        $this->twigExtension->setXEditableTypeMapping($this->xEditableTypeMapping);

        $request = $this->createMock(Request::class);
        $request->method('get')->with('_sonata_admin')->willReturn('sonata_admin_foo_service');

        $loader = new FilesystemLoader([
            __DIR__.'/../../../src/Resources/views/CRUD',
            __DIR__.'/../../Fixtures/Resources/views/CRUD',
        ]);
        $loader->addPath(__DIR__.'/../../../src/Resources/views/', 'SonataAdmin');
        $loader->addPath(__DIR__.'/../../Fixtures/Resources/views/', 'App');

        $this->environment = new Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $this->environment->addExtension($this->twigExtension);
        $this->environment->addExtension(new TranslationExtension($translator));
        $this->environment->addExtension(new FakeTemplateRegistryExtension());

        // routing extension
        $xmlFileLoader = new XmlFileLoader(new FileLocator([sprintf('%s/../../../src/Resources/config/routing', __DIR__)]));
        $routeCollection = $xmlFileLoader->load('sonata_admin.xml');

        $xmlFileLoader = new XmlFileLoader(new FileLocator([sprintf('%s/../../Fixtures/Resources/config/routing', __DIR__)]));
        $testRouteCollection = $xmlFileLoader->load('routing.xml');

        $routeCollection->addCollection($testRouteCollection);
        $requestContext = new RequestContext();
        $urlGenerator = new UrlGenerator($routeCollection, $requestContext);
        $this->environment->addExtension(new RoutingExtension($urlGenerator));
        $this->environment->addExtension(new StringExtension());

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->createMock(AbstractAdmin::class);

        $this->admin
            ->method('getCode')
            ->willReturn('sonata_admin_foo_service');

        $this->admin
            ->method('id')
            ->with($this->equalTo($this->object))
            ->willReturn('12345');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($this->object))
            ->willReturn('12345');

        $this->admin
            ->method('trans')
            ->willReturnCallback(static function ($id, $parameters = [], $domain = null) use ($translator) {
                return $translator->trans($id, $parameters, $domain);
            });

        $this->adminBar = $this->createMock(AbstractAdmin::class);
        $this->adminBar
            ->method('hasAccess')
            ->willReturn(true);
        $this->adminBar
            ->method('getNormalizedIdentifier')
            ->with($this->equalTo($this->object))
            ->willReturn('12345');

        $container
            ->method('get')
            ->willReturnCallback(function (string $id) {
                if ('sonata_admin_foo_service' === $id) {
                    return $this->admin;
                }

                if ('sonata_admin_bar_service' === $id) {
                    return $this->adminBar;
                }
            });

        // initialize field description
        $this->fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $this->fieldDescription
            ->method('getName')
            ->willReturn('fd_name');

        $this->fieldDescription
            ->method('getAdmin')
            ->willReturn($this->admin);

        $this->fieldDescription
            ->method('getLabel')
            ->willReturn('Data');
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
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        December 24, 2013 10:11
                    </time>
                </td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        December 24, 2013 18:11
                    </time>
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
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        24.12.2013 10:11:12
                    </time>
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
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        24.12.2013 18:11:12
                    </time>
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
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345">
                    <time datetime="2013-12-24" title="2013-12-24">
                        December 24, 2013
                    </time>
                </td>',
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
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345">
                    <time datetime="2013-12-24" title="2013-12-24">
                        24.12.2013
                    </time>
                </td>',
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
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345">
                    <time datetime="10:11:12+00:00" title="10:11:12+00:00">
                        10:11:12
                    </time>
                </td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345">
                    <time datetime="10:11:12+00:00" title="10:11:12+00:00">
                        18:11:12
                    </time>
                </td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
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
                    [1&nbsp;=>&nbsp;First, 2&nbsp;=>&nbsp;Second]
                </td>',
                'array',
                [1 => 'First', 2 => 'Second'],
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> [] </td>',
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
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
                Creating a Template for the...
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345"> Creatin... </td>',
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
                ['truncate' => ['cut' => false]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for t etc.
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['ellipsis' => ' etc.']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template[...]
                </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [
                    'truncate' => [
                        'length' => 20,
                        'cut' => false,
                        'ellipsis' => '[...]',
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
            [
                <<<'EOT'
<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
    <span
        class="x-editable"
        data-type="checklist"
        data-value="[&quot;Status1&quot;,&quot;Status2&quot;]"
        data-title="Data"
        data-pk="12345"
        data-url="/core/set-object-field-value?context=list&amp;field=fd_name&amp;objectId=12345&amp;code=sonata_admin_foo_service"
        data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Delete&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
         Delete, Alias2
    </span>
</td>
EOT
                ,
                'choice',
                [
                    'Status1',
                    'Status2',
                ],
                [
                    'editable' => true,
                    'multiple' => true,
                    'catalogue' => 'SonataAdminBundle',
                    'choices' => [
                        'Status1' => 'action_delete',
                        'Status2' => 'Alias2',
                        'Status3' => 'Alias3',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getRenderViewElementTests
     */
    public function testRenderViewElement(string $expected, string $type, $value, array $options): void
    {
        $this->fieldDescription
            ->method('getValue')
            ->willReturn($value);

        $this->fieldDescription
            ->method('getType')
            ->willReturn($type);

        $this->fieldDescription
            ->method('getOptions')
            ->willReturn($options);

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturnCallback(static function () use ($type): ?string {
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
                        return null;
                }
            });

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
                '<th>Data</th> <td><time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00"> December 24, 2013 10:11 </time></td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), [],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00"> 24.12.2013 10:11:12 </time></td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00"> December 24, 2013 18:11 </time></td>',
                'datetime',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24" title="2013-12-24"> December 24, 2013 </time></td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24" title="2013-12-24"> 24.12.2013 </time></td>',
                'date',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y'],
            ],
            [
                '<th>Data</th> <td><time datetime="10:11:12+00:00" title="10:11:12+00:00"> 10:11:12 </time></td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<th>Data</th> <td><time datetime="10:11:12+00:00" title="10:11:12+00:00"> 18:11:12 </time></td>',
                'time',
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            ['<th>Data</th> <td>10.746135</td>', 'number', 10.746135, ['safe' => false]],
            ['<th>Data</th> <td>5678</td>', 'integer', 5678, ['safe' => false]],
            ['<th>Data</th> <td> 1074.6135 % </td>', 'percent', 10.746135, []],
            ['<th>Data</th> <td> EUR 10.746135 </td>', 'currency', 10.746135, ['currency' => 'EUR']],
            ['<th>Data</th> <td> GBP 51.23456 </td>', 'currency', 51.23456, ['currency' => 'GBP']],
            [
                '<th>Data</th> <td> <ul><li>1&nbsp;=>&nbsp;First</li><li>2&nbsp;=>&nbsp;Second</li></ul> </td>',
                'array',
                [1 => 'First', 2 => 'Second'],
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td> [1&nbsp;=>&nbsp;First, 2&nbsp;=>&nbsp;Second] </td>',
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
            [
                '<th>Data</th> <td> Delete </td>',
                'trans',
                'delete',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle', 'format' => 'action_%s'],
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
                '<th>Data</th> <td> Creating a Template for the... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => true],
            ],
            [
                '<th>Data</th> <td> Creatin... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['length' => 10]],
            ],
            [
                '<th>Data</th> <td> Creating a Template for the Field... </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['cut' => false]],
            ],
            [
                '<th>Data</th> <td> Creating a Template for t etc. </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['ellipsis' => ' etc.']],
            ],
            [
                '<th>Data</th> <td> Creating a Template[...] </td>',
                'html',
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [
                    'truncate' => [
                        'length' => 20,
                        'cut' => false,
                        'ellipsis' => '[...]',
                    ],
                ],
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

    public function getRenderViewElementWithNoValueTests(): iterable
    {
        return [
            // NoValueException
            ['<th>Data</th> <td></td>', 'string', ['safe' => false]],
            ['<th>Data</th> <td></td>', 'text', ['safe' => false]],
            ['<th>Data</th> <td></td>', 'textarea', ['safe' => false]],
            ['<th>Data</th> <td>&nbsp;</td>', 'datetime', []],
            [
                '<th>Data</th> <td>&nbsp;</td>',
                'datetime',
                ['format' => 'd.m.Y H:i:s'],
            ],
            ['<th>Data</th> <td>&nbsp;</td>', 'date', []],
            ['<th>Data</th> <td>&nbsp;</td>', 'date', ['format' => 'd.m.Y']],
            ['<th>Data</th> <td>&nbsp;</td>', 'time', []],
            ['<th>Data</th> <td></td>', 'number', ['safe' => false]],
            ['<th>Data</th> <td></td>', 'integer', ['safe' => false]],
            ['<th>Data</th> <td> 0 % </td>', 'percent', []],
            ['<th>Data</th> <td> </td>', 'currency', ['currency' => 'EUR']],
            ['<th>Data</th> <td> </td>', 'currency', ['currency' => 'GBP']],
            ['<th>Data</th> <td> <ul></ul> </td>', 'array', ['safe' => false]],
            [
                '<th>Data</th> <td><span class="label label-danger">no</span></td>',
                'boolean',
                [],
            ],
            [
                '<th>Data</th> <td> </td>',
                'trans',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<th>Data</th> <td></td>',
                'choice',
                ['safe' => false, 'choices' => []],
            ],
            [
                '<th>Data</th> <td></td>',
                'choice',
                ['safe' => false, 'choices' => [], 'multiple' => true],
            ],
            ['<th>Data</th> <td>&nbsp;</td>', 'url', []],
            [
                '<th>Data</th> <td>&nbsp;</td>',
                'url',
                ['url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td>&nbsp;</td>',
                'url',
                ['route' => ['name' => 'sonata_admin_foo']],
            ],
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     *
     * @dataProvider getDeprecatedTextExtensionItems
     *
     * @expectedDeprecation The "truncate.preserve" option is deprecated since sonata-project/admin-bundle 3.65, to be removed in 4.0. Use "truncate.cut" instead. ("@SonataAdmin/CRUD/show_html.html.twig" at line %d).
     *
     * @expectedDeprecation The "truncate.separator" option is deprecated since sonata-project/admin-bundle 3.65, to be removed in 4.0. Use "truncate.ellipsis" instead. ("@SonataAdmin/CRUD/show_html.html.twig" at line %d).
     */
    public function testDeprecatedTextExtension(string $expected, string $type, $value, array $options): void
    {
        $loader = new StubFilesystemLoader([
            sprintf('%s/../../../src/Resources/views/CRUD', __DIR__),
        ]);
        $loader->addPath(sprintf('%s/../../../src/Resources/views/', __DIR__), 'SonataAdmin');
        $environment = new Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $environment->addExtension($this->twigExtension);
        $environment->addExtension(new TranslationExtension($this->translator));
        $environment->addExtension(new StringExtension());

        $this->fieldDescription
            ->method('getValue')
            ->willReturn($value);

        $this->fieldDescription
            ->method('getType')
            ->willReturn($type);

        $this->fieldDescription
            ->method('getOptions')
            ->willReturn($options);

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturn('@SonataAdmin/CRUD/show_html.html.twig');

        $this->assertSame(
            $this->removeExtraWhitespace($expected),
            $this->removeExtraWhitespace(
                $this->twigExtension->renderViewElement(
                    $environment,
                    $this->fieldDescription,
                    $this->object
                )
            )
        );
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getDeprecatedTextExtensionItems(): iterable
    {
        yield 'default_separator' => [
            '<th>Data</th> <td> Creating a Template for the Field... </td>',
            'html',
            '<p><strong>Creating a Template for the Field</strong> and form</p>',
            ['truncate' => ['preserve' => true, 'separator' => '...']],
        ];

        yield 'custom_length' => [
            '<th>Data</th> <td> Creating a Template[...] </td>',
            'html',
            '<p><strong>Creating a Template for the Field</strong> and form</p>',
            [
                'truncate' => [
                    'length' => 20,
                    'preserve' => true,
                    'separator' => '[...]',
                ],
            ],
        ];
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetValueFromFieldDescription(): void
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $fieldDescription
            ->method('getValue')
            ->willReturn('test123');

        $this->assertSame(
            'test123',
            $this->getMethodAsPublic('getValueFromFieldDescription')->invoke(
                $this->twigExtension,
                $object,
                $fieldDescription
            )
        );
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetValueFromFieldDescriptionWithRemoveLoopException(): void
    {
        $object = $this->createMock(\ArrayAccess::class);
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('remove the loop requirement');

        $this->assertSame(
            'anything',
            $this->getMethodAsPublic('getValueFromFieldDescription')->invoke(
                $this->twigExtension,
                $object,
                $fieldDescription,
                ['loop' => true]
            )
        );
    }

    public function testGetValueFromFieldDescriptionWithNoValueException(): void
    {
        $this->expectException(NoValueException::class);
        $object = new \stdClass();
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $fieldDescription
            ->method('getValue')
            ->willReturnCallback(static function (): void {
                throw new NoValueException();
            });

        $fieldDescription
            ->method('getAssociationAdmin')
            ->willReturn(null);

        $this->assertNull(
            $this->getMethodAsPublic('getValueFromFieldDescription')->invoke(
                $this->twigExtension,
                $object,
                $fieldDescription
            )
        );
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetValueFromFieldDescriptionWithNoValueExceptionNewAdminInstance(): void
    {
        $object = new \stdClass();
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);

        $fieldDescription
            ->method('getValue')
            ->willReturnCallback(static function (): void {
                throw new NoValueException();
            });

        $fieldDescription
            ->method('getAssociationAdmin')
            ->willReturn($this->admin);

        $newInstance = new \stdClass();

        $this->admin->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($newInstance);

        $this->assertSame(
            $newInstance,
            $this->getMethodAsPublic('getValueFromFieldDescription')->invoke(
                $this->twigExtension,
                $object,
                $fieldDescription
            )
        );
    }

    public function testRenderRelationElementNoObject(): void
    {
        $this->assertSame('foo', $this->twigExtension->renderRelationElement('foo', $this->fieldDescription));
    }

    public function testRenderRelationElementToString(): void
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->willReturnCallback(static function ($value, $default = null) {
                if ('associated_property' === $value) {
                    return $default;
                }
            });

        $element = new FooToString();
        $this->assertSame('salut', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @group legacy
     */
    public function testDeprecatedRelationElementToString(): void
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->willReturnCallback(static function ($value, $default = null) {
                if ('associated_tostring' === $value) {
                    return '__toString';
                }
            });

        $element = new FooToString();
        $this->assertSame(
            'salut',
            $this->twigExtension->renderRelationElement($element, $this->fieldDescription)
        );
    }

    /**
     * @group legacy
     */
    public function testRenderRelationElementCustomToString(): void
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')
            ->willReturnCallback(static function ($value, $default = null) {
                if ('associated_property' === $value) {
                    return $default;
                }

                if ('associated_tostring' === $value) {
                    return 'customToString';
                }
            });

        $element = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['customToString'])
            ->getMock();
        $element
            ->method('customToString')
            ->willReturn('fooBar');

        $this->assertSame('fooBar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @group legacy
     */
    public function testRenderRelationElementMethodNotExist(): void
    {
        $this->fieldDescription->expects($this->exactly(2))
            ->method('getOption')

            ->willReturnCallback(static function ($value, $default = null) {
                if ('associated_tostring' === $value) {
                    return 'nonExistedMethod';
                }
            });

        $element = new \stdClass();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You must define an `associated_property` option or create a `stdClass::__toString');

        $this->twigExtension->renderRelationElement($element, $this->fieldDescription);
    }

    public function testRenderRelationElementWithPropertyPath(): void
    {
        $this->fieldDescription->expects($this->once())
            ->method('getOption')

            ->willReturnCallback(static function ($value, $default = null) {
                if ('associated_property' === $value) {
                    return 'foo';
                }
            });

        $element = new \stdClass();
        $element->foo = 'bar';

        $this->assertSame('bar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    public function testRenderRelationElementWithClosure(): void
    {
        $this->fieldDescription->expects($this->once())
            ->method('getOption')

            ->willReturnCallback(static function ($value, $default = null) {
                if ('associated_property' === $value) {
                    return static function ($element): string {
                        return sprintf('closure %s', $element->foo);
                    };
                }
            });

        $element = new \stdClass();
        $element->foo = 'bar';

        $this->assertSame(
            'closure bar',
            $this->twigExtension->renderRelationElement($element, $this->fieldDescription)
        );
    }

    public function testGetUrlsafeIdentifier(): void
    {
        $model = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds(['sonata_admin_foo_service']);
        $this->pool->setAdminClasses([\stdClass::class => ['sonata_admin_foo_service']]);

        $this->admin->expects($this->once())
            ->method('getUrlSafeIdentifier')
            ->with($this->equalTo($model))
            ->willReturn('1234567');

        $this->assertSame('1234567', $this->twigExtension->getUrlSafeIdentifier($model));
    }

    public function testGetUrlsafeIdentifier_GivenAdmin_Foo(): void
    {
        $model = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds([
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ]);
        $this->pool->setAdminClasses([\stdClass::class => [
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ]]);

        $this->admin->expects($this->once())
            ->method('getUrlSafeIdentifier')
            ->with($this->equalTo($model))
            ->willReturn('1234567');

        $this->adminBar->expects($this->never())
            ->method('getUrlSafeIdentifier');

        $this->assertSame('1234567', $this->twigExtension->getUrlSafeIdentifier($model, $this->admin));
    }

    public function testGetUrlsafeIdentifier_GivenAdmin_Bar(): void
    {
        $model = new \stdClass();

        // set admin to pool
        $this->pool->setAdminServiceIds(['sonata_admin_foo_service', 'sonata_admin_bar_service']);
        $this->pool->setAdminClasses([\stdClass::class => [
            'sonata_admin_foo_service',
            'sonata_admin_bar_service',
        ]]);

        $this->admin->expects($this->never())
            ->method('getUrlSafeIdentifier');

        $this->adminBar->expects($this->once())
            ->method('getUrlSafeIdentifier')
            ->with($this->equalTo($model))
            ->willReturn('1234567');

        $this->assertSame('1234567', $this->twigExtension->getUrlSafeIdentifier($model, $this->adminBar));
    }

    public function xEditableChoicesProvider()
    {
        return [
            'needs processing' => [
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2']],
                [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
            'already processed' => [
                ['choices' => [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ]],
                [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
            'not required' => [
                [
                    'required' => false,
                    'choices' => ['' => '', 'Status1' => 'Alias1', 'Status2' => 'Alias2'],
                ],
                [
                    ['value' => '', 'text' => ''],
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
            'not required multiple' => [
                [
                    'required' => false,
                    'multiple' => true,
                    'choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2'],
                ],
                [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider xEditablechoicesProvider
     */
    public function testGetXEditableChoicesIsIdempotent(array $options, array $expectedChoices): void
    {
        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getOption')
            ->withConsecutive(
                ['choices', []],
                ['catalogue'],
                ['required'],
                ['multiple']
            )
            ->will($this->onConsecutiveCalls(
                $options['choices'],
                'MyCatalogue',
                $options['multiple'] ?? null
            ));

        $this->assertSame($expectedChoices, $this->twigExtension->getXEditableChoices($fieldDescription));
    }

    public function select2LocalesProvider()
    {
        return [
            ['ar', 'ar'],
            ['az', 'az'],
            ['bg', 'bg'],
            ['ca', 'ca'],
            ['cs', 'cs'],
            ['da', 'da'],
            ['de', 'de'],
            ['el', 'el'],
            [null, 'en'],
            ['es', 'es'],
            ['et', 'et'],
            ['eu', 'eu'],
            ['fa', 'fa'],
            ['fi', 'fi'],
            ['fr', 'fr'],
            ['gl', 'gl'],
            ['he', 'he'],
            ['hr', 'hr'],
            ['hu', 'hu'],
            ['id', 'id'],
            ['is', 'is'],
            ['it', 'it'],
            ['ja', 'ja'],
            ['ka', 'ka'],
            ['ko', 'ko'],
            ['lt', 'lt'],
            ['lv', 'lv'],
            ['mk', 'mk'],
            ['ms', 'ms'],
            ['nb', 'nb'],
            ['nl', 'nl'],
            ['pl', 'pl'],
            ['pt-PT', 'pt'],
            ['pt-BR', 'pt-BR'],
            ['pt-PT', 'pt-PT'],
            ['ro', 'ro'],
            ['rs', 'rs'],
            ['ru', 'ru'],
            ['sk', 'sk'],
            ['sv', 'sv'],
            ['th', 'th'],
            ['tr', 'tr'],
            ['ug-CN', 'ug'],
            ['ug-CN', 'ug-CN'],
            ['uk', 'uk'],
            ['vi', 'vi'],
            ['zh-CN', 'zh'],
            ['zh-CN', 'zh-CN'],
            ['zh-TW', 'zh-TW'],
        ];
    }

    /**
     * @dataProvider select2LocalesProvider
     */
    public function testCanonicalizedLocaleForSelect2(?string $expected, string $original): void
    {
        $this->assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForSelect2($this->mockExtensionContext($original)));
    }

    public function momentLocalesProvider(): array
    {
        return [
            ['af', 'af'],
            ['ar-dz', 'ar-dz'],
            ['ar', 'ar'],
            ['ar-ly', 'ar-ly'],
            ['ar-ma', 'ar-ma'],
            ['ar-sa', 'ar-sa'],
            ['ar-tn', 'ar-tn'],
            ['az', 'az'],
            ['be', 'be'],
            ['bg', 'bg'],
            ['bn', 'bn'],
            ['bo', 'bo'],
            ['br', 'br'],
            ['bs', 'bs'],
            ['ca', 'ca'],
            ['cs', 'cs'],
            ['cv', 'cv'],
            ['cy', 'cy'],
            ['da', 'da'],
            ['de-at', 'de-at'],
            ['de', 'de'],
            ['de', 'de-de'],
            ['dv', 'dv'],
            ['el', 'el'],
            [null, 'en'],
            [null, 'en-us'],
            ['en-au', 'en-au'],
            ['en-ca', 'en-ca'],
            ['en-gb', 'en-gb'],
            ['en-ie', 'en-ie'],
            ['en-nz', 'en-nz'],
            ['eo', 'eo'],
            ['es-do', 'es-do'],
            ['es', 'es-ar'],
            ['es', 'es-mx'],
            ['es', 'es'],
            ['et', 'et'],
            ['eu', 'eu'],
            ['fa', 'fa'],
            ['fi', 'fi'],
            ['fo', 'fo'],
            ['fr-ca', 'fr-ca'],
            ['fr-ch', 'fr-ch'],
            ['fr', 'fr-fr'],
            ['fr', 'fr'],
            ['fy', 'fy'],
            ['gd', 'gd'],
            ['gl', 'gl'],
            ['he', 'he'],
            ['hi', 'hi'],
            ['hr', 'hr'],
            ['hu', 'hu'],
            ['hy-am', 'hy-am'],
            ['id', 'id'],
            ['is', 'is'],
            ['it', 'it'],
            ['ja', 'ja'],
            ['jv', 'jv'],
            ['ka', 'ka'],
            ['kk', 'kk'],
            ['km', 'km'],
            ['ko', 'ko'],
            ['ky', 'ky'],
            ['lb', 'lb'],
            ['lo', 'lo'],
            ['lt', 'lt'],
            ['lv', 'lv'],
            ['me', 'me'],
            ['mi', 'mi'],
            ['mk', 'mk'],
            ['ml', 'ml'],
            ['mr', 'mr'],
            ['ms', 'ms'],
            ['ms-my', 'ms-my'],
            ['my', 'my'],
            ['nb', 'nb'],
            ['ne', 'ne'],
            ['nl-be', 'nl-be'],
            ['nl', 'nl'],
            ['nl', 'nl-nl'],
            ['nn', 'nn'],
            ['pa-in', 'pa-in'],
            ['pl', 'pl'],
            ['pt-br', 'pt-br'],
            ['pt', 'pt'],
            ['ro', 'ro'],
            ['ru', 'ru'],
            ['se', 'se'],
            ['si', 'si'],
            ['sk', 'sk'],
            ['sl', 'sl'],
            ['sq', 'sq'],
            ['sr-cyrl', 'sr-cyrl'],
            ['sr', 'sr'],
            ['ss', 'ss'],
            ['sv', 'sv'],
            ['sw', 'sw'],
            ['ta', 'ta'],
            ['te', 'te'],
            ['tet', 'tet'],
            ['th', 'th'],
            ['tlh', 'tlh'],
            ['tl-ph', 'tl-ph'],
            ['tr', 'tr'],
            ['tzl', 'tzl'],
            ['tzm', 'tzm'],
            ['tzm-latn', 'tzm-latn'],
            ['uk', 'uk'],
            ['uz', 'uz'],
            ['vi', 'vi'],
            ['x-pseudo', 'x-pseudo'],
            ['yo', 'yo'],
            ['zh-cn', 'zh-cn'],
            ['zh-hk', 'zh-hk'],
            ['zh-tw', 'zh-tw'],
        ];
    }

    /**
     * @dataProvider momentLocalesProvider
     */
    public function testCanonicalizedLocaleForMoment(?string $expected, string $original): void
    {
        $this->assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForMoment($this->mockExtensionContext($original)));
    }

    public function testIsGrantedAffirmative(): void
    {
        $this->assertTrue(
            $this->twigExtension->isGrantedAffirmative(['foo', 'bar'])
        );
        $this->assertTrue($this->twigExtension->isGrantedAffirmative('foo'));
        $this->assertTrue($this->twigExtension->isGrantedAffirmative('bar'));
    }

    /**
     * @dataProvider getRenderViewElementCompareTests
     */
    public function testRenderViewElementCompare(string $expected, string $type, $value, array $options, ?string $objectName = null): void
    {
        $this->fieldDescription
            ->method('getValue')
            ->willReturn($value);

        $this->fieldDescription
            ->method('getType')
            ->willReturn($type);

        $this->fieldDescription
            ->method('getOptions')
            ->willReturn($options);

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturnCallback(static function () use ($type, $options): ?string {
                if (isset($options['template'])) {
                    return $options['template'];
                }

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
                        return null;
                }
            });

        $this->object->name = 'SonataAdmin';

        $comparedObject = clone $this->object;

        if (null !== $objectName) {
            $comparedObject->name = $objectName;
        }

        $this->assertSame(
            $this->removeExtraWhitespace($expected),
            $this->removeExtraWhitespace(
                $this->twigExtension->renderViewElementCompare(
                    $this->environment,
                    $this->fieldDescription,
                    $this->object,
                    $comparedObject
                )
            )
        );
    }

    public function getRenderViewElementCompareTests(): iterable
    {
        return [
            ['<th>Data</th> <td>Example</td><td>Example</td>', 'string', 'Example', ['safe' => false]],
            ['<th>Data</th> <td>Example</td><td>Example</td>', 'text', 'Example', ['safe' => false]],
            ['<th>Data</th> <td>Example</td><td>Example</td>', 'textarea', 'Example', ['safe' => false]],
            ['<th>Data</th> <td>SonataAdmin<br/>Example</td><td>SonataAdmin<br/>Example</td>', 'virtual_field', 'Example', ['template' => 'custom_show_field.html.twig', 'safe' => false, 'SonataAdmin']],
            ['<th class="diff">Data</th> <td>SonataAdmin<br/>Example</td><td>SonataAdmin<br/>Example</td>', 'virtual_field', 'Example', ['template' => 'custom_show_field.html.twig', 'safe' => false], 'sonata-project/admin-bundle'],
            [
                '<th>Data</th> <td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> May 27, 2020 10:11 </time></td>'
                .'<td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> May 27, 2020 10:11 </time></td>',
                'datetime',
                new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('Europe/London')), [],
            ],
            [
                '<th>Data</th> <td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> 27.05.2020 10:11:12 </time></td>'
                .'<td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> 27.05.2020 10:11:12 </time></td>',
                'datetime',
                new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<th>Data</th> <td><time datetime="2020-05-27T10:11:12+00:00" title="2020-05-27T10:11:12+00:00"> May 27, 2020 18:11 </time></td>'
                .'<td><time datetime="2020-05-27T10:11:12+00:00" title="2020-05-27T10:11:12+00:00"> May 27, 2020 18:11 </time></td>',
                'datetime',
                new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<th>Data</th> <td><time datetime="2020-05-27" title="2020-05-27"> May 27, 2020 </time></td>'
                .'<td><time datetime="2020-05-27" title="2020-05-27"> May 27, 2020 </time></td>',
                'date',
                new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
        ];
    }

    /**
     * This method generates url part for Twig layout.
     */
    private function buildTwigLikeUrl(array $url): string
    {
        return htmlspecialchars(http_build_query($url, '', '&', PHP_QUERY_RFC3986));
    }

    private function getMethodAsPublic($privateMethod): \ReflectionMethod
    {
        $reflection = new \ReflectionMethod('Sonata\AdminBundle\Twig\Extension\SonataAdminExtension', $privateMethod);
        $reflection->setAccessible(true);

        return $reflection;
    }

    private function removeExtraWhitespace(string $string): string
    {
        return trim(preg_replace(
            '/\s+/',
            ' ',
            $string
        ));
    }

    private function mockExtensionContext(string $locale): array
    {
        $request = $this->createMock(Request::class);
        $request->method('getLocale')->willReturn($locale);
        $appVariable = $this->createMock(AppVariable::class);
        $appVariable->method('getRequest')->willReturn($request);

        return ['app' => $appVariable];
    }
}
