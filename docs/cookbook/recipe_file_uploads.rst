Uploading and saving documents (including images) using DoctrineORM and SonataAdmin
===================================================================================

This is a full working example of a file upload management method using
SonataAdmin with the DoctrineORM persistence layer.

Pre-requisites
--------------

- you already have SonataAdmin and DoctrineORM up and running
- you already have an Entity class that you wish to be able to connect uploaded
  documents to, in this example that class will be called ``Image``.
- you already have an Admin set up, in this example it's called ``ImageAdmin``
- you understand file permissions on your web server and can manage the permissions
  needed to allow your web server to upload and update files in the relevant
  folder(s)

The recipe
----------

First we will cover the basics of what your Entity needs to contain to enable document
management with Doctrine. There is a good cookbook entry about
`uploading files with Doctrine and Symfony`_ on the Symfony website, so I will show
code examples here without going into the details. It is strongly recommended that
you read that cookbook first.

To get file uploads working with SonataAdmin we need to:

- add a file upload field to our ImageAdmin
- 'touch' the Entity when a new file is uploaded so its lifecycle events are triggered

Basic configuration - the Entity
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Following the guidelines from the Symfony cookbook, we have an Entity definition
that looks something like the YAML below (of course, you can achieve something
similar with XML or Annotation based definitions too). In this example we are using
the ``updated`` field to trigger the lifecycle callbacks by setting it based on the
upload timestamp.

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/Doctrine/Image.orm.yml

        AppBundleBundle\Entity\Image:
            type: entity
            repositoryClass: AppBundle\Entity\Repositories\ImageRepository
            table: images
            id:
                id:
                    type:         integer
                    generator:    { strategy: AUTO }
            fields:
                filename:
                    type:         string
                    length:       100

                # changed when files are uploaded, to force preUpdate and postUpdate to fire
                updated:
                    type:         datetime
                    nullable:     true

                # ... other fields ...
            lifecycleCallbacks:
                prePersist:   [ lifecycleFileUpload ]
                preUpdate:    [ lifecycleFileUpload ]

We then have the following methods in our ``Image`` class to manage file uploads:

.. code-block:: php

    <?php
    // src/AppBundle/Bundle/Entity/Image.php

    const SERVER_PATH_TO_IMAGE_FOLDER = '/server/path/to/images';

    /**
     * Unmapped property to handle file uploads
     */
    private $file;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Manages the copying of the file to the relevant place on the server
     */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // we use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and target filename as params
        $this->getFile()->move(
            self::SERVER_PATH_TO_IMAGE_FOLDER,
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->filename = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->setFile(null);
    }

    /**
     * Lifecycle callback to upload the file to the server
     */
    public function lifecycleFileUpload()
    {
        $this->upload();
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire
     */
    public function refreshUpdated()
    {
        $this->setUpdated(new \DateTime());
    }

    // ... the rest of your class lives under here, including the generated fields
    //     such as filename and updated

When we upload a file to our Image, the file itself is transient and not persisted
to our database (it is not part of our mapping). However, the lifecycle callbacks
trigger a call to ``Image::upload()`` which manages the actual copying of the
uploaded file to the filesystem and updates the ``filename`` property of our Image,
this filename field *is* persisted to the database.

Most of the above is simply from the `uploading files with Doctrine and Symfony`_ cookbook
entry. It is highly recommended reading!

Basic configuration - the Admin class
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

We need to do two things in Sonata to enable file uploads:

1. Add a file upload widget
2. Ensure that the Image class' lifecycle events fire when we upload a file

Both of these are straightforward when you know what to do:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/ImageAdmin.php

    class ImageAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('file', 'file', array(
                    'required' => false
                ))

                // ...
            ;
        }

        public function prePersist($image)
        {
            $this->manageFileUpload($image);
        }

        public function preUpdate($image)
        {
            $this->manageFileUpload($image);
        }

        private function manageFileUpload($image)
        {
            if ($image->getFile()) {
                $image->refreshUpdated();
            }
        }

        // ...
    }

