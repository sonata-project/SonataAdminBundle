<?php

declare(strict_types=1);

namespace Sonata\AdminBundle\Tests\App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;

final class CustomModelManagerThrowableMessageController extends CRUDController
{
    public const ERROR_MESSAGE = 'message from model manager throwable';

    protected function handleModelManagerThrowable(ModelManagerThrowable $exception)
    {
        return self::ERROR_MESSAGE;
    }
}
