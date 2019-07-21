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

namespace Sonata\AdminBundle\Util;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Object\MetadataInterface;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class AdminSubjectExtractor
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function getSubjectAsString(object $subject, AdminInterface $admin = null): string
    {
        $admin = $this->getAdmin($subject, $admin);

        // Hold the the current admin subject in a variable in order to use `subjectAsString()` from
        // the subject's admin, in order to avoid unwanted overrides.
        $originalSubject = $admin->hasSubject() ? $admin->getSubject() : null;
        $hasPreviousSubject = $originalSubject !== $subject;
        if ($hasPreviousSubject) {
            $admin->setSubject($subject);
        }

        $subjectAsString = $admin->subjectAsString();

        if ($hasPreviousSubject) {
            // Restore the original subject.
            $admin->setSubject($originalSubject);
        }

        return $subjectAsString;
    }

    public function getSubjectMetadata(object $subject, AdminInterface $admin = null): MetadataInterface
    {
        $admin = $this->getAdmin($subject, $admin);

        // Hold the the current admin subject in a variable in order to use `subjectAsString()` from
        // the subject's admin, in order to avoid unwanted overrides.
        $originalSubject = $admin->hasSubject() ? $admin->getSubject() : null;
        $hasPreviousSubject = $originalSubject !== $subject;
        if ($hasPreviousSubject) {
            $admin->setSubject($subject);
        }

        $subjectMetadata = $admin->getSubjectMetadata();

        if ($hasPreviousSubject) {
            // Restore the original subject.
            $admin->setSubject($originalSubject);
        }

        return $subjectMetadata;
    }

    private function getAdmin(object $subject, AdminInterface $admin = null): AdminInterface
    {
        $class = \get_class($subject);

        if ($admin) {
            if (!is_a($class, $admin->getClass(), true)) {
                throw new \InvalidArgumentException(sprintf('Admin "%s" isn\'t configured to handle objects of type "%s"', $admin->getCode(), $class));
            }
        } elseif (!$this->pool->hasAdminByClass($class)) {
            throw new \InvalidArgumentException(sprintf('Object of type "%s" is not handled by any admin', $class));
        } else {
            $admin = $this->pool->getAdminByClass($class);
        }

        return $admin;
    }
}
