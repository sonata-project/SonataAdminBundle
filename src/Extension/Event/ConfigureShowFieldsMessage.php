<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Extension\Event;

use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ConfigureShowFieldsMessage implements MessageInterface
{
    /**
     * @var ShowMapper
     */
    private $mapper;

    public function __construct(ShowMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return ShowMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }
}
