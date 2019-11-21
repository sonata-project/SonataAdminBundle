Using DataMapper to work with domain entities without per field setters
=======================================================================

This is an example of using DataMapper with entities that avoid setters/getters for each field.

Pre-requisites
--------------

- You already have SonataAdmin and DoctrineORM up and running.
- You already have an Entity class, in this example that class will be called ``Example``.
- You already have an Admin set up, in this example it's called ``ExampleAdmin``.

The recipe
----------

If there is a requirement for the entity to have domain specific methods instead of getters/setters for each
entity field then it won't work with SonataAdmin out of the box. But Symfony Form component provides ``DataMapper``
that can be used to make it work. Symfony itself lacks examples of using ``DataMapper`` but there is an article by
webmozart that covers it - https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/

Example Entity
^^^^^^^^^^^^^^

.. code-block:: php

    // src/Entity/Example.php

    namespace App\Entity;

    class Example
    {
        private $name;

        private $description;

        public function __construct($name, $description)
        {
            $this->name = $name;
            $this->description = $description;
        }

        public function update($description)
        {
            $this->description = $description
        }

        // rest of the code goes here
    }

DataMapper
^^^^^^^^^^

To be able to set entity data without the possibility to use setters a ``DataMapper`` should be created::

    // src/Form/DataMapper/ExampleDataMapper.php

    namespace App\Form\DataMapper;

    use Symfony\Component\Form\DataMapperInterface;
    use App\Entity\Example;

    class ExampleDataMapper implements DataMapperInterface
    {
        /**
         * @param Example $data
         * @param FormInterface[]|\Traversable $forms
         */
        public function mapDataToForms($data, $forms)
        {
            if (null !== $data) {
                $forms = iterator_to_array($forms);
                $forms['name']->setData($data->getName());
                $forms['description']->setData($data->getDescription());
            }
        }

        /**
         * @param FormInterface[]|\Traversable $forms
         * @param Example $data
         */
        public function mapFormsToData($forms, &$data)
        {
            $forms = iterator_to_array($forms);

            if (null === $data->getId()) {
                $name = $forms['name']->getData();
                $description = $forms['description']->getData();

                // New entity is created
                $data = new Example(
                    $name,
                    $description
                );
            } else {
                $data->update(
                    $forms['description']->getData()
                );
            }
        }
    }

Admin class
^^^^^^^^^^^

Now we need to configure the form to use our ``ExampleDataMapper``::

    // src/Admin/ExampleAdmin.php

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use App\Form\DataMapper\ExampleDataMapper;

    final class ExampleAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $form)
        {
            $form
                ->add('name', null)
                ->add('description', null);
            ;

            $builder = $form->getFormBuilder();
            $builder->setDataMapper(new ExampleDataMapper());
        }

        // ...
    }
