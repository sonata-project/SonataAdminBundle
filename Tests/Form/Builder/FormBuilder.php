<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Builder;

use Symfony\Component\Form\Test\FormBuilderInterface;

/**
 * Class FormBuilder
 *
 * Used to avoid an issue when to mocking FormBuilderInterface directly
 *
 * @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
 */
abstract class FormBuilder implements FormBuilderInterface
{
}
