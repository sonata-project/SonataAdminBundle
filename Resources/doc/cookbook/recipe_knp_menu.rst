KnpMenu
=======

The admin comes with `KnpMenu <https://github.com/KnpLabs/KnpMenu>`_ integration
It integrates a menu with the KnpMenu library. This menu can be a SonataAdmin service or a route of a custom controller.

Add a custom controller entry in the menu
-----------------------------------------

To add a custom controller entry in the admin menu:

Create your controller

.. code-block:: php

    /**
     * @Route("/blog", name="blog_home")
     */
    public function blogAction()
    {
        // ...
    }

    /**
     * @Route("/blog/article/{articleId}", name="blog_article")
     */
    public function ArticleAction($articleId)
    {
        // ...
    }

Add the controller route as an item of the menu

.. code-block:: yaml

    # Default configuration for "SonataAdminBundle"
    sonata_admin:
        dashboard:
            groups:
                news:
                    label:                ~
                    label_catalogue:      ~
                    items:
                        - sonata.news.admin.post
                        - route:        blog_home
                          label:        Blog
                        - route:        blog_article
                          route_params: { articleId: 3 }
                          label:        Article
                    ...

Also you can override the template of knp_menu used by sonata. The default one is `SonataAdminBundle:Menu:sonata_menu.html.twig`:

.. code-block:: yaml

    # Default configuration for "SonataAdminBundle"
    sonata_admin:
        templates:
            knp_menu_template:           ApplicationAdminBundle:Menu:custom_knp_menu.html.twig
        ...

And voilà, now you have a new menu group which contains an entry to sonata_admin_id, to your blog and to a specific article.
