/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Encore from "@symfony/webpack-encore";
import StyleLintPlugin from "stylelint-webpack-plugin";

Encore.setOutputPath('./src/Resources/public')
  .setPublicPath('/')
  .setManifestKeyPrefix('bundles/sonataadmin')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enablePostCssLoader()
  .enableVersioning(false)
  .enableSourceMaps(false)
  .autoProvidejQuery()
  .disableSingleRuntimeChunk()

  .enableStimulusBridge('./assets/shared/controllers.json')

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
      context: 'assets/theme/adminlte2/scss',
      emitWarning: true,
    })
  )

  .configureJsMinimizerPlugin((options) => {
    options.terserOptions = {
      output: { comments: false },
    };
    options.extractComments = false;
  })

  .copyFiles([
    { from: './assets/theme/adminlte2/images/', pattern: /\.(png|gif)$/, to: 'images/[name].[ext]' },
    {
      from: './assets/theme/adminlte2/node_modules/admin-lte/dist/css/skins/',
      pattern: /skin-.*\.min.css/,
      to: 'admin-lte-skins/[name].[ext]',
    },
    {
      from: './assets/theme/adminlte2/node_modules/select2/dist/js/i18n/',
      pattern: /\.js/,
      to: 'select2-locale/[name].[ext]',
    },
  ])

  .addEntry('app', './assets/theme/adminlte2/js/app.js');

export default await Encore.getWebpackConfig();
