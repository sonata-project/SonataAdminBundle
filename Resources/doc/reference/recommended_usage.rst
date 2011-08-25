Recommended Usage
=======================

The recommended usage is to employ the method descriptions for list, forms, and filters.

Example
-------

.. code-block:: php

    <?php

    //wrong

    protected $list = ...
 
    public function configureFormFields(FormMapper $formMapper) {
        $formMapper->add()...
    }
 
    protected $datagrid = ...
 
    //right

    public function configureListFields(ListMapper $list) {
        $list->add()...
    }

    public function configureFormFields(FormMapper $formMapper) {
        $formMapper->add()...
    }
  
    public function configureDatagridFilters(DatagridMapper $datagrid) {
        $datagrid->add()...
    }


This is also explained in: http://www.craftitonline.com/2011/07/sonataadminbundle-catch-up
where an odd error is reported.

