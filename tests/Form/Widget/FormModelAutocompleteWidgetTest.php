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

namespace Sonata\AdminBundle\Tests\Form\Widget;

use PHPUnit\Framework\MockObject\Stub;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Twig\CanonicalizeRuntime;
use Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

final class FormModelAutocompleteWidgetTest extends BaseWidgetTestCase
{
    protected $type = 'form';

    /**
     * @var ModelManagerInterface<object>&Stub
     */
    private ModelManagerInterface $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = static::createStub(ModelManagerInterface::class);

        parent::setUp();
    }

    public function testRenderAfterSubmittingAnUnknownIdWhenStrict(): void
    {
        $this->modelManager->method('find')->willReturn(null);

        $form = $this->factory->create(ModelAutocompleteType::class, null, [
            'model_manager' => $this->modelManager,
            'class' => \stdClass::class,
            'property' => 'name',
            'strict' => true,
        ]);
        $form->submit('42');

        static::assertFalse($form->isSynchronized());

        $html = $this->cleanHtmlWhitespace($this->renderWidget($form->createView()));

        static::assertStringContainsString('data-sonata-select2="false"></select>', $html);
        static::assertStringContainsString('<input type="hidden" name="sonata_type_model_autocomplete" value="">', $html);
    }

    protected function getEnvironment(): Environment
    {
        $environment = parent::getEnvironment();

        $loader = $environment->getLoader();
        static::assertInstanceOf(FilesystemLoader::class, $loader);
        $loader->addPath(\sprintf('%s/../../../src/Resources/views', __DIR__), 'SonataAdmin');

        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $canonicalizeRuntime = new CanonicalizeRuntime($requestStack);
        $environment->addExtension(new CanonicalizeExtension($canonicalizeRuntime));
        $environment->addRuntimeLoader(new FactoryRuntimeLoader([
            CanonicalizeRuntime::class => static fn (): CanonicalizeRuntime => $canonicalizeRuntime,
        ]));

        return $environment;
    }

    /**
     * @return FormExtensionInterface[]
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([new ModelAutocompleteType()], []),
        ];
    }
}
