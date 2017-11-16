<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * This class is intended to be used when testing with 3-level admin nesting.
 * PostAdmin -> CommentAdmin -> CommentVoteAdmin
 */
class CommentVoteAdmin extends AbstractAdmin
{
}
