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

namespace Sonata\AdminBundle\Tests\App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;

/**
 * @psalm-suppress MissingConstructor
 *
 * @see ConfigureCRUDControllerListener
 *
 * @phpstan-extends CRUDController<object>
 */
final class CustomCRUDController extends CRUDController
{
}
