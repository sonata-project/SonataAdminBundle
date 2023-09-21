iCheck
======

The admin comes with `iCheck <http://icheck.fronteed.com/>`_ integration.
iCheck is a jQuery based checkbox and radio buttons skinning plugin.
It provides a cross-browser and accessible solution to checkboxes and radio buttons customization.

The iCheck plugin is enabled on all ``checkbox`` and ``radio`` form elements by default.

Disable iCheck
--------------

If you don't want to use iCheck in your admin, you can disable it in configuration.

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        options:
            use_icheck: false # disable iCheck

Disable iCheck on some form elements
-------------------------------------

To disable iCheck on some ``checkbox`` or ``radio`` form elements,
set data attribute ``data-sonata-icheck = "false"`` to this form element::

    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Form\Type\ModelType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class, [
                'attr' => [
                    'data-sonata-icheck' => 'false'
                ]
            ])
        ;
    }

.. note::

    You have to use false as string! ``"false"``!

When using ``Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType`` (or other types that inherit from it)
with the ``expanded``: ``true`` option (that renders the form type with checkboxes or radio buttons),
it is necessary to set the ``data-sonata-icheck`` attribute on its choice elements::

    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Form\Type\ModelType;
    use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ChoiceFieldMaskType::class, [
                'expanded' => true,
                'placeholder_attr' => [
                    // the placeholder (if any) needs also the data-sonata-icheck attr too since is rendered as
                    // checkbox or radio button
                    'data-sonata-icheck' => 'false'
                ],
                'choice_attr' => [
                    'val1' => ['data-sonata-icheck' => 'false'],
                    'val2' => ['data-sonata-icheck' => 'false'],
                    // ...
                ],
            ])
        ;
    }
