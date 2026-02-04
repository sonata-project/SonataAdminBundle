<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\Controller;

use SensioLabs\AdminBundle\Controller\CRUDController;

/**
 * @phpstan-extends CRUDController<object>
 */
final class CustomModelManagerExceptionMessageController extends CRUDController
{
    public const ERROR_MESSAGE = 'message from model manager exception';

    /**
     * @phpstan-throws void
     */
    protected function handleModelManagerException(\Exception $exception): string
    {
        return self::ERROR_MESSAGE;
    }
}
