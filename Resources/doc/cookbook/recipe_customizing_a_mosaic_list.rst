Customizing a mosaic list
=========================

Since version 3.0, the AdminBundle now include a mosaic list mode in order to have a more visual representation.

.. figure:: ../images/list_mosaic_default.png
   :align: center
   :alt: Default view
   :width: 700px

It is possible to configure the default view by creating a dedicated template.

First, configure the ``outer_list_rows_mosaic`` template key:

.. code-block:: xml

       <service id="sonata.media.admin.media" class="%sonata.media.admin.media.class%">
            <tag name="sonata.admin" manager_type="orm" group="sonata_media" label_catalogue="%sonata.media.admin.media.translation_domain%" label="media" label_translator_strategy="sonata.admin.label.strategy.underscore" />
            <argument />
            <argument>%sonata.media.admin.media.entity%</argument>
            <argument>%sonata.media.admin.media.controller%</argument>
            <call method="setTemplates">
                <argument type="collection">
                    <argument key="outer_list_rows_mosaic">SonataMediaBundle:MediaAdmin:list_outer_rows_mosaic.html.twig</argument>
                </argument>
            </call>


The ``list_outer_rows_mosaic.html.twig`` is the name of one mosaic's tile. You should also extends the template and overwrite the default blocks availables.

.. code-block:: jinja

    {% extends 'SonataAdminBundle:CRUD:list_outer_rows_mosaic.html.twig' %}

    {% block sonata_mosaic_background %}{{ meta.image }}{% endblock %}

    {% block sonata_mosaic_default_view %}
        <span class="label label-primary pull-right">{{ object.providerName|trans({}, 'SonataMediaBundle') }}</span>
    {% endblock %}

    {% block sonata_mosaic_hover_view %}
        <span class="label label-primary pull-right">{{ object.providerName|trans({}, 'SonataMediaBundle') }}</span>

        {% if object.width %} {{ object.width }}{% if object.height %}x{{ object.height }}{% endif %}px{% endif %}
        {% if object.length > 0 %}
            ({{ object.length }})
        {% endif %}

        <br />

        {% if object.authorname is not empty %}
           {{ object.authorname }}
        {% endif %}

        {% if object.copyright is not empty and object.authorname is not empty %}
            ~
        {% endif %}

        {% if object.copyright is not empty %}
            &copy; {{ object.copyright }}
        {% endif  %}
    {% endblock %}

    {% block sonata_mosaic_description %}
        {% if admin.isGranted('EDIT', object) and admin.hasRoute('edit') %}
            <a href="{{ admin.generateUrl('edit', {'id' : object|sonata_urlsafeid(admin) }) }}">{{ meta.title|truncate(40) }}</a>
        {% elseif admin.isGranted('SHOW', object) and admin.hasRoute('show') %}
            <a href="{{ admin.generateUrl('show', {'id' : object|sonata_urlsafeid(admin) }) }}">{{ meta.title|truncate(40) }}</a>
        {% else %}
            {{ meta.title|truncate(40) }}
        {% endif %}
    {% endblock %}


Block types:
 - ``sonata_mosaic_background``: this block is the background value defined in the ObjectMetadata object.
 - ``sonata_mosaic_default_view``: this block is used when the list is displayed.
 - ``sonata_mosaic_hover_view``: this block is used when the mouse is over the tile.
 - ``sonata_mosaic_description``: this block will be always on screen and should represent the entity's name.


The ``ObjectMetadata`` object is returned by the related admin class, for instance the MediaBundle defines the method as:

.. code-block:: jinja

    <?php

    class MediaAdmin extends AbstractAdmin
    {
        // [...] others methods

        public function getObjectMetadata($object)
        {
            $provider = $this->pool->getProvider($object->getProviderName());

            $url = $provider->generatePublicUrl($object, $provider->getFormatName($object, 'admin'));

            return new Metadata($object->getName(), $object->getDescription(), $url);
        }
    }


The final view will look like:

.. figure:: ../images/list_mosaic_custom.png
   :align: center
   :alt: Customize view
   :width: 700px
