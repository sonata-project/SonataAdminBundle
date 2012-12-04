Saving hooks
============

When the model is persisted upon on the stated object two Admin methods are
always called. You can extend this method to add custom business logic.

    - new object : ``prePersist($object)`` / ``postPersist($object)``
    - edited object : ``preUpdate($object)`` / ``postUpdate($object)``
    - deleted object : ``preRemove($object)`` / ``postRemove($object)``


Example used with the FOS/UserBundle
------------------------------------

The ``FOSUserBundle`` provides authentication features for your Symfony2 Project,
and is compatible with Doctrine ORM & ODM. See 
https://github.com/FriendsOfSymfony/UserBundle for more information.

The user management system requires to perform specific call when the user 
password or username are updated. This is how the Admin bundle can be used to 
solve the issue by using the ``prePersist`` saving hook.

.. code-block:: php

    <?php
    namespace FOS\UserBundle\Admin\Entity;

    use Sonata\AdminBundle\Admin\Admin;
    use FOS\UserBundle\Model\UserManagerInterface;

    class UserAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('username')
                    ->add('email')
                    ->add('plainPassword', 'text')
                ->end()
                ->with('Groups')
                    ->add('groups', 'sonata_type_model', array('required' => false))
                ->end()
                ->with('Management')
                    ->add('roles', 'sonata_security_roles', array( 'multiple' => true))
                    ->add('locked', null, array('required' => false))
                    ->add('expired', null, array('required' => false))
                    ->add('enabled', null, array('required' => false))
                    ->add('credentialsExpired', null, array('required' => false))
                ->end()
            ;
        }
        
        public function preUpdate($user)
        {
            $this->getUserManager()->updateCanonicalFields($user);
            $this->getUserManager()->updatePassword($user);
        }

        public function setUserManager(UserManagerInterface $userManager)
        {
            $this->userManager = $userManager;
        }

        /**
         * @return UserManagerInterface
         */
        public function getUserManager()
        {
            return $this->userManager;
        }
    }


The service declaration where the ``UserManager`` is injected into the Admin class.

.. code-block:: xml

    <service id="fos.user.admin.user" class="%fos.user.admin.user.class%">
        <tag name="sonata.admin" manager_type="orm" group="fos_user" />
        <argument />
        <argument>%fos.user.admin.user.entity%</argument>
        <argument />

        <call method="setUserManager">
            <argument type='service' id='fos_user.user_manager' />
        </call>
    </service>
