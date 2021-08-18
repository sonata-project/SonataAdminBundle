The Show View
=============

Now your admin allows you to create, edit and list blog posts and categories.
But what if you just want to have a detailed view of one blog? This chapter
will tead you how to use the show view.

If you're still on http://localhost:8000/admin/app/blogpost/list, you'll see a
show button on every blog post row. But when you click on it, you end up with a
"No form available" message. This is because Sonata doesn't know which fields to
show, let's now configure some.

Configuring the Show Mapper
---------------------------

If you're now familiar with the ``FormMapper`` and the ``ListMapper``, the
``ShowMapper`` will look very similar::

    // src/Admin/BlogPostAdmin.php

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('title')
            ->add('body')
            ->add('category')
        ;
    }


Using Groups and tabs
---------------------

Like the ``FormMapper``, the ``ShowMapper`` also supports grouping fields together::

    // src/Admin/BlogPostAdmin.php

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Post')
                ->with('Content', ['class' => 'col-md-9'])
                    // ...
                ->end()
                ->with('Meta data', ['class' => 'col-md-3'])
                    // ...
                ->end()
            ->end()

            ->tab('Publish Options')
                // ...
            ->end()
        ;
    }


Round Up
--------

You've learned how to find posts to edit, how to create a nice list view,
how to add options to search, order and filter this list. Now you've learned
how to display the details of one post.

There might have been some very difficult things, but imagine the difficulty
writing everything yourself! As you're now already quite good with the basics,
you can start reading other articles in the documentation, like:

* :doc:`Customizing the Dashboard <../reference/dashboard>`
* :doc:`Configuring the Security system <../reference/security>`
* :doc:`Adding export functionality <../reference/action_export>`
* :doc:`Adding a preview page <../reference/preview_mode>`
