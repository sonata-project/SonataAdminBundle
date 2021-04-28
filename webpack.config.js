var Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');

Encore
  .setOutputPath('./src/Resources/public/dist')
  .setPublicPath('/bundles/sonataadmin/dist')
  .setManifestKeyPrefix('bundles/sonataadmin/dist')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enablePostCssLoader()
  .enableVersioning(false)
  .enableSourceMaps(false)
  .enableEslintLoader({
    emitWarning: false
  })
  .autoProvidejQuery()
  .disableSingleRuntimeChunk()

  .configureCssMinimizerPlugin((options) => {
    options.minimizerOptions = {
      preset: ['default', { discardComments: { removeAll: true } }],
    };
  })

  .configureImageRule({
    filename: 'images/[name][ext]',
  })

  .configureFontRule({
    filename: 'fonts/[name][ext]',
  })

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
    { from: './node_modules/admin-lte/dist/css/skins/', pattern: /skin-.*\.min.css/, to: 'admin-lte-skins/[name].[ext]' },
    { from: './node_modules/select2/dist/js/i18n/', pattern: /\.js/, to: 'select2-locale/[name].[ext]' },
    { from: './node_modules/moment/locale/', to: 'moment-locale/[name].[ext]' },
  ])

  .addEntry('app', './assets/js/app.js')
;

module.exports = Encore.getWebpackConfig();
