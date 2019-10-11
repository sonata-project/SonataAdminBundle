Modifying form fields dynamically depending on edited object
============================================================

It is a quite common situation when you need to modify your form's fields because
of edited object's properties or structure. Let us assume you only want to display
an admin form field for new objects and you do not want it to be shown for those
objects that have already been saved to the database and now are being edited.

This is a way for you to accomplish this.

In your ``Admin`` class' ``configureFormFields`` method you are able to get the
current object by calling ``$this->getSubject()``. The value returned will be your
linked model. And another method ``isCurrentRoute`` for check the current request's route.
Then, you should be able to dynamically add needed fields to the form::

    // src/Admin/PostAdmin

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;

    final class PostAdmin extends AbstractAdmin
    {
        // ...

        protected function configureFormFields(FormMapper $formMapper)
        {
            // Description field will always be added to the form:
            $formMapper
                ->add('description', TextareaType::class)
            ;

            $subject = $this->getSubject();

            if ($subject->isNew()) {
                // The thumbnail field will only be added when the edited item is created
                $formMapper->add('thumbnail', FileType::class);
            }

            // Name field will be added only when create an item
            if ($subject->isCurrentRoute('create')) {
                $formMapper->add('name', TextType::class);
            }

            // The foo field will added when current action is related acme.demo.admin.code Admin's edit form
            if ($subject->isCurrentRoute('edit', 'acme.demo.admin.code')) {
                $formMapper->add('foo', 'text');
            }
        }
    }
