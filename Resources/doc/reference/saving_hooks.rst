Saving hooks
============

When the model is persited upon on the object stated two Admin methods are always call. You can extends this
method to add custom business logic.

    - new object : ``prePersist($object)`` / ``postPersist($object)``
    - new object : ``preUpdate($object)`` / ``postUpdate($object)``
    - deleted object : ``preRemove($object)`` / ``postRemove($object)``


Example used with the FOS/UserBundle
------------------------------------

The ``FOSUserBundle`` provides authentication features for your Symfony2 Project. Compatible with Doctrine ORM & ODM.
See https://github.com/FriendsOfSymfony/UserBundle for more information.

The user management system requires to perform specific call when the user password or username are updated. This
is how the Admin bundle can be used to solve the issue by using the ``prePersist`` saving hook.

.. code-block:: php

    <?php
    namespace FOS\UserBundle\Admin\Entity;

    use Sonata\AdminBundle\Admin\Admin;
    use FOS\UserBundle\Model\UserManagerInterface;

    class UserAdmin extends Admin
    {
        protected $userManager;

        protected $form = array(
            'username',
            'email',
            'enabled',
            'plainPassword' => array('type' => 'string'),
            'locked',
            'expired',
            'credentialsExpired',
            'credentialsExpireAt',
            'groups'
        );

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
