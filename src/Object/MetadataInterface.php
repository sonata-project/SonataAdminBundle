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

namespace Sonata\AdminBundle\Object;

/**
 * NEXT_MAJOR: Remove CoreBundle dependency.
 */
interface MetadataInterface extends \Sonata\CoreBundle\Model\MetadataInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @return string|null
     */
    public function getDomain();

    /**
     * @return array<string, mixed>
     */
    public function getOptions();

    /**
     * @param string $name    The option key
     * @param mixed  $default The default value if option not found
     */
    public function getOption($name, $default = null);
}
