var Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('./src/Resources/public/dist')
  .setPublicPath('/bundles/sonataadmin/dist')
  .setManifestKeyPrefix('bundles/sonataadmin/dist')

  .cleanupOutputBeforeBuild()
  .enableVersioning(false)
  .disableSingleRuntimeChunk()

  // copy jQuery UI files
  // copy Bootstrap DateTime Picker files

  // copy jQuery files
  .copyFiles({
    from: './node_modules/jquery/dist/',
    pattern: /jquery\.min\.js/,
    to: 'jquery/[name].[ext]'
  })

  // copy jQuery SlimScroll files
  .copyFiles({
    from: './node_modules/jquery-slimscroll/',
    pattern: /jquery\.slimscroll\.min\.js/,
    to: 'jquery-slimscroll/[name].[ext]'
  })

  // copy jQuery scrollTo files
  .copyFiles({
    from: './node_modules/jquery.scrollto/',
    pattern: /jquery\.scrollTo\.min\.js/,
    to: 'jquery-scrollto/[name].[ext]'
  })

  // copy jQuery Form files
  .copyFiles({
    from: './node_modules/jquery-form/',
    pattern: /jquery\.form\.js/,
    to: 'jquery-form/[name].[ext]'
  })

  // copy AdminLTE files
  .copyFiles({
    from: './node_modules/admin-lte/dist/css/',
    pattern: /AdminLTE\.min\.css/,
    to: 'admin-lte/css/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/admin-lte/dist/img/',
    to: 'admin-lte/img/[path][name].[ext]'
  })
  .copyFiles({
    from: './node_modules/admin-lte/dist/js/',
    pattern: /adminlte\.min\.js/,
    to: 'admin-lte/js/[name].[ext]'
  })

  // copy Bootstrap files
  .copyFiles({
    from: './node_modules/bootstrap/dist/css/',
    pattern: /bootstrap\.min\.css/,
    to: 'bootstrap/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/bootstrap/dist/js/',
    pattern: /bootstrap\.min\.js/,
    to: 'bootstrap/[name].[ext]'
  })

  // copy Components Font Awesome files
  .copyFiles({
    from: './node_modules/components-font-awesome/css/',
    pattern: /font-awesome\.min\.css/,
    to: 'components-font-awesome/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/components-font-awesome/fonts/',
    to: 'components-font-awesome/fonts/[name].[ext]'
  })

  // copy Ionicons files
  .copyFiles({
    from: './node_modules/ionicons/css/',
    pattern: /ionicons\.min\.css/,
    to: 'ionicons/css/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/ionicons/fonts/',
    to: 'ionicons/fonts/[name].[ext]'
  })

  // copy iCheck files
  .copyFiles({
    from: './node_modules/icheck/skins/square/',
    pattern: /blue/,
    to: 'icheck/skins/square/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/icheck/',
    pattern: /icheck\.min\.js/,
    to: 'icheck/[name].[ext]'
  })

  // copy Select2 files
  .copyFiles({
    from: './node_modules/select2/',
    pattern: /select2\.(css|js)/,
    to: 'select2/[name].[ext]'
  })

  // copy Select2 Bootstrap css files
  .copyFiles({
    from: './node_modules/select2-bootstrap-css/',
    pattern: /select2-bootstrap\.min\.css/,
    to: 'select2-bootstrap-css/[name].[ext]'
  })

  // copy xEditable files
  .copyFiles({
    from: './node_modules/x-editable/dist/bootstrap3-editable/css/',
    to: 'x-editable/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/x-editable/dist/bootstrap3-editable/img/',
    to: 'x-editable/img/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/x-editable/dist/bootstrap3-editable/js/',
    pattern: /bootstrap-editable\.min\.js/,
    to: 'x-editable/[name].[ext]'
  })

  // copy Moment Form files
  .copyFiles({
    from: './node_modules/moment/min/',
    pattern: /moment\.min\.js/,
    to: 'moment/[name].[ext]'
  })

  // copy Waypoints files
  .copyFiles({
    from: './node_modules/waypoints/lib/shortcuts/',
    pattern: /sticky\.min\.js/,
    to: 'waypoints/shortcuts/[name].[ext]'
  })
  .copyFiles({
    from: './node_modules/waypoints/lib/',
    pattern: /jquery\.waypoints\.min\.js/,
    to: 'waypoints/[name].[ext]'
  })

  // copy Readmore js files
  .copyFiles({
    from: './node_modules/readmore-js/',
    pattern: /readmore\.min\.js/,
    to: 'readmore-js/[name].[ext]'
  })

  // copy Masonry files
  .copyFiles({
    from: './node_modules/masonry-layout/dist/',
    pattern: /masonry\.pkgd\.min\.js/,
    to: 'masonry-layout/[name].[ext]'
  })
;

module.exports = Encore.getWebpackConfig();
