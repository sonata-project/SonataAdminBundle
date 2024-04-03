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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\SonataConfiguration;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToString;
use Sonata\AdminBundle\Tests\Fixtures\Enum\Suit;
use Sonata\AdminBundle\Tests\Fixtures\StubFilesystemLoader;
use Sonata\AdminBundle\Twig\Extension\RenderElementExtension;
use Sonata\AdminBundle\Twig\Extension\XEditableExtension;
use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Sonata\AdminBundle\Twig\XEditableRuntime;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * NEXT_MAJOR: Remove this test.
 *
 * @group legacy
 */
final class RenderElementExtensionTest extends TestCase
{
    private RenderElementExtension $twigExtension;

    private Environment $environment;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    /**
     * @var FieldDescriptionInterface&MockObject
     */
    private FieldDescriptionInterface $fieldDescription;

    private \stdClass $object;

    private TranslatorInterface $translator;

    /**
     * @var MutableTemplateRegistryInterface&MockObject
     */
    private MutableTemplateRegistryInterface $templateRegistry;

    protected function setUp(): void
    {
        date_default_timezone_set('Europe/London');

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

        $this->templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $request = $this->createMock(Request::class);
        $request->method('get')->with('_sonata_admin')->willReturn('sonata_admin_foo_service');

        $loader = new StubFilesystemLoader([
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
        $this->environment->addGlobal('sonata_config', new SonataConfiguration('title', '/path/to/logo.png', [
            'confirm_exit' => true,
            'default_admin_route' => 'show',
            'default_group' => 'default',
            'default_icon' => '<i class="fas fa-folder"></i>',
            'default_translation_domain' => 'SonataAdminBundle',
            'dropdown_number_groups_per_colums' => 2,
            'form_type' => 'standard',
            'html5_validate' => true,
            'javascripts' => [],
            'js_debug' => false,
            'list_action_button_content' => 'all',
            'lock_protection' => false,
            'logo_content' => 'text',
            'mosaic_background' => 'bundles/sonataadmin/images/default_mosaic_image.png',
            'pager_links' => null,
            'role_admin' => 'ROLE_SONATA_ADMIN',
            'role_super_admin' => 'ROLE_SUPER_ADMIN',
            'search' => true,
            'skin' => 'skin-black',
            'sort_admins' => true,
            'stylesheets' => [],
            'use_bootlint' => false,
            'use_icheck' => true,
            'use_select2' => true,
            'use_stickyforms' => false,
        ]));

        $this->twigExtension = new RenderElementExtension(new RenderElementRuntime($propertyAccessor));

        $this->registerRequiredTwigExtensions();

        // initialize object
        $this->object = new \stdClass();

        // initialize admin
        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin
            ->method('getTemplateRegistry')
            ->willReturn($this->templateRegistry);

        $this->admin
            ->method('getBaseCodeRoute')
            ->willReturn('sonata_admin_foo_service');

        $this->admin
            ->method('id')
            ->with(static::equalTo($this->object))
            ->willReturn('12345');

        $this->admin
            ->method('getUrlSafeIdentifier')
            ->with(static::equalTo($this->object))
            ->willReturn('12345');

        $this->admin
            ->method('getNormalizedIdentifier')
            ->with(static::equalTo($this->object))
            ->willReturn('12345');

        // initialize field description
        $this->fieldDescription = $this->createMock(FieldDescriptionInterface::class);

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

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider provideRenderListElementCases
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderListElement(string $expected, string $type, mixed $value, array $options): void
    {
        $this->admin
            ->method('getPersistentParameters')
            ->willReturn(['context' => 'foo']);

        $this->admin
            ->method('hasAccess')
            ->willReturn(true);

        $this->templateRegistry->method('getTemplate')->with('base_list_field')
            ->willReturn('@SonataAdmin/CRUD/base_list_field.html.twig');

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
            ->method('getOption')
            ->willReturnCallback(static fn (string $name, mixed $default = null): mixed => $options[$name] ?? $default);

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturnCallback(static fn (): ?string => TemplateRegistryInterface::LIST_TEMPLATES[$type] ?? null);

        static::assertSame(
            $this->removeExtraWhitespace($expected),
            $this->removeExtraWhitespace($this->twigExtension->renderListElement(
                $this->environment,
                $this->object,
                $this->fieldDescription,
            ))
        );
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderListElementWithAdditionalValuesInArray(): void
    {
        $this->templateRegistry->method('getTemplate')->with('base_list_field')
            ->willReturn('@SonataAdmin/CRUD/base_list_field.html.twig');

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturn('@SonataAdmin/CRUD/list_string.html.twig');

        static::assertSame(
            $this->removeExtraWhitespace('<td class="sonata-ba-list-field sonata-ba-list-field-" objectId="12345"> Extra value </td>'),
            $this->removeExtraWhitespace($this->twigExtension->renderListElement(
                $this->environment,
                [$this->object, 'fd_name' => 'Extra value'],
                $this->fieldDescription
            ))
        );
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderWithDebug(): void
    {
        $this->fieldDescription
            ->method('getTemplate')
            ->willReturn('@SonataAdmin/CRUD/base_list_field.html.twig');

        $this->fieldDescription
            ->method('getFieldName')
            ->willReturn('fd_name');

        $this->fieldDescription
            ->method('getValue')
            ->willReturn('foo');

        $parameters = [
            'admin' => $this->admin,
            'value' => 'foo',
            'field_description' => $this->fieldDescription,
            'object' => $this->object,
        ];

        $this->environment->enableDebug();

        static::assertSame(
            $this->removeExtraWhitespace(
                <<<'EOT'
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
                $this->twigExtension->renderListElement($this->environment, $this->object, $this->fieldDescription, $parameters)
            )
        );
    }

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider provideRenderViewElementCases
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderViewElement(string $expected, string $type, mixed $value, array $options): void
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
            ->method('getOption')
            ->willReturnCallback(static fn (string $name, mixed $default = null): mixed => $options[$name] ?? $default);

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturnCallback(static fn (): ?string => TemplateRegistryInterface::SHOW_TEMPLATES[$type] ?? null);

        static::assertSame(
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

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider provideRenderViewElementCompareCases
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderViewElementCompare(
        string $expected,
        string $type,
        mixed $value,
        array $options,
        ?string $objectName
    ): void {
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
            ->method('getOption')
            ->willReturnCallback(static fn (string $name, mixed $default = null): mixed => $options[$name] ?? $default);

        $this->fieldDescription
            ->method('getTemplate')
            ->willReturnCallback(static function () use ($type, $options): ?string {
                if (isset($options['template']) && \is_string($options['template'])) {
                    return $options['template'];
                }

                return TemplateRegistryInterface::SHOW_TEMPLATES[$type] ?? null;
            });

        $this->object->name = 'SonataAdmin';

        $comparedObject = clone $this->object;

        if (null !== $objectName) {
            $comparedObject->name = $objectName;
        }

        static::assertSame(
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

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderRelationElementNoObject(): void
    {
        static::assertSame('foo', $this->twigExtension->renderRelationElement('foo', $this->fieldDescription));
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderRelationElementToString(): void
    {
        $this->fieldDescription->expects(static::once())
            ->method('getOption')
            ->willReturnCallback(static function (string $value, mixed $default = null): mixed {
                if ('associated_property' === $value) {
                    return $default;
                }

                return null;
            });

        $element = new FooToString();
        static::assertSame('salut', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderRelationElementCustomToString(): void
    {
        $this->fieldDescription->expects(static::once())
            ->method('getOption')
            ->willReturnCallback(static function (string $value, mixed $default = null): mixed {
                if ('associated_property' === $value) {
                    return 'customToString';
                }

                return $default;
            });

        $element = new class() {
            public function customToString(): string
            {
                return 'fooBar';
            }
        };

        static::assertSame('fooBar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderRelationElementMethodNotExist(): void
    {
        $this->fieldDescription->expects(static::once())
            ->method('getOption')
            ->willReturnCallback(static function (string $value, mixed $default = null): mixed {
                if ('associated_property' === $value) {
                    return null;
                }

                return $default;
            });

        $element = new \stdClass();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You must define an `associated_property` option or create a `stdClass::__toString');

        $this->twigExtension->renderRelationElement($element, $this->fieldDescription);
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderRelationElementWithPropertyPath(): void
    {
        $this->fieldDescription->expects(static::once())
            ->method('getOption')

            ->willReturnCallback(static function (string $value, mixed $default = null): mixed {
                if ('associated_property' === $value) {
                    return 'foo';
                }

                return $default;
            });

        $element = new \stdClass();
        $element->foo = 'bar';

        static::assertSame('bar', $this->twigExtension->renderRelationElement($element, $this->fieldDescription));
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    public function testRenderRelationElementWithClosure(): void
    {
        $this->fieldDescription->expects(static::once())
            ->method('getOption')
            ->willReturnCallback(static function (string $value, mixed $default = null): mixed {
                if ('associated_property' === $value) {
                    return static fn (object $element): string => property_exists($element, 'foo') ? sprintf('closure %s', $element->foo) : '';
                }

                return $default;
            });

        $element = new \stdClass();
        $element->foo = 'bar';

        static::assertSame(
            'closure bar',
            $this->twigExtension->renderRelationElement($element, $this->fieldDescription)
        );
    }

    /**
     * @phpstan-return iterable<array{string, string, mixed, array<string, mixed>}>
     */
    public function provideRenderListElementCases(): iterable
    {
        $elements = [
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>',
                FieldDescriptionInterface::TYPE_STRING,
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_STRING,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>',
                FieldDescriptionInterface::TYPE_STRING,
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_STRING,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> Example </td>',
                FieldDescriptionInterface::TYPE_TEXTAREA,
                'Example',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_TEXTAREA,
                null,
                [],
            ],
            'datetime field' => [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        December 24, 2013 10:11
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        December 24, 2013 18:11
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        24.12.2013 10:11:12
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                null,
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345">
                    <time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00">
                        24.12.2013 18:11:12
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['format' => 'd.m.Y H:i:s', 'timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                null,
                ['format' => 'd.m.Y H:i:s', 'timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345">
                    <time datetime="2013-12-24" title="2013-12-24">
                        December 24, 2013
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_DATE,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_DATE,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345">
                    <time datetime="2013-12-24" title="2013-12-24">
                        24.12.2013
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_DATE,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_DATE,
                null,
                ['format' => 'd.m.Y'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345">
                    <time datetime="10:11:12+00:00" title="10:11:12+00:00">
                        10:11:12
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_TIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345">
                    <time datetime="10:11:12+00:00" title="10:11:12+00:00">
                        18:11:12
                    </time>
                </td>',
                FieldDescriptionInterface::TYPE_TIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_TIME,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-float" objectId="12345"> 10.746135 </td>',
                FieldDescriptionInterface::TYPE_FLOAT,
                10.746135,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-float" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_FLOAT,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> 5678 </td>',
                FieldDescriptionInterface::TYPE_INTEGER,
                5678,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_INTEGER,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 1074.6135 % </td>',
                FieldDescriptionInterface::TYPE_PERCENT,
                10.746135,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 0 % </td>',
                FieldDescriptionInterface::TYPE_PERCENT,
                0,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_PERCENT,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> EUR 10.746135 </td>',
                FieldDescriptionInterface::TYPE_CURRENCY,
                10.746135,
                ['currency' => 'EUR'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> EUR 0 </td>',
                FieldDescriptionInterface::TYPE_CURRENCY,
                0,
                ['currency' => 'EUR'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> GBP 51.23456 </td>',
                FieldDescriptionInterface::TYPE_CURRENCY,
                51.23456,
                ['currency' => 'GBP'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_CURRENCY,
                null,
                ['currency' => 'GBP'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> <a href="mailto:admin@admin.com">admin@admin.com</a> </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com">admin@admin.com</a> </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme', 'body' => 'Message Body']).'">admin@admin.com</a>  </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme']).'">admin@admin.com</a>  </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['subject' => 'Main Theme'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345">
                    <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['body' => 'Message Body']).'">admin@admin.com</a>  </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true, 'body' => 'Message Body'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-email" objectId="12345"> admin@admin.com </td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345">
                    [1&nbsp;=>&nbsp;First, 2&nbsp;=>&nbsp;Second]
                </td>',
                FieldDescriptionInterface::TYPE_ARRAY,
                [1 => 'First', 2 => 'Second'],
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> [] </td>',
                FieldDescriptionInterface::TYPE_ARRAY,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-success">yes</span>
                </td>',
                FieldDescriptionInterface::TYPE_BOOLEAN,
                true,
                ['editable' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-danger">no</span>
                </td>',
                FieldDescriptionInterface::TYPE_BOOLEAN,
                false,
                ['editable' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-boolean" objectId="12345">
                    <span class="label label-danger">no</span>
                </td>',
                FieldDescriptionInterface::TYPE_BOOLEAN,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{value: 0, text: 'no'},{value: 1, text: 'yes'}]"
                        >
                            <span class="label label-success">yes</span>
                        </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_BOOLEAN,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{value: 0, text: 'no'},{value: 1, text: 'yes'}]"
                        >
                        <span class="label label-danger">no</span> </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_BOOLEAN,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{value: 0, text: 'no'},{value: 1, text: 'yes'}]" >
                            <span class="label label-danger">no</span> </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_BOOLEAN,
                null,
                ['editable' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>',
                FieldDescriptionInterface::TYPE_TRANS,
                'action_delete',
                ['catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_TRANS,
                null,
                ['catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345"> Delete </td>',
                FieldDescriptionInterface::TYPE_TRANS,
                'action_delete',
                ['format' => '%s', 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345">
                action.action_delete
                </td>',
                FieldDescriptionInterface::TYPE_TRANS,
                'action_delete',
                ['format' => 'action.%s'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-trans" objectId="12345">
                action.action_delete
                </td>',
                FieldDescriptionInterface::TYPE_TRANS,
                'action_delete',
                ['format' => 'action.%s', 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'Status1',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Status1 </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1'],
                ['choices' => [], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'Status1',
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                null,
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345">
                NoValidKeyInChoices
                </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'NoValidKeyInChoices',
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2', 'Status3' => 'Alias3']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'Foo',
                ['catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1, Alias3 </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1', 'Status3'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true], ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Alias1 | Alias3 </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1', 'Status3'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true, 'delimiter' => ' | '], ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
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
                FieldDescriptionInterface::TYPE_CHOICE,
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
                FieldDescriptionInterface::TYPE_CHOICE,
                ['NoValidKeyInChoices', 'Status2'],
                ['choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-choice" objectId="12345"> Delete, Alias3 </td>',
                FieldDescriptionInterface::TYPE_CHOICE,
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
                FieldDescriptionInterface::TYPE_CHOICE,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[]"
                        >
                            Status1
                        </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_CHOICE,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Alias1&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
                            Alias1 </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_CHOICE,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Alias1&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >

                        </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_CHOICE,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Alias1&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
                            NoValidKeyInChoices
                        </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_CHOICE,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{&quot;value&quot;:&quot;Foo&quot;,&quot;text&quot;:&quot;Delete&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
                             Delete
                        </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_CHOICE,
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
                FieldDescriptionInterface::TYPE_URL,
                null,
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_URL,
                null,
                ['url' => 'http://example.com'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345"> &nbsp; </td>',
                FieldDescriptionInterface::TYPE_URL,
                null,
                ['route' => ['name' => 'sonata_admin_foo']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">http://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com" target="_blank">https://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['attributes' => ['target' => '_blank']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com" target="_blank" class="fooLink">https://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['attributes' => ['target' => '_blank', 'class' => 'fooLink']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['hide_protocol' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['hide_protocol' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">http://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['hide_protocol' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['hide_protocol' => false],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">Foo</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['url' => 'http://example.com'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                '<b>Foo</b>',
                ['url' => 'http://example.com'],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo">Foo</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['route' => ['name' => 'sonata_admin_foo']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="https://example.com">https://example.com</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['route' => ['name' => 'show']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo">Foo</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['route' => ['name' => 'sonata_admin_foo', 'absolute' => true]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo">foo/bar?a=b&amp;c=123456789</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://foo/bar?a=b&c=123456789',
                ['route' => ['name' => 'sonata_admin_foo'],
                    'hide_protocol' => true, ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
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
                FieldDescriptionInterface::TYPE_URL,
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
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                [
                    'route' => [
                        'name' => 'sonata_admin_foo_param',
                        'absolute' => true,
                        'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                    ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                [
                    'route' => [
                        'name' => 'sonata_admin_foo_object',
                        'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                        'identifier_parameter_name' => 'barId',
                    ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-url" objectId="12345">
                <a href="http://localhost/foo/obj/abcd/12345/efgh?param3=ijkl">Foo</a>
                </td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                [
                    'route' => [
                        'name' => 'sonata_admin_foo_object',
                        'absolute' => true,
                        'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                        'identifier_parameter_name' => 'barId',
                    ],
                ],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                <p><strong>Creating a Template for the Field</strong> and form</p>
                </td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Field and form
                </td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['strip' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the...
                </td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => true],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345"> Creatin... </td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['length' => 10]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for the Field...
                </td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['cut' => false]],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template for t etc.
                </td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['ellipsis' => ' etc.']],
            ],
            [
                '<td class="sonata-ba-list-field sonata-ba-list-field-html" objectId="12345">
                Creating a Template[...]
                </td>',
                FieldDescriptionInterface::TYPE_HTML,
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
                    <td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345">
                    <div
                        class="sonata-readmore"
                        data-readmore-height="40"
                        data-readmore-more="Read more"
                        data-readmore-less="Close">A very long string</div>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_STRING,
                'A very long string',
                [
                    'collapse' => true,
                ],
            ],
            [
                <<<'EOT'
                    <td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345">
                    <div
                        class="sonata-readmore"
                        data-readmore-height="10"
                        data-readmore-more="More"
                        data-readmore-less="Less">A very long string</div>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_STRING,
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
                            data-url="/core/set-object-field-value?_sonata_admin=sonata_admin_foo_service&amp;context=list&amp;field=fd_name&amp;objectId=12345"
                            data-source="[{&quot;value&quot;:&quot;Status1&quot;,&quot;text&quot;:&quot;Delete&quot;},{&quot;value&quot;:&quot;Status2&quot;,&quot;text&quot;:&quot;Alias2&quot;},{&quot;value&quot;:&quot;Status3&quot;,&quot;text&quot;:&quot;Alias3&quot;}]" >
                             Delete, Alias2
                        </span>
                    </td>
                    EOT
                ,
                FieldDescriptionInterface::TYPE_CHOICE,
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

        // TODO: Remove the "if" check when dropping support of PHP < 8.1 and add the case to the list
        if (\PHP_VERSION_ID < 80100) {
            return $elements;
        }

        $elements[] = [
            '<td class="sonata-ba-list-field sonata-ba-list-field-enum" objectId="12345"> &nbsp; </td>',
            FieldDescriptionInterface::TYPE_ENUM,
            null,
            [],
        ];

        $elements[] = [
            '<td class="sonata-ba-list-field sonata-ba-list-field-enum" objectId="12345"> Hearts </td>',
            FieldDescriptionInterface::TYPE_ENUM,
            Suit::Hearts,
            [],
        ];

        $elements[] = [
            '<td class="sonata-ba-list-field sonata-ba-list-field-enum" objectId="12345"> Clubs </td>',
            FieldDescriptionInterface::TYPE_ENUM,
            Suit::Clubs,
            [
                'use_value' => false,
            ],
        ];

        $elements[] = [
            '<td class="sonata-ba-list-field sonata-ba-list-field-enum" objectId="12345"> C </td>',
            FieldDescriptionInterface::TYPE_ENUM,
            Suit::Clubs,
            [
                'use_value' => true,
            ],
        ];

        return $elements;
    }

    /**
     * @phpstan-return iterable<array{string, string, mixed, array<string, mixed>}>
     */
    public function provideRenderViewElementCases(): iterable
    {
        $elements = [
            ['<th>Data</th> <td>Example</td>', FieldDescriptionInterface::TYPE_STRING, 'Example', ['safe' => false]],
            ['<th>Data</th> <td>Example</td>', FieldDescriptionInterface::TYPE_STRING, 'Example', ['safe' => false]],
            ['<th>Data</th> <td>Example</td>', FieldDescriptionInterface::TYPE_TEXTAREA, 'Example', ['safe' => false]],
            [
                '<th>Data</th> <td><time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00"> December 24, 2013 10:11 </time></td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')), [],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00"> 24.12.2013 10:11:12 </time></td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y H:i:s'],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24T10:11:12+00:00" title="2013-12-24T10:11:12+00:00"> December 24, 2013 18:11 </time></td>',
                FieldDescriptionInterface::TYPE_DATETIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24" title="2013-12-24"> December 24, 2013 </time></td>',
                FieldDescriptionInterface::TYPE_DATE,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<th>Data</th> <td><time datetime="2013-12-24" title="2013-12-24"> 24.12.2013 </time></td>',
                FieldDescriptionInterface::TYPE_DATE,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                ['format' => 'd.m.Y'],
            ],
            [
                '<th>Data</th> <td><time datetime="10:11:12+00:00" title="10:11:12+00:00"> 10:11:12 </time></td>',
                FieldDescriptionInterface::TYPE_TIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London')),
                [],
            ],
            [
                '<th>Data</th> <td><time datetime="10:11:12+00:00" title="10:11:12+00:00"> 18:11:12 </time></td>',
                FieldDescriptionInterface::TYPE_TIME,
                new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('UTC')),
                ['timezone' => 'Asia/Hong_Kong'],
            ],
            ['<th>Data</th> <td>10.746135</td>', FieldDescriptionInterface::TYPE_FLOAT, 10.746135, ['safe' => false]],
            ['<th>Data</th> <td>5678</td>', FieldDescriptionInterface::TYPE_INTEGER, 5678, ['safe' => false]],
            ['<th>Data</th> <td>1074.6135 %</td>', FieldDescriptionInterface::TYPE_PERCENT, 10.746135, []],
            ['<th>Data</th> <td>0 %</td>', FieldDescriptionInterface::TYPE_PERCENT, 0, []],
            ['<th>Data</th> <td>EUR 10.746135</td>', FieldDescriptionInterface::TYPE_CURRENCY, 10.746135, ['currency' => 'EUR']],
            ['<th>Data</th> <td>GBP 51.23456</td>', FieldDescriptionInterface::TYPE_CURRENCY, 51.23456, ['currency' => 'GBP']],
            ['<th>Data</th> <td>EUR 0</td>', FieldDescriptionInterface::TYPE_CURRENCY, 0, ['currency' => 'EUR']],
            [
                '<th>Data</th> <td> <ul><li>1&nbsp;=>&nbsp;First</li><li>2&nbsp;=>&nbsp;Second</li></ul> </td>',
                FieldDescriptionInterface::TYPE_ARRAY,
                [1 => 'First', 2 => 'Second'],
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td> [1&nbsp;=>&nbsp;First, 2&nbsp;=>&nbsp;Second] </td>',
                FieldDescriptionInterface::TYPE_ARRAY,
                [1 => 'First', 2 => 'Second'],
                ['safe' => false, 'inline' => true],
            ],
            [
                '<th>Data</th> <td><span class="label label-success">yes</span></td>',
                FieldDescriptionInterface::TYPE_BOOLEAN,
                true,
                [],
            ],
            [
                '<th>Data</th> <td><span class="label label-danger">yes</span></td>',
                FieldDescriptionInterface::TYPE_BOOLEAN,
                true,
                ['inverse' => true],
            ],
            ['<th>Data</th> <td><span class="label label-danger">no</span></td>', FieldDescriptionInterface::TYPE_BOOLEAN, false, []],
            [
                '<th>Data</th> <td><span class="label label-success">no</span></td>',
                FieldDescriptionInterface::TYPE_BOOLEAN,
                false,
                ['inverse' => true],
            ],
            [
                '<th>Data</th> <td>Delete</td>',
                FieldDescriptionInterface::TYPE_TRANS,
                'action_delete',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle'],
            ],
            [
                '<th>Data</th> <td>Delete</td>',
                FieldDescriptionInterface::TYPE_TRANS,
                'delete',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle', 'format' => 'action_%s'],
            ],
            ['<th>Data</th> <td>Status1</td>', FieldDescriptionInterface::TYPE_CHOICE, 'Status1', ['safe' => false]],
            [
                '<th>Data</th> <td>Alias1</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'Status1',
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<th>Data</th> <td>NoValidKeyInChoices</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'NoValidKeyInChoices',
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<th>Data</th> <td>Delete</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                'Foo',
                ['safe' => false, 'catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ]],
            ],
            [
                '<th>Data</th> <td>NoValidKeyInChoices</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['NoValidKeyInChoices'],
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>NoValidKeyInChoices, Alias2</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['NoValidKeyInChoices', 'Status2'],
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>Alias1, Alias3</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1', 'Status3'],
                ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>Alias1 | Alias3</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1', 'Status3'], ['safe' => false, 'choices' => [
                    'Status1' => 'Alias1',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true, 'delimiter' => ' | '],
            ],
            [
                '<th>Data</th> <td>Delete, Alias3</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Foo', 'Status3'],
                ['safe' => false, 'catalogue' => 'SonataAdminBundle', 'choices' => [
                    'Foo' => 'action_delete',
                    'Status2' => 'Alias2',
                    'Status3' => 'Alias3',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td><b>Alias1</b>, <b>Alias3</b></td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1', 'Status3'],
                ['safe' => true, 'choices' => [
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td>&lt;b&gt;Alias1&lt;/b&gt;, &lt;b&gt;Alias3&lt;/b&gt;</td>',
                FieldDescriptionInterface::TYPE_CHOICE,
                ['Status1', 'Status3'],
                ['safe' => false, 'choices' => [
                    'Status1' => '<b>Alias1</b>',
                    'Status2' => '<b>Alias2</b>',
                    'Status3' => '<b>Alias3</b>',
                ], 'multiple' => true],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com" target="_blank">http://example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['safe' => false, 'attributes' => ['target' => '_blank']],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com" target="_blank" class="fooLink">http://example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['safe' => false, 'attributes' => ['target' => '_blank', 'class' => 'fooLink']],
            ],
            [
                '<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['safe' => false],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['safe' => false, 'hide_protocol' => true],
            ],
            [
                '<th>Data</th> <td><a href="https://example.com">example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['safe' => false, 'hide_protocol' => true],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">http://example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://example.com',
                ['safe' => false, 'hide_protocol' => false],
            ],
            [
                '<th>Data</th> <td><a href="https://example.com">https://example.com</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'https://example.com',
                ['safe' => false, 'hide_protocol' => false],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">Foo</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['safe' => false, 'url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com">&lt;b&gt;Foo&lt;/b&gt;</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                '<b>Foo</b>',
                ['safe' => false, 'url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td><a href="http://example.com"><b>Foo</b></a></td>',
                FieldDescriptionInterface::TYPE_URL,
                '<b>Foo</b>',
                ['safe' => true, 'url' => 'http://example.com'],
            ],
            [
                '<th>Data</th> <td><a href="/foo">Foo</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['safe' => false, 'route' => ['name' => 'sonata_admin_foo']],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo">Foo</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo',
                    'absolute' => true,
                ]],
            ],
            [
                '<th>Data</th> <td><a href="/foo">foo/bar?a=b&amp;c=123456789</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://foo/bar?a=b&c=123456789',
                [
                    'safe' => false,
                    'route' => ['name' => 'sonata_admin_foo'],
                    'hide_protocol' => true,
                ],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo">foo/bar?a=b&amp;c=123456789</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'http://foo/bar?a=b&c=123456789',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo',
                    'absolute' => true,
                ], 'hide_protocol' => true],
            ],
            [
                '<th>Data</th> <td><a href="/foo/abcd/efgh?param3=ijkl">Foo</a></td>',
                FieldDescriptionInterface::TYPE_URL,
                'Foo',
                ['safe' => false, 'route' => [
                    'name' => 'sonata_admin_foo_param',
                    'parameters' => ['param1' => 'abcd', 'param2' => 'efgh', 'param3' => 'ijkl'],
                ]],
            ],
            [
                '<th>Data</th> <td><a href="http://localhost/foo/abcd/efgh?param3=ijkl">Foo</a></td>',
                FieldDescriptionInterface::TYPE_URL,
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
                FieldDescriptionInterface::TYPE_URL,
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
                FieldDescriptionInterface::TYPE_URL,
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
                FieldDescriptionInterface::TYPE_EMAIL,
                null,
                [],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com">admin@admin.com</a></td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                [],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme', 'body' => 'Message Body']).'">admin@admin.com</a></td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['subject' => 'Main Theme']).'">admin@admin.com</a></td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['subject' => 'Main Theme'],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com?'.$this->buildTwigLikeUrl(['body' => 'Message Body']).'">admin@admin.com</a></td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme', 'body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true, 'subject' => 'Main Theme'],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true, 'body' => 'Message Body'],
            ],
            [
                '<th>Data</th> <td> <a href="mailto:admin@admin.com">admin@admin.com</a></td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => false],
            ],
            [
                '<th>Data</th> <td> admin@admin.com</td>',
                FieldDescriptionInterface::TYPE_EMAIL,
                'admin@admin.com',
                ['as_string' => true],
            ],
            [
                '<th>Data</th> <td><p><strong>Creating a Template for the Field</strong> and form</p></td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                [],
            ],
            [
                '<th>Data</th> <td>Creating a Template for the Field and form</td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['strip' => true],
            ],
            [
                '<th>Data</th> <td>Creating a Template for the...</td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => true],
            ],
            [
                '<th>Data</th> <td>Creatin...</td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['length' => 10]],
            ],
            [
                '<th>Data</th> <td>Creating a Template for the Field...</td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['cut' => false]],
            ],
            [
                '<th>Data</th> <td>Creating a Template for t etc.</td>',
                FieldDescriptionInterface::TYPE_HTML,
                '<p><strong>Creating a Template for the Field</strong> and form</p>',
                ['truncate' => ['ellipsis' => ' etc.']],
            ],
            [
                '<th>Data</th> <td>Creating a Template[...]</td>',
                FieldDescriptionInterface::TYPE_HTML,
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
                FieldDescriptionInterface::TYPE_STRING,
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
                FieldDescriptionInterface::TYPE_STRING,
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

        // TODO: Remove the "if" check when dropping support of PHP < 8.1 and add the case to the list
        if (\PHP_VERSION_ID < 80100) {
            return $elements;
        }

        $elements[] = [
            '<th>Data</th> <td>Hearts</td>',
            FieldDescriptionInterface::TYPE_ENUM,
            Suit::Hearts,
            [],
        ];

        return $elements;
    }

    /**
     * @phpstan-return iterable<array{string, string, mixed, array<string, mixed>, string|null}>
     */
    public function provideRenderViewElementCompareCases(): iterable
    {
        yield ['<th>Data</th> <td>Example</td><td>Example</td>', FieldDescriptionInterface::TYPE_STRING, 'Example', ['safe' => false], null];
        yield ['<th>Data</th> <td>Example</td><td>Example</td>', FieldDescriptionInterface::TYPE_STRING, 'Example', ['safe' => false], null];
        yield ['<th>Data</th> <td>Example</td><td>Example</td>', FieldDescriptionInterface::TYPE_TEXTAREA, 'Example', ['safe' => false], null];
        yield ['<th>Data</th> <td>SonataAdmin<br/>Example</td><td>SonataAdmin<br/>Example</td>', 'virtual_field', 'Example', ['template' => 'custom_show_field.html.twig', 'safe' => false], 'SonataAdmin'];
        yield ['<th class="diff">Data</th> <td>SonataAdmin<br/>Example</td><td>sonata-project/admin-bundle<br/>Example</td>', 'virtual_field', 'Example', ['template' => 'custom_show_field.html.twig', 'safe' => false], 'sonata-project/admin-bundle'];
        yield [
            '<th>Data</th> <td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> May 27, 2020 10:11 </time></td>'
            .'<td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> May 27, 2020 10:11 </time></td>',
            FieldDescriptionInterface::TYPE_DATETIME,
            new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('Europe/London')),
            [],
            null,
        ];
        yield [
            '<th>Data</th> <td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> 27.05.2020 10:11:12 </time></td>'
            .'<td><time datetime="2020-05-27T09:11:12+00:00" title="2020-05-27T09:11:12+00:00"> 27.05.2020 10:11:12 </time></td>',
            FieldDescriptionInterface::TYPE_DATETIME,
            new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('Europe/London')),
            ['format' => 'd.m.Y H:i:s'],
            null,
        ];
        yield [
            '<th>Data</th> <td><time datetime="2020-05-27T10:11:12+00:00" title="2020-05-27T10:11:12+00:00"> May 27, 2020 18:11 </time></td>'
            .'<td><time datetime="2020-05-27T10:11:12+00:00" title="2020-05-27T10:11:12+00:00"> May 27, 2020 18:11 </time></td>',
            FieldDescriptionInterface::TYPE_DATETIME,
            new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('UTC')),
            ['timezone' => 'Asia/Hong_Kong'],
            null,
        ];
        yield [
            '<th>Data</th> <td><time datetime="2020-05-27" title="2020-05-27"> May 27, 2020 </time></td>'
            .'<td><time datetime="2020-05-27" title="2020-05-27"> May 27, 2020 </time></td>',
            FieldDescriptionInterface::TYPE_DATE,
            new \DateTime('2020-05-27 10:11:12', new \DateTimeZone('Europe/London')),
            [],
            null,
        ];
    }

    /**
     * This method generates url part for Twig layout.
     *
     * @param array<string, string> $url
     */
    private function buildTwigLikeUrl(array $url): string
    {
        return htmlspecialchars(http_build_query($url, '', '&', \PHP_QUERY_RFC3986));
    }

    private function removeExtraWhitespace(string $string): string
    {
        return trim(preg_replace('/\s+/', ' ', $string) ?? '');
    }

    private function registerRequiredTwigExtensions(): void
    {
        $this->environment->addExtension($this->twigExtension);
        $this->environment->addExtension(new XEditableExtension(new XEditableRuntime($this->translator)));
        $this->environment->addExtension(new TranslationExtension($this->translator));
        $this->environment->addExtension(new FakeTemplateRegistryExtension());
        $this->environment->addExtension(new StringExtension());

        $this->environment->addRuntimeLoader(new FactoryRuntimeLoader([
            XEditableRuntime::class => fn (): XEditableRuntime => new XEditableRuntime($this->translator),
        ]));

        $this->registerRoutingExtension();
    }

    private function registerRoutingExtension(): void
    {
        $xmlFileLoader = new XmlFileLoader(new FileLocator([
            sprintf('%s/../../../src/Resources/config/routing', __DIR__),
        ]));
        $routeCollection = $xmlFileLoader->load('sonata_admin.xml');

        $xmlFileLoader = new XmlFileLoader(new FileLocator([
            sprintf('%s/../../Fixtures/Resources/config/routing', __DIR__),
        ]));

        $testRouteCollection = $xmlFileLoader->load('routing.xml');

        $routeCollection->addCollection($testRouteCollection);
        $requestContext = new RequestContext();
        $urlGenerator = new UrlGenerator($routeCollection, $requestContext);
        $this->environment->addExtension(new RoutingExtension($urlGenerator));
    }
}
