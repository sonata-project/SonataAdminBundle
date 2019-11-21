iCheck
======

The admin comes with `iCheck <http://icheck.fronteed.com/>`_ integration
since version 3.0.0. iCheck is a jQuery based checkbox and radio buttons skinning plugin.
It provides a cross-browser and accessible solution to checkboxes and radio buttons customization.

The iCheck plugin is enabled on all ``checkbox`` and ``radio`` form elements by default.

Disable iCheck
--------------

If you don't want to use iCheck in your admin, you can disable it in configuration.

.. configuration-block::

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

    public function configureFormFields(FormMapper $form)
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
