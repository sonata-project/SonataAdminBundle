Showing image previews
======================

This is a full working example of one way to add image previews to your create and
edit views in SonataAdmin.


Pre-requisites
--------------

- you have already got the image files on a server somewhere and have a helper
  method to retrieve a publicly visible URL for that image, in this example that
  method is called ``Image::getWebPath()``
- you have already set up an Admin to edit the object that contains the images,
  now you just want to add the previews. In this example that class is called
  ``ImageAdmin``

.. note::

    There is a separate cookbook recipe to demonstrate how to upload images
    (and other files) using SonataAdmin.


The recipe
----------

SonataAdmin lets us put raw HTML into the 'help' option for any given form field.
We are going to use this functionality to embed an image tag when an image exists.

To do this we need to:

- get access to the ``Image`` instance from within ``ImageAdmin``
- create an image tag based on the Image's URL
- add a 'help' option to a field on the Image form to display the image tag

For the sake of this example we will use some basic CSS to restrict the size of
the preview image (we are not going to generate and save special thumbnails).


Basic example - for single layer Admins
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If we are working directly with our ``ImageAdmin`` class then getting hold of
the ``Image`` instance is simply a case of calling ``$this->getSubject()``. Since
we are manipulating form fields we do this from within ``ImageAdmin::configureFormFields()``:

.. code-block:: php

    class ImageAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            // get the current Image instance
            $image = $this->getSubject();

            // use $fileFieldOptions so we can add other options to the field
            $fileFieldOptions = array('required' => false);
            if ($image && ($webPath = $image->getWebPath())) {
                // get the container so the full path to the image can be set
                $container = $this->getConfigurationPool()->getContainer();
                $fullPath = $container->get('request')->getBasePath().'/'.$webPath;

                // add a 'help' option containing the preview's img tag
                $fileFieldOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
            }

            $formMapper
                // ... other fields ...
                ->add('file', 'file', $fileFieldOptions)
            ;
        }
        // ...
    }

We then use CSS to restrict the max size of the image:

.. code-block:: css

    img.admin-preview {
        max-height: 200px;
        max-width: 200px;
    }

And that is all there is to it!

However, this method does not work when the ``ImageAdmin`` can be embedded in other
Admins using the ``sonata_type_admin`` field type. For that we need...

Advanced example - works with embedded Admins
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

When one Admin is embedded in another Admin, ``$this->getSubject()`` does not return the
instance under management by the embedded Admin. Instead we need to detect that our
Admin class is embedded and use a different method:

.. code-block:: php

    class ImageAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            if($this->hasParentFieldDescription()) { // this Admin is embedded
                // $getter will be something like 'getlogoImage'
                $getter = 'get' . $this->getParentFieldDescription()->getFieldName();

                // get hold of the parent object
                $parent = $this->getParentFieldDescription()->getAdmin()->getSubject();
                if ($parent) {
                    $image = $parent->$getter();
                } else {
                    $image = null;
                }
            } else {
                $image = $this->getSubject();
            }

            // use $fileFieldOptions so we can add other options to the field
            $fileFieldOptions = array('required' => false);
            if ($image && ($webPath = $image->getWebPath())) {
                // add a 'help' option containing the preview's img tag
                $fileFieldOptions['help'] = '<img src="'.$webPath.'" class="admin-preview" />';
            }

            $formMapper
                // ... other fields ...
                ->add('file', 'file', $fileFieldOptions)
            ;
        }
        // ...
    }

As you can see, the only change is how we retrieve set ``$image`` to the relevant Image instance.
When our ImageAdmin is embedded we need to get the parent object first then use a getter to
retrieve the Image. From there on, everything else is the same.


Notes
-----

If you have more than one level of embedding Admins this will (probably) not work. If you know of
a more generic solution, please fork and update this recipe on GitHub. Similarly, if there are any
errors or typos (or a much better way to do this) get involved and share your insights for the
benefit of everyone.

