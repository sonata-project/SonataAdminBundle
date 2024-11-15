Select2
=======

The admin comes with `select2 <https://select2.org/>`_ integration.
Select2 is a jQuery based replacement for select boxes.
It supports searching, remote data sets, and infinite scrolling of results.

The select2 is enabled on all ``select`` form elements by default.

Disable select2
---------------

If you don't want to use select2 in your admin, you can disable it in configuration.

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        options:
            use_select2: false # disable select2

.. note::

    If you disable select2, autocomplete form types will stop working.

Disable select2 on some form elements
-------------------------------------

To disable select2 on some ``select`` form element,
set data attribute ``data-sonata-select2 = "false"`` to this form element::

    use Sonata\AdminBundle\Form\Type\ModelType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class, [
                'attr' => [
                    'data-sonata-select2' => 'false'
                ]
            ])
        ;
    }

.. note::

    You have to use false as string! ``"false"``!

AllowClear
----------

Select2 parameter ``allowClear`` is handled automatically by admin. But if you want
to overload the default functionality, you can set data attribute ``data-sonata-select2-allow-clear="true"``
to enable ``allowClear`` or ``data-sonata-select2-allow-clear = "false"`` to disable the ``allowClear`` parameter::

    use Sonata\AdminBundle\Form\Type\ModelType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class, [
                'attr' => [
                    'data-sonata-select2-allow-clear' => 'false'
                ]
            ])
        ;
    }

.. note::

    You have to use false as string! ``"false"``!

AllowTags
----------

Select2 parameter ``allowTags`` can be set using the data attribute ``data-sonata-select2-allow-tags="true"``
to enable ``allowTags``::

    use Sonata\AdminBundle\Form\Type\ModelType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class, [
                'attr' => [
                    'data-sonata-select2-allow-tags' => 'true'
                ]
            ])
        ;
    }

.. note::

    You have to use true as string! ``"true"``!

Minimum results for search
--------------------------

To control the minimum amount of results that are required before the select is searchable you can set the data attribute ``data-sonata-select2-minimumResultsForSearch``. This controls select2's ``minimumResultsForSearch`` parameter::


    use Sonata\AdminBundle\Form\Type\ModelType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class, [
                'attr' => [
                    'data-sonata-select2-minimumResultsForSearch' => '10',
                ]
            ])
        ;
    }

.. note::

    By default ``minimumResultsForSearch`` will be set to ``10``

Maximum selection length
--------------------------

To control the maximum amount of results that can be selected, you can set the data attribute ``data-sonata-select2-maximumSelectionLength``. This controls select2's ``maximumSelectionLength`` parameter::


    use Sonata\AdminBundle\Form\Type\ModelType;

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class, [
                'attr' => [
                    'data-sonata-select2-maximumSelectionLength' => '3',
                ]
            ])
        ;
    }
