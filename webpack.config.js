var Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');

Encore
  .setOutputPath('./src/Resources/public/dist')
  .setPublicPath('/bundles/sonataadmin/dist')
  .setManifestKeyPrefix('bundles/sonataadmin/dist')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enableVersioning(false)
  .disableSingleRuntimeChunk()

  .addPlugin(
    new StyleLintPlugin({
      context: 'assets/scss',
    })
  )

  .configureTerserPlugin((options) => {
    options.terserOptions = {
      output: { comments: false }
    };
    options.extractComments = false;
  })

  .copyFiles([
    // copy jQuery UI files
    // copy Bootstrap DateTime Picker files
    { from: './node_modules/jquery/dist/', pattern: /jquery\.min\.js/, to: 'jquery/[name].[ext]' },
    { from: './node_modules/jquery-slimscroll/', pattern: /jquery\.slimscroll\.min\.js/, to: 'jquery-slimscroll/[name].[ext]' },
    { from: './node_modules/jquery.scrollto/', pattern: /jquery\.scrollTo\.min\.js/, to: 'jquery-scrollto/[name].[ext]' },
    { from: './node_modules/jquery-form/', pattern: /jquery\.form\.js/, to: 'jquery-form/[name].[ext]' },
    { from: './node_modules/admin-lte/dist/css/skins/', pattern: /skin-.*\.min.css/, to: 'admin-lte/css/skins/[name].[ext]' },
    { from: './node_modules/admin-lte/dist/css/alt/', pattern: /AdminLTE-without-plugins\.min\.css/, to: 'admin-lte/css/[name].[ext]' },
    { from: './node_modules/admin-lte/dist/img/', to: 'admin-lte/img/[path][name].[ext]' },
    { from: './node_modules/admin-lte/dist/js/', pattern: /adminlte\.min\.js/, to: 'admin-lte/js/[name].[ext]' },
    { from: './node_modules/bootstrap/dist/css/', pattern: /bootstrap\.min\.css/, to: 'bootstrap/[name].[ext]' },
    { from: './node_modules/bootstrap/dist/js/', pattern: /bootstrap\.min\.js/, to: 'bootstrap/[name].[ext]' },
    { from: './node_modules/components-font-awesome/css/', pattern: /font-awesome\.min\.css/, to: 'components-font-awesome/css/[name].[ext]' },
    { from: './node_modules/components-font-awesome/fonts/', to: 'components-font-awesome/fonts/[name].[ext]' },
    { from: './node_modules/icheck/skins/square/', pattern: /blue/, to: 'icheck/skins/square/[name].[ext]' },
    { from: './node_modules/icheck/', pattern: /icheck\.min\.js/, to: 'icheck/[name].[ext]' },
    { from: './node_modules/select2/dist/css/', pattern: /select2\.min\.css/, to: 'select2/[name].[ext]' },
    { from: './node_modules/select2/dist/js/', pattern: /select2\.full\.min\.js/, to: 'select2/[name].[ext]' },
    { from: './node_modules/select2/dist/js/i18n/', pattern: /\.js/, to: 'select2/locale/[name].[ext]' },
    { from: './node_modules/select2-bootstrap-theme/dist/', pattern: /select2-bootstrap\.min\.css/, to: 'select2-bootstrap-theme/[name].[ext]' },
    { from: './node_modules/x-editable/dist/bootstrap3-editable/css/', to: 'x-editable/[name].[ext]' },
    { from: './node_modules/x-editable/dist/bootstrap3-editable/img/', to: 'x-editable/img/[name].[ext]' },
    { from: './node_modules/x-editable/dist/bootstrap3-editable/js/', pattern: /bootstrap-editable\.min\.js/, to: 'x-editable/[name].[ext]' },
    { from: './node_modules/moment/min/', pattern: /moment\.min\.js/, to: 'moment/[name].[ext]' },
    { from: './node_modules/moment/locale/', to: 'moment/locale/[name].[ext]' },
    { from: './node_modules/waypoints/lib/shortcuts/', pattern: /sticky\.min\.js/, to: 'waypoints/shortcuts/[name].[ext]' },
    { from: './node_modules/waypoints/lib/', pattern: /jquery\.waypoints\.min\.js/, to: 'waypoints/[name].[ext]' },
    { from: './node_modules/readmore-js/', pattern: /readmore\.min\.js/, to: 'readmore-js/[name].[ext]' },
    { from: './node_modules/masonry-layout/dist/', pattern: /masonry\.pkgd\.min\.js/, to: 'masonry-layout/[name].[ext]' }
  ])

  .addStyleEntry('app', './assets/scss/app.scss')
;

module.exports = Encore.getWebpackConfig();