We mark the ``file`` field as not required since we do not need the user to upload a
new image every time the Image is updated. When a file is uploaded (and nothing else
is changed on the form) there is no change to the data which Doctrine needs to persist
so no ``preUpdate`` event would fire. To deal with this we hook into SonataAdmin's
``preUpdate`` event (which triggers every time the edit form is submitted) and use
that to update an Image field which is persisted. This then ensures that Doctrine's
lifecycle events are triggered and our Image manages the file upload as expected.

And that is all there is to it!

However, this method does not work when the ``ImageAdmin`` is embedded in other
Admins using the ``sonata_type_admin`` field type. For that we need something more...

Advanced example - works with embedded Admins
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

When one Admin is embedded in another Admin, the child Admin's ``preUpdate()`` method is
not triggered when the parent is submitted. To deal with this we need to use the parent
Admin's lifecycle events to trigger the file management when needed.

In this example we have a Page class which has three one-to-one Image relationships
defined, linkedImage1 to linkedImage3. The PostAdmin class' form field configuration
looks like this:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PostAdmin.php

    class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('linkedImage1', 'sonata_type_admin', array(
                    'delete' => false
                ))
                ->add('linkedImage2', 'sonata_type_admin', array(
                    'delete' => false
                ))
                ->add('linkedImage3', 'sonata_type_admin', array(
                    'delete' => false
                ))

                // ...
            ;
        }

        // ...
    }

This is easy enough - we have embedded three fields, which will then use our ``ImageAdmin``
class to determine which fields to show.

In our PostAdmin we then have the following code to manage the relationships' lifecycles:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PostAdmin.php

    class PostAdmin extends AbstractAdmin
    {
        // ...

        public function prePersist($page)
        {
            $this->manageEmbeddedImageAdmins($page);
        }

        public function preUpdate($page)
        {
            $this->manageEmbeddedImageAdmins($page);
        }

        private function manageEmbeddedImageAdmins($page)
        {
            // Cycle through each field
            foreach ($this->getFormFieldDescriptions() as $fieldName => $fieldDescription) {
                // detect embedded Admins that manage Images
                if ($fieldDescription->getType() === 'sonata_type_admin' &&
                    ($associationMapping = $fieldDescription->getAssociationMapping()) &&
                    $associationMapping['targetEntity'] === 'AppBundle\Entity\Image'
                ) {
                    $getter = 'get'.$fieldName;
                    $setter = 'set'.$fieldName;

                    /** @var Image $image */
                    $image = $page->$getter();

                    if ($image) {
                        if ($image->getFile()) {
                            // update the Image to trigger file management
                            $image->refreshUpdated();
                        } elseif (!$image->getFile() && !$image->getFilename()) {
                            // prevent Sf/Sonata trying to create and persist an empty Image
                            $page->$setter(null);
                        }
                    }
                }
            }
        }

        // ...
    }

Here we loop through the fields of our PageAdmin and look for ones which are ``sonata_type_admin``
fields which have embedded an Admin which manages an Image.

Once we have those fields we use the ``$fieldName`` to build strings which refer to our accessor
and mutator methods. For example we might end up with ``getlinkedImage1`` in ``$getter``. Using
this accessor we can get the actual Image object from the Page object under management by the
PageAdmin. Inspecting this object reveals whether it has a pending file upload - if it does we
trigger the same ``refreshUpdated()`` method as before.

The final check is to prevent a glitch where Symfony tries to create blank Images when nothing
has been entered in the form. We detect this case and null the relationship to stop this from
happening.

Notes
-----

If you are looking for richer media management functionality there is a complete SonataMediaBundle
which caters to this need. It is documented online and is created and maintained by the same team
as SonataAdmin.

To learn how to add an image preview to your ImageAdmin take a look at the related cookbook entry.


.. _`uploading files with Doctrine and Symfony`: http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
