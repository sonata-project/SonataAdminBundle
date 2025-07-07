display_if Option
=================

The ``display_if`` option allows conditionally displaying a field in Sonata Admin interfaces
based on a bool function.

Definition
----------

.. code-block:: php

    // FieldDescriptionOption
    'display_if': callable(AdminInterface $admin): bool

Description
-----------

When defined, ``display_if`` must be a callable that returns a boolean. If it returns ``false``,
the field will not be added to the mapper, effectively hiding it from the admin interface.

This option can be used in:

- ``FormMapper``
- ``ListMapper``
- ``ShowMapper``
- ``DatagridMapper``

Example: Conditional Form Field
------------------------------

.. code-block:: php

    $formMapper
        ->add('name')
        ->add('salaries', null, [], [
            'display_if' => static function olderThanTwenty(User $user): bool{
                return $user->getAge() >= 20;
            }
        ])
        ->add('status');

In this example, the ``salaries`` field will only be shown if the user is older than 20.

Example: ListMapper Column Visibility
-------------------------------------

.. code-block:: php

    $listMapper
        ->add('name')
        ->add('internalData', null, [
            'display_if' => static function (AdminInterface $admin): bool {
                return $admin->getSubject() !== null && $admin->getSubject()->isInternal();
            },
        ]);

Behavior
--------

If the ``display_if`` callable returns ``false``:

- The field is not added to the form/mapper.
- It will not be rendered in the admin interface.
- ``$mapper->has('field_name')`` will return ``false``.

Callback Arguments
------------------

The callable receives the current ``AdminInterface`` instance. You can use it to:

- Check user roles with ``$admin->isGranted(...)``
- Access the current subject via ``$admin->getSubject()``
- Check current routes with ``$admin->getRequest()->get('_route')``

Best Practices
--------------

- Keep the ``display_if`` callback small and focused.
- Avoid heavy logic in the callable to prevent performance issues.
- Use it for roles, context checks, or toggles based on the subject's state.
