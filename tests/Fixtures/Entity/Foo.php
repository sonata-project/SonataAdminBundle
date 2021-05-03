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

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class Foo
{
    /**
     * @var string|int
     */
    private $bar;

    /**
     * @var string|int
     */
    private $baz;

    public function __toString()
    {
        return (string) $this->bar;
    }

    /**
     * @return int|string
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * @param string|int $bar
     */
    public function setBar($bar): void
    {
        $this->bar = $bar;
    }

    /**
     * @return int|string
     */
    public function getBaz()
    {
        return $this->baz;
    }

    /**
     * @param string|int $baz
     */
    public function setBaz($baz): void
    {
        $this->baz = $baz;
    }
}
