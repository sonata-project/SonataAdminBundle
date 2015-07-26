CHANGELOG
=========

### 2015-06-18
 * Add missing Route constructor parameters to ``RouteCollection:add`` method

### 2015-03-04
 * [BC BREAK] Admin LTE 2.0 used. Assets files changed.
 * [BC BREAK] moved ``sonata_wrapper`` block on ```standard_layout.html.twig```
 * [BC BREAK] removed ``btn-outline``` from doctrine-orm-admin form actions buttons

### 2015-03-01
 * [BC BREAK] added the ``hasRoute`` method to the AdminInterface

## 2015-02-18
 * [BC BREAK] Integration of KNPMenu for the admin menu. This integration is resetted when the standard layout ``standard_layout.html.twig`` is overrided. The KNPMenu is available in ``sonata_menu.html.twig`` template.

### 2015-02-15
 * [BC BREAK] added ``getFieldOption``, ``setFieldOption`` methods to the FilterInterface
 * [BC BREAK] added the ``getFilterFieldDescription`` method to the AdminInterface
 * [BC BREAK] added the ``getMaxPageLinks``, ``setMaxPageLinks`` methods to the PagerInterface
 * [BC BREAK] CSS class ``sonata-autocomplete-dropdown-item`` is not automatically added to dropdown
   autocomplete item in ``sonata_type_model_autocomplete``, use option ``dropdown_item_css_class``
   to set the CSS class of dropdown item.

## 2015-01-05
 * [BC BREAK] #2665 - text from Admin's toString method is escaped for html output before adding in flash message to prevent possible XSS vulnerability.

### 2014-11-04
* [BC BREAK] Inline edition now validates the whole object.

### 2014-09-21
 * added new form type ``sonata_choice_field_mask``

### 2014-09-19
 * [BC BREAK] ``field_options`` is now directly given to ``value`` form in ``DateRangeType`` and ``DateTimeRangeType`` filters.

### 2014-08-08
 * added new form type ``sonata_type_model_autocomplete``
 * changed ``collection`` form type to ``sonata_type_native_collection``

### 2013-12-27

* [BC BREAK] Added KnpMenuBundle v2.x compatibility, ``buildSideMenu`` must now use the ``Admin::generateMenuUrl`` method to generate the route arguments for the KnpMenu options.

### 2013-12-14

* [BC BREAK] Added the ``getTranslationLabel`` method to AdminInterface

### 2013-12-05

* [BC BREAK] Move some classes to SonataCoreBundle, you need to add a new dependency

### 2013-11-23

* [BC BREAK] added ``getBatchActions`` to the AdminInterface
  If you do not extend the Admin class, you need to add this method to
  your admin.

### 2013-10-26
 * added new form type ``sonata_type_model_hidden``

### 2013-10-13

* [BC BREAK] added ``setCurrentChild``, ``getCurrentChild`` to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

### 2013-10-05

* [BC BREAK] added ``getExportFormats``, ``getDataSourceIterator`` to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

### 2013-10-01

* [BC BREAK] added ``supportsPreviewMode`` to the AdminInterface
  If you do not extend the Admin class, you need to add this method to
  your admin.

### 2013-09-30

* [BC BREAK] added ``getFilterParameters`` to the AdminInterface
  If you do not extend the Admin class, you need to add this method to
  your admin.

### 2013-09-27

* [BC BREAK] added ``hasParentFieldDescription``, ``getPersistentParameters``,
  ``getParentFieldDescription``, ``getUniqid``, ``getBaseCodeRoute``,
  ``getIdParameter`` to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

* added support for select2 (jQuery based replacement for select boxes)

### 2013-09-23

* change list's action buttons to use ``btn-small`` from twitter bootstrap

### 2013-09-20

* [BC BREAK] added ``getTranslator``, ``getForm``, ``getBreadcrumbs``
  to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

### 2013-09-13

* [BC BREAK] added ``getMaxPerPage``, ``setMaxPerPage``, ``setPage``,
  ``setQuery ``, ``getResults`` to the PagerInterface
  If you do not extend the Pager class, you need to add these methods to
  your pager.

* [BC BREAK] added ``isActive`` to the FilterInterface
  If you do not extend the Filter class, you need to add this method to
  your filter.

### 2013-09-11

* [BC BREAK] added ``hasShowFieldDescription``, ``hasListFieldDescription``,
  ``removeListFieldDescription``, ``removeFilterFieldDescription``,
  ``hasFilterFieldDescription`` to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

* [BC BREAK] added ``reorderFilters`` to the DatagridInterface
  If you do not extend the Datagrid class, you need to add this method to
  your Datagrid.

### 2013-09-05

* [BC BREAK] added ``getListBuilder``, ``getDatagridBuilder``, ``setBaseControllerName``,
  ``getBaseControllerName``, ``getFormFieldDescriptions``, ``getRoutes``, ``getFilterFieldDescriptions``,
  ``getListFieldDescriptions``, ``isChild`` to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

### 2013-08-30
* [BC BREAK] added ``getLabel``, ``removeShowFieldDescription``, ``getShowGroups``,
  ``setShowGroups``, ``reorderShowGroup`` to the AdminInterface
  If you do not extend the Admin class, you need to add these methods to
  your admin.

### 2013-07-26

* [BC BREAK] added alterNewInstance to AdminExtensionInterface
  If you do not extend the AdminExtension, you need to add an empty method to
  your extension classes:

      public function alterNewInstance(AdminInterface $admin, $object)
      {}

* [BC BREAK] added hasRequest to the AdminInterface
  If you do not extend the Admin class, you need to add a hasRequest method to
  your admin like this (depending on how you handle the request you return in
  getRequest:

      public function hasRequest()
      {
          return null !== $this->request;
      }

### 2013-07-23

* [BC BREAK] changed route name/pattern guesser to be more acurate and
  persistance layer agnostic, this might affect you if you use a namespace scheme
  similar to the examples below:
    * **Before** - admin for `Symfony\Cmf\Bundle\FoobarBundle\Document\Bar` generated base route name  `admin_bundle_foobar_bar` and base pattern `/cmf/bundle/bar`
    * **After** - admin for `Symfony\Cmf\Bundle\FoobarBundle\Document\Bar` generates `admin_cmf_foobar_bar` and `/cmf/foobar/bar`

### 2013-07-05

*  Remove qTip

### 2012-11-25

* [BC BREAK] change the configureSideMenu signature to use the AdminInterface

    -    protected function configureSideMenu(MenuItemInterface $menu, $action, Admin $childAdmin = null)
    +    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)

### 2012-08-05

* [BC BREAK] remove ``getListTemplate``, ``getEditTemplate``, ``getShowTemplate`` => just use ``getTemplate('edit')``
* add a ``delete`` template configuration entry

### 2012-06-05

* [BC BREAK] Fix bug introduces by 09334d81, now an admin must have the role ``ROLE_SONATA_ADMIN`` to see the top bar navigation

### 2012-05-31

* Update batch action confirmation message (breaks some translations)

### 2012-05-02

* [BC BREAK] add ProxyQueryInterface hint into the FilterInterface class

### 2012-03-07

* [BC BREAK] Extension : refactor the AdminExtensionInterface to use the proper AdminInterface, add a new configureQuery method
* Add export to xls format (html file)

### 2012-03-01

* [BC BREAK] Introduce Block Into the Admin Bundle
    * The AdminBundle has now a dependency to BlockBundle : http://github.com/sonata-project/SonataBlockBundle
    * The dashboard list is now a block, so it can be removed from the sonata_admin configuration.
    * More blocks can be created please follow the instruction here : https://sonata-project.org/bundles/block/master/doc/reference/your_first_block.html

* [BC BREAK] New configuration format for the dashboard section.

### 2012-02-28

* Add export feature to csv, xml, json
  The AdminBundle has now a new dependency to exporter : https://github.com/sonata-project/exporter

### 2011-09-04

* Add a delete option on widget with edit = list
* Refactoring the Menu/Breadcrumb management due to a change in the KnpLab Menu lib

### 2011-08-03

* remove property definitions
* add TypeGuesser for list/show/filter
* refactor [Form|List|Filter|Show]|Mapper to match the Symfony2 Form API
* add theme form definition from within the Admin class - default : SonataAdminBundle:Form:admin_fields.html.twig
* add new twig block type names to allows custom widget layouts per admin
* add show

### 2011-04-01

* migrate to the new form framework

### 2011-03-03

* add sortable option

### 2011-02-08

* add prototype for nested admin

### 2011-02-07

* refactor code to use builder (FormBuilder, DatagradBuilder, FilterBuilder)

### 2011-02-02

* starting to use the form.field_factory service
* update code to integrate the last symfony changes

### 2011-01-24

* add list mode
* add 'add_empty' option to association widget (ie: select)
* add country field type
* refactor the form creation

### 2011-01-18

* respect symfony conventions
* add new base edit template (standard and inline)
* admin instances are not singletons anymore
* add inline edition

### 2011-01-15

* respect symfony conventions
* add a FieldDescription
* register routes by using the getUrls from each Admin class
* build admin information "on demand"
* create an EntityAdmin and add new abstract method into the Admin class
* add inline edition for one-to-one association
