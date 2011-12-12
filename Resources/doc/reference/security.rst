Security
========

The security part is managed by a ``SecurityHandler``, the bundle comes with 2 handlers

  - ``sonata.admin.security.handler.acl`` : ACL and ROLES to handle permissions
  - ``sonata.admin.security.handler.noop`` : always return true, can be used with the Symfony2 firewall

The default value is ``sonata.admin.security.handler.noop``, if you want to change the default value
you can set the ``security_handler`` to ``sonata.admin.security.handler.acl``.

.. code-block:: yaml

    sonata_admin:
        security_handler: sonata.admin.security.handler.acl

The following section explains how to set up ACL with the ``FriendsOfSymfony/UserBundle``.

ACL and FriendsOfSymfony/UserBundle
-----------------------------------

If you want an easy way to handle users, please use :

 - https://github.com/FriendsOfSymfony/FOSUserBundle : handle users and group stored from RDMS or MongoDB
 - https://github.com/sonata-project/SonataUserBundle : integrate the ``FriendsOfSymfony/UserBundle`` with
   the ``AdminBundle``

The security integration is a work in progress and have some knows issues :
 - ACL permissions are immutables
 - Only one PermissionMap can be defined


Configuration
~~~~~~~~~~~~~

    - The following configuration defines :

        - the ``FriendsOfSymfony/FOSUserBundle`` as a security provider
        - the login form for authentification
        - the access control : resources with related required roles, the important part is the admin configuration
        - the ``acl`` option enable the ACL.

.. code-block:: yaml

    parameters:
        # ... other parameters
        security.acl.permission.map.class: Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap

    security:
        providers:
            fos_userbundle:
                id: fos_user.user_manager

        firewalls:
            main:
                pattern:      .*
                form-login:
                    provider:       fos_userbundle
                    login_path:     /login
                    use_forward:    false
                    check_path:     /login_check
                    failure_path:   null
                logout:       true
                anonymous:    true

        access_control:
            # The WDT has to be allowed to anonymous users to avoid requiring the login with the AJAX request
            - { path: ^/wdt/, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/profiler/, role: IS_AUTHENTICATED_ANONYMOUSLY }

            # AsseticBundle paths used when using the controller for assets
            - { path: ^/js/, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/css/, role: IS_AUTHENTICATED_ANONYMOUSLY }

            # URL of FOSUserBundle which need to be available to anonymous users
            - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/login_check$, role: IS_AUTHENTICATED_ANONYMOUSLY } # for the case of a failed login
            - { path: ^/user/new$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/check-confirmation-email$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/confirm/, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/confirmed$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/request-reset-password$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/send-resetting-email$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/check-resetting-email$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/user/reset-password/, role: IS_AUTHENTICATED_ANONYMOUSLY }

            # Secured part of the site
            # This config requires being logged for the whole site and having the admin role for the admin part.
            # Change these rules to adapt them to your needs
            - { path: ^/admin/, role: ROLE_ADMIN }
            - { path: ^/.*, role: IS_AUTHENTICATED_ANONYMOUSLY }


        role_hierarchy:
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_SONATA_ADMIN, ROLE_ALLOWED_TO_SWITCH]

        acl:
            connection: default

- Install the ACL tables ``php app/console init:acl``

- Create a new user :

.. code-block::

    # php app/console fos:user:create
    Please choose a username:root
    Please choose an email:root@domain.com
    Please choose a password:root
    Created user root


- Promote an user as super admin :

.. code-block::

    # php app/console fos:user:promote root
    User "root" has been promoted as a super administrator.

If you have Admin classes, you can install the related CRUD ACL rules :

.. code-block::

    # php app/console sonata:admin:setup-acl
    Starting ACL AdminBundle configuration
    > install ACL for sonata.media.admin.media
       - add role: ROLE_SONATA_MEDIA_ADMIN_MEDIA_EDIT, ACL: ["EDIT"]
       - add role: ROLE_SONATA_MEDIA_ADMIN_MEDIA_LIST, ACL: ["LIST"]
       - add role: ROLE_SONATA_MEDIA_ADMIN_MEDIA_CREATE, ACL: ["CREATE"]
       - add role: ROLE_SONATA_MEDIA_ADMIN_MEDIA_DELETE, ACL: ["DELETE"]
       - add role: ROLE_SONATA_MEDIA_ADMIN_MEDIA_OPERATOR, ACL: ["OPERATOR"]
    ... skipped ...

If you try to access to the admin class you should see the login form, just logon with the ``root`` user.

An Admin is displayed in the dashboard (and menu) when the user has the role ``LIST``. To change this override the ``showIn`` 
method in the Admin class.

Usage
~~~~~

Everytime you create a new ``Admin`` class, you should create start the command ``php app/console sonata:admin:setup-acl``
so the ACL database will be updated with the latest masks and roles informations.