Events
======

An event mechanism is available to add an extra entry point to extend Admin instance.

ConfigureEvent
~~~~~~~~~~~~~~

This event is generated when a form, list, show, datagrid is configured. The event names are:

 - sonata.admin.event.configure.form
 - sonata.admin.event.configure.list
 - sonata.admin.event.configure.datagrid
 - sonata.admin.event.configure.show

PersistenceEvent
~~~~~~~~~~~~~~~~

This event is generated when a persistency layer update, save or delete an object. The event names are:

 - sonata.admin.event.persistence.pre_update
 - sonata.admin.event.persistence.post_update
 - sonata.admin.event.persistence.pre_persist
 - sonata.admin.event.persistence.post_persist
 - sonata.admin.event.persistence.pre_remove
 - sonata.admin.event.persistence.post_remove


ConfigureQueryEvent
~~~~~~~~~~~~~~~~~~~

This event is generated when a list query is defined. The event name is: ``sonata.admin.event.configure.query``

BlockEvent
~~~~~~~~~~~~~~~~~~~

Block events help you customize your templates. Available events are :

 - sonata.admin.dashboard.top
 - sonata.admin.dashboard.bottom
 - sonata.admin.list.table.top
 - sonata.admin.list.table.bottom
 - sonata.admin.edit.form.top
 - sonata.admin.edit.form.bottom
 - sonata.admin.show.top
 - sonata.admin.show.bottom

If you want more information about block events, you should check the
`"Event" section of block bundle documentation <http://sonata-project.org/bundles/block/master/doc/reference/events.html>`_.
