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

namespace SensioLabs\AdminBundle\Tests\App\Model;

final class Translated
{
    /**
     * @var string|null
     */
    public $nameForm;

    /**
     * @var bool
     */
    public $isPublished = true;

    /**
     * @var \DateTime|null
     */
    public $datePublished;
}
