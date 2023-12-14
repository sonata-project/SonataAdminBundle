<?php

declare(strict_types=1);

namespace Sonata\AdminBundle\Tests\App\Controller;

use Exception;
use Sonata\AdminBundle\Controller\CRUDController;

/**
 * @phpstan-extends CRUDController<object>
 */
final class CustomModelManagerExceptionMessageController extends CRUDController
{
    public const ERROR_MESSAGE = 'message from model manager exception';

    protected function handleModelManagerException(Exception $exception): string
    {
        return self::ERROR_MESSAGE;
    }
}
