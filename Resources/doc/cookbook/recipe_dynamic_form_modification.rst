Modifying form fields dynamically depending on edited object
============================================================

It is a quite common situation when you need to modify your form's fields because
of edited object's properties or structure. Let us assume you only want to display
an admin form field for new objects and you do not want it to be shown for those
objects that have already been saved to the database and now are being edited.

This is a way for you to accomplish this.

In your ``Admin`` class' ``configureFormFields`` method you are able to get the
current object by calling ``$this->getSubject()``. The value returned will be your
linked model. Then, you should be able to dynamically add needed fields to the form:

.. code-block:: php
    
    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;

    class MyModelAdmin extends Admin 
    {
    // ...

      protected function configureFormFields(FormMapper $formMapper)
      {
        // Description field will always be added to the form:
        $formMapper->add('description', 'textarea');

        $subject = $this->getSubject();

        if ($subject->isNew()) {
            // The thumbnail field will only be added when the edited item is created
            $formMapper->add('thumbnail', 'file');
        }
      }
    }

