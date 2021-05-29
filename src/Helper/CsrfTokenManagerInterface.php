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

namespace Sonata\AdminBundle\Helper;

/**
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 */
interface CsrfTokenManagerInterface
{
    public function getCsrfToken(string $intention);

    public function validateCsrfToken(string $intention): void;
}
