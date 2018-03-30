Integrate Symfony Workflow Component
====================================

If you are using Symfony `Workflow Component`_ and if you wish to use it with Sonata,
there is a 3rd party library that provides toolkit classes.

You can find it on `Packagist`_ and `GitHub`_.

Downloading the library
-----------------------

From a terminal, use composer to install the library :

.. code-block:: bash

    $ composer require yokai/sonata-workflow

Usage
-----

Let's say we have a ``BlogPost`` entity that is under a Symfony workflow:

.. code-block:: yaml

   # config/packages/workflow.yml
   framework:
       workflows:
           blog_post:
               type: state_machine
               marking_store:
                   type: single_state
                   arguments:
                       - status
               supports:
                   - App\Entity\BlogPost
               places:
                   - draft
                   - pending_review
                   - pending_update
                   - published
               initial_place: draft
               transitions:
                   start_review:
                       from: draft
                       to:   pending_review
                   interrupt_review:
                       from: pending_review
                       to:   pending_update
                   restart_review:
                       from: pending_update
                       to:   pending_review
                   publish:
                       from: pending_review
                       to:   published

You can use the provided extension to take care of your entity admin.

.. code-block:: yaml

   # config/packages/sonata_admin.yml
   services:
       admin.blog_post:
           class: App\Admin\BlogPostAdmin
           public: true
           arguments:
               - ~
               - App\Entity\PullRequest
               - Yokai\SonataWorkflow\Controller\WorkflowController
           tags:
               - { name: 'sonata.admin', manager_type: orm }

       admin.extension.blog_post_workflow:
           class: Yokai\SonataWorkflow\Admin\Extension\WorkflowExtension
           public: true
           arguments:
               - "@workflow.registry"
               - transitions_icons:
                     start_review: fa fa-question
                     interrupt_review: fa fa-edit
                     restart_review: fa fa-question
                     publish: fa fa-times

   sonata_admin:
       extensions:
           admin.extension.blog_post_workflow:
               admins:
                   - admin.blog_post


You are all set. If you visit your admin page in edit or show mode,
you will see something like this:

.. image:: ../images/admin_with_workflow.png
   :align: center
   :alt: Sonata Admin with Workflow
   :width: 700px


.. _`Workflow Component`: https://symfony.com/doc/current/components/workflow.html
.. _`Packagist`: https://packagist.org/packages/yokai/sonata-workflow
.. _`GitHub`: https://github.com/yokai-php/sonata-workflow
