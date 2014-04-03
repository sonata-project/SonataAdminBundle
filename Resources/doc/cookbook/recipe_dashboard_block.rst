Creating a Dashboard block
==============================

This is a walkthrough of how to create a dashboard block that can be used with Sonata Admin Bundle

The recipe
----------

In order to create a dashboard block, we need to:

- Create a new block class that implements BlockBundleInterface
- Create a new block template
- Create a new block service for your block
- Add the new service to the Sonata Block Bundle configuration
- Add the new service to the Sonata Admin Bundle configuration
- Verify that the block works as expected

Step 1 - Create a new block class
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Create a new block class that implements BlockBundleInterface

.. code-block:: php

    <?php

    namespace InstitutoStorico\Bundle\NewsletterBundle\Block;

    use Symfony\Component\HttpFoundation\Response;

    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Validator\ErrorElement;

    use Sonata\BlockBundle\Model\BlockInterface;
    use Sonata\BlockBundle\Block\BaseBlockService;

    class NewsletterBlockService extends BaseBlockService
    {
        public function getName()
        {
            return 'My Newsletter';
        }

        public function getDefaultSettings()
        {
            return array();
        }

        public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
        {
        }

        public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
        {
        }

        public function execute(BlockInterface $block, Response $response = null)
        {
            // merge settings
            $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

            return $this->renderResponse('InstitutoStoricoNewsletterBundle:Block:block_my_newsletter.html.twig', array(
                'block'     => $block,
                'settings'  => $settings
                ), $response);
        }
    }

Step 2 - Create a new block template
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The base template from SonataBlockBundle can be overridden here:

.. code-block:: html+jinja

    {% extends 'SonataBlockBundle:Block:block_base.html.twig' %}

    {% block block %}
    <table class="table table-bordered table-striped sonata-ba-list">
        <thead>
            <tr>
                <th colspan="3">Newsletter - inviare</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <div class="btn-group" align="center">
                        <a class="btn btn-small" href="#">Servizio Newsletter</a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    {% endblock %}

Step 3 - Create a new block service for your block
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The service declaration looks like this:

.. code-block:: yaml

    sonata.block.service.newsletter:
        class: InstitutoStorico\Bundle\NewsletterBundle\Block\NewsletterBlockService
        arguments: [ "sonata.block.service.newsletter", @templating ]
        tags:
            - { name: sonata.block }

Step 4 - Add newly created to Sonata Block Bundle configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    #Sonata Block Bundle
    sonata_block:
        default_contexts: [cms]
        blocks:
            sonata.admin.block.admin_list:
                contexts:   [admin]
            sonata.block.service.text: ~
            sonata.block.service.action: ~
            sonata.block.service.rss: ~
            sonata.block.service.newsletter: ~

Step 5 - Add newly created service to Sonata Admin Block Bundle configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    # Sonata Admin Generator
    sonata_admin:
        ...
        dashboard:
            blocks:
                # display a dashboard block
                - { position: left, type: sonata.admin.block.admin_list }
                - { position: left, type: sonata.block.service.newsletter}

The newsletter block should now be active in your Admin Dashboard. 