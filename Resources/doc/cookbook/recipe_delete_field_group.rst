Deleting a Group of Fields from an Admin
========================================

In some cases, when you extend existing Admins, you might want to delete fields from the admin, or make them not show.
You could delete every field by hand, using the ``FormMapper``s ``remove`` method.

    .. code-block:: php

        class UserAdmin extends \Sonata\UserBundle\Admin\Model\UserAdmin {

            protected function configureFormFields(FormMapper $formMapper)
            {
                parent::configureFormFields($formMapper);

                $formMapper->remove('facebookName');
                $formMapper->remove('twitterUid');
                $formMapper->remove('twitterName');
                $formMapper->remove('gplusUid');
                $formMapper->remove('gplusName');

            }
        }


This works, as long as the extended Admin does not use Groups to organize it's field. In the above example, we try to remove all fields from the User Admin, that comes with the SonataUserBundle.
However, since the fields we deleted, are all part of the 'Social' Group of the form, the fields will be deleted and the empty group will stay.
For this case, the FormMapper comes with a method, which allows you to get rid of a whole form group: ``removeGroup``

    .. code-block:: php

        class UserAdmin extends \Sonata\UserBundle\Admin\Model\UserAdmin {

            protected function configureFormFields(FormMapper $formMapper)
            {
                parent::configureFormFields($formMapper);

                $formMapper->removeGroup('Social', 'User');

            }
        }

This will remove the whole 'Social' group from the form, which happens to contain all the fields, we deleted manually in the first example. The second argument is the name of the tab, the group belongs to.
This is optional. However, when not provided, it will be assumed that you mean the 'default' tab. If the group is on another tab, it won't be removed, when this is not provided.
There is a third optional argument for the method, which let's you choose, whether or not, tabs are also removed, if you happen to remove all groups of a tab. This behaviour is disabled by default, but
can be enabled, by setting the third argument of ``removeGroup`` to ``true``
