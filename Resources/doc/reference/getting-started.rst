
Getting started with the Sonata Admin
=====================================


Here is a checklist of what is needed to create an admin interface for one Entity:

**1. Setup the Sonata Admin dependencies.**

See the install documentation. Remember to enable translations.

**2. Setup the dependency to the ORM bundle you want to use.**

Either SonataDoctrineORMAdminBundle, SonataDoctrineMongoDBAdminBundle or SonataDoctrinePhpcrAdminBundle.

**3. Create an Admin class that extends the Sonata admin class**

The easiest way to do this is to extend the Sonata\AdminBundle\Admin\Admin class. Heres an example from the SonataNewsBundle:

::
   
   /*
    * This file is part of the Sonata package.
    *
    * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
    */
   
   namespace Sonata\NewsBundle\Admin;
   
   use Sonata\AdminBundle\Admin\Admin;
   use Sonata\AdminBundle\Datagrid\ListMapper;
   use Sonata\AdminBundle\Datagrid\DatagridMapper;
   use Sonata\AdminBundle\Validator\ErrorElement;
   use Sonata\AdminBundle\Form\FormMapper;
   
   class TagAdmin extends Admin
   {
       /**
        * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
        * @return void
        */
       protected function configureFormFields(FormMapper $formMapper)
       {
           $formMapper
               ->add('name')
               ->add('enabled', null, array('required' => false))
           ;
       }
   
       /**
        * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
        * @return void
        */
       protected function configureDatagridFilters(DatagridMapper $datagridMapper)
       {
           $datagridMapper
               ->add('name')
               ->add('posts')
           ;
       }
   
       /**
        * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
        * @return void
        */
       protected function configureListFields(ListMapper $listMapper)
       {
           $listMapper
               ->addIdentifier('name')
               ->add('slug')
               ->add('enabled')
           ;
       }
   
       /**
        * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
        * @param $object
        * @return void
        */
       public function validate(ErrorElement $errorElement, $object)
       {
           $errorElement
               ->with('name')
                   ->assertMaxLength(array('limit' => 32))
               ->end()
           ;
       }
   }
   


 
**4. Link the class to the dashboard.**
 
 The easiest way to do this is to create a default group in the dashboard config::
 
    dashboard_groups:
        default: ~
        
**5. Create an adminservice**

You need to create a service for the new admin class and link it into the framework by setting the sonata.admin tag.

::
 
   
   <container xmlns="http://symfony.com/schema/dic/services"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
       <services>
          <service id="sonata.admin.course" class="YourNS\AdminBundle\Admin\BlogAdmin">
             <tag name="sonata.admin" manager_type="orm" group="Posts" label="Blog"/>
             <argument />
             <argument>YourNS\AdminBundle\Entity\Course</argument>
             <argument>SonataAdminBundle:CRUD</argument>
             <call method="setTranslationDomain">
                 <argument>YourNSAdminBundle</argument>
             </call>    
         </service>
      </services>
   </container> 
   
That should be it!
