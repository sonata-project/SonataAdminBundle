<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Tag
{
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    public function getPosts()
    {
        return $this->posts;
    }

    public  function addPost(Post $post)
    {
        $this->posts[] = $post;
    }

    public function removePost(Post $post)
    {
        $this->posts->removeElement($post);
    }
}
