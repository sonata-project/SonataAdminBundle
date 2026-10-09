Webpack
=======

By default, SonataAdminBundle comes with its own pre-rendered, built-in assets
(such as jQuery, Bootstrap, AdminLTE, and Select2) which are already registered
and automatically included in the default layout configuration.

.. note::

    You do not need to configure Webpack Encore to get the basic admin panel working.

However, if you want to extend the admin panel with your own custom frontend logic,
custom styles, or modern JavaScript components, follow the steps below to integrate Webpack Encore.

Copy assets
-----------

To make sure all core assets, fonts, and dependencies from SonataAdmin are properly mirrored into your
web-accessible directory, run the official installation command. This will copy the files into your
`assets` directory:

.. code-block:: bash

    php bin/console sonata:admin:install-assets [target_path] # by default path is "./assets"

Add Configuration to webpack
----------------------------

First, update your main `webpack.config.js`.

.. code-block:: js

    // ./webpack.config.mjs
    import { default as getAdminlte2Config } from './assets/sonata_admin/theme/adminlte2/sonata-webpack.config.mjs';
    // ...

    // set your project config
    const app_config = await Encore.getWebpackConfig();
    app_config.name = 'app';

    // set sonata admin_lte2 config
    const admin_config = await getAdminlte2Config(Encore);
    admin_config.name = 'admin_lte2';

    export default [app_config, admin_config];

.. note::

    It is multiple configs webpack solution. You can run encore for separate config by:

    .. code-block:: js

        yarn encore watch --config-name admin_lte2

First build
-----------

To avoid conflict with your project assets, ``sonata-webpack.config.mjs`` config set own ``node_modules``
in self directory. You need install packages (or update them after dependencies changes) to avoid encore errors.

    .. code-block:: bash

        cd assets/sonata_admin/theme/admin_lte2
        yarn install
        cd ../../../../ # back to project directory
        yarn encore dev --config-name admin_lte2

Set assets in layout
--------------------

Your custom assets for admin panel are generated. Now you need to make some changes in
``sonata_admin.assets`` configuration.



.. code-block:: yaml

    # Default configuration for extension with alias: "sonata_admin"
    sonata_admin:
        assets:
            stylesheets:
                # - bundles/sonataadmin/app.css         # replace this line
                - build/sonata_admin/adminlte2/app.css  # with new one
                - bundles/sonataform/app.css

            javascripts:
                # - bundles/sonataadmin/app.js          # replace this line
                - build/sonata_admin/adminlte2/app.cs          # replace this line
                - bundles/sonataform/app.js  # with new one

.. note::

    You can also override layout template by add ``sonata_admin_adminlte2`` endpoint.
