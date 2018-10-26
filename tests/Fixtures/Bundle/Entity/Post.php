<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Post
{
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function addTag(Tag $tag)
    {
        $tag->addPost($this);
        $this->tags[] = ($tag);
    }

    public function removePost(Tag $tag)
    {
        $tag->removePost($this);
        $this->tags->removeElement($tag);
    }
}
