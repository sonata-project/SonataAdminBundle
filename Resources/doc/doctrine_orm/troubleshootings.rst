Troubleshooting
===============

Deleted elements from a one-to-many association are not removed!
----------------------------------------------------------------

Make sure the Orphan Removal option is set to ``true``

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xsi="http://www.w3.org/2001/XMLSchema-instance" schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
        <entity name="Application\Sonata\MediaBundle\Entity\Gallery" table="media__gallery" >

            <one-to-many
                field="galleryHasMedias"
                target-entity="Application\Sonata\MediaBundle\Entity\GalleryHasMedia"
                mapped-by="gallery"
                orphan-removal="true"
                >

                <orphan-removal>true</orphan-removal>

            </one-to-many>

            <!-- other definitions -->
        </entity>
    </doctrine-mapping>

.. note::

    The last Doctrine version requires to define the ``orphan-removal`` as an attribute and not as a node.

Ordered fields are not ordered!
-------------------------------

Make sure the ``order-by`` option is correctly set.

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xsi="http://www.w3.org/2001/XMLSchema-instance" schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
        <entity name="Application\Sonata\MediaBundle\Entity\Gallery" table="media__gallery" >

            <one-to-many
                field="galleryHasMedias"
                target-entity="Application\Sonata\MediaBundle\Entity\GalleryHasMedia"
                mapped-by="gallery"
                >
                <order-by>
                    <order-by-field name="position" direction="ASC" />
                </order-by>

            </one-to-many>

            <!-- other definitions -->

        </entity>
    </doctrine-mapping>
