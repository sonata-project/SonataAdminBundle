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

namespace Sonata\AdminBundle\Tests\Form\Type\Filter;

use Symfony\Component\Form\Test\TypeTestCase;

abstract class BaseTypeTest extends TypeTestCase
{
    public function testHasTypeAndValue(): void
    {
        $form = $this->factory->create($this->getTestedType());

        $this->assertTrue($form->has('type'));
        $this->assertTrue($form->has('value'));
    }

    public function testHasFieldTypeAndOptions(): void
    {
        $form = $this->factory->create($this->getTestedType());

        $this->assertTrue($form->getConfig()->hasOption('field_type'));
        $this->assertTrue($form->getConfig()->hasOption('field_options'));
    }

    /**
     * @phpstan-return class-string
     */
    abstract protected function getTestedType(): string;
}
