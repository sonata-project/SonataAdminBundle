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

class PostCategory
{
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function setPosts(Collection $posts): void
    {
        $this->posts = $posts;
    }

    public function addPost(Post $post): void
    {
        $this->posts->add($post);
    }

    public function removePost(Post $post): void
    {
        $this->posts->removeElement($post);
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
