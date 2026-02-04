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

namespace SensioLabs\AdminBundle\Tests\Fixtures\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;

/**
 * This class is intended to be used when testing with 3-level admin nesting.
 * PostAdmin -> CommentAdmin -> CommentVoteAdmin.
 *
 * @phpstan-extends AbstractAdmin<object>
 */
final class CommentVoteAdmin extends AbstractAdmin
{
}
