Extensions
=======

A new way of dynamically configuring admin extensions:

.. code-block:: yaml

    # app/config/config.yml
        extensions:
            sandbox.main.content.publishable.admin.extension:
                admins:
                    - symfony_cmf_post.admin
                implements:
                    - Sandbox\MainBundle\Document\PublishableInterface
            sandbox.main.content.block.admin.extension:
                excludes:
                    - symfony_cmf_blog.admin 
                    - symfony_cmf_post.admin 
                extends:
                    - Symfony\Cmf\Bundle\BlogBundle\Document\Post
                instanceof:
                    - Symfony\Cmf\Bundle\BlogBundle\Document\Blog
            sandbox.main.content.user.admin.extension:
                excludes:
                    - symfony_cmf_post.admin 
                extends:
                    - Symfony\Cmf\Bundle\BlogBundle\Document\Blog
                instanceof:
                    - Symfony\Cmf\Bundle\BlogBundle\Document\Blog
                implements:
                    - Sandbox\MainBundle\Document\PublishableInterface



