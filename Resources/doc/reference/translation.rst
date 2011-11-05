Translation
===========

There are two main catalogue names in an Admin class:

* ``SonataAdminBundle`` : this catalogue is used to translate shared messages accross different admin
* ``messages`` : this catalogue is used to translate the message for the current admin

Ideally the ``messages`` catalogue should be changed to avoid any issues with other Admin classes.

You have two options to configure the catalogue for the admin class:

* one by defining a property

.. code-block:: php

    <?php
    class PageAdmin extends Admin
    {
        protected $translationDomain = 'SonataPageBundle';
    }


* or by injecting the value through the container

.. code-block:: xml

        <service id="sonata.page.admin.page" class="Sonata\PageBundle\Admin\PageAdmin">
            <tag name="sonata.admin" manager_type="orm" group="sonata_page" label="page"/>
            <argument />
            <argument>Application\Sonata\PageBundle\Entity\Page</argument>
            <argument />

            <call method="setTranslationDomain">
                <argument>SonataPageBundle</argument>
            </call>
        </service>


An admin instance always get the ``translator`` instance, so it can be used to translate messages within the
``configure*Fields`` method or in templates.

.. code-block:: jinja

    {# the classical call by using the twig trans helper #}
    {% trans from 'SonataPageBundle'%}message_create_snapshots{% endtrans %}

    {# by using the admin trans method with hardcoded catalogue #}
    {{ admin.trans('message_create_snapshots', {}, 'SonataPageBundle') }}

    {# by using the admin trans with the configured catalogue #}
    {{ admin.trans('message_create_snapshots') }}


The later solution is more flexible as no catalogue parameters are hardcoded.

Translate field labels
----------------------

The Admin bundle comes with a customized form field template. The most notable changes from the original one is the use
of the translation domain provided by the Admin instance to translate label.

By default, the label is the the field name. However a label can be defined as a the third argument of the ``add`` method:

.. code-block:: php

    <?php
    class PageAdmin extends Admin
    {
        public function configureFormFields(FormMapper $formMapper)
        {
            $formMapper->add('name', null, array('required' => false, 'label' => 'label.name'));
        }
    }