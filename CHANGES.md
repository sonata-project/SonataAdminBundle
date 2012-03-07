CHANGELOG
=========

### 2012-03-07

* [BC BREAK] Extension : refactor the AdminExtensionInterface to use the proper AdminInterface, add a new configureQuery method
* Add export to xls format (html file)

### 2012-03-01

* [BC BREAK] Introduce Block Into the Admin Bundle
    * The AdminBundle has now a dependency to BlockBundle : http://github.com/sonata-project/SonataBlockBundle
    * The dashboard list is now a block, so it can be removed from the sonata_admin configuration.
    * More blocks can be created please follow the instruction here : http://sonata-project.org/bundles/block/master/doc/reference/your_first_block.html

* [BC BREAK] New configuration format for the dasboard section.

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

### 18/01/2011

* respect symfony conventions
* add new base edit template (standard and inline)
* admin instances are not singletons anymore
* add inline edition

### 15/01/2011

* respect symfony conventions
* add a FieldDescription
* register routes by using the getUrls from each Admin class
* build admin information "on demand"
* create an EntityAdmin and add new abstract method into the Admin class
* add inline edition for one-to-one association