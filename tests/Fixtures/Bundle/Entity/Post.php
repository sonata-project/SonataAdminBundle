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

namespace Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Post
{
    private $tags;

    private $postCategories;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->postCategories = new ArrayCollection();
    }

    public function setTags(Collection $tags): void
    {
        $this->tags = $tags;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): void
    {
        $tag->setPost($this);
        $this->tags->add($tag);
    }

    public function removeTag(Tag $tag): void
    {
        $tag->setPost(null);
        $this->tags->removeElement($tag);
    }

    public function setPostCategories(Collection $postCategories): void
    {
        $this->postCategories = $postCategories;
    }

    public function getPostCategories(): Collection
    {
        return $this->postCategories;
    }

    public function addPostCategory(PostCategory $postCategory): void
    {
        $postCategory->addPost($this);
        $this->postCategories->add($postCategory);
    }

    public function removePostCategory(PostCategory $postCategory): void
    {
        $postCategory->removePost($this);
        $this->postCategories->removeElement($postCategory);
    }
}
