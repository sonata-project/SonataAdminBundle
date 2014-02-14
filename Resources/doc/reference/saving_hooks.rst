Saving hooks
============

When a SonataAdmin is submitted for processing, two events are always called. One
is before any persistence layer interaction and the other is afterwards, the
events are named as follows:

- new object : ``prePersist($object)`` / ``postPersist($object)``
- edited object : ``preUpdate($object)`` / ``postUpdate($object)``
- deleted object : ``preRemove($object)`` / ``postRemove($object)``

It is worth noting that the update events are called whenever the Admin is successfully
submitted, regardless of whether there are any actual persistence layer events. This
differs from the use of preUpdate and postUpdate events in DoctrineORM and perhaps some
other persistence layers.

For example: if you submit an edit form without changing any of the values on the form
then there is nothing to change in the database and DoctrineORM would not fire the **Entity**
class's own ``preUpdate`` and ``postUpdate`` events. However, your **Admin** class's
``preUpdate``  and  ``postUpdate`` methods *are* called and this can be used to your
advantage.

.. note::

    When embedding one Admin within another, for example using the ``sonata_type_admin``
    field type, the child Admin's hooks are **not** fired.


Example used with the FOS/UserBundle
------------------------------------

The ``FOSUserBundle`` provides authentication features for your Symfony2 Project,
and is compatible with Doctrine ORM, Doctrine ODM and Propel. See
`FOSUserBundle on GitHub 
<https://github.com/FriendsOfSymfony/FOSUserBundle/>`_ for more information.

The user management system requires to perform specific call when the user
password or username are updated. This is how the Admin bundle can be used to
solve the issue by using the ``preUpdate`` saving hook.

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

.. configuration-block::

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
