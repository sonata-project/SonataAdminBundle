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

namespace Sonata\AdminBundle\Model;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * @method bool          hasMetadata(string $class)
 * @method ClassMetadata getMetadata(string $class)
 */
interface ClassMetadataReaderInterface
{
    // NEXT_MAJOR: Uncomment these lines and remove the @method phpdoc above.
    // public function hasMetadata(string $class): bool;
    // public function getMetadata(string $class): ClassMetadata;
}
