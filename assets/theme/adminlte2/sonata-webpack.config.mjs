import path from 'path';
import Encore from '@symfony/webpack-encore';
import StyleLintPlugin from 'stylelint-webpack-plugin';

export default function getSonataAdminThemeWebpackConfig() {
  const currentEnv = process.env.NODE_ENV || 'dev';
  Encore.configureRuntimeEnvironment(currentEnv);

  Encore.reset();

  Encore.setOutputPath('./public/build/sonata_admin/adminlte2')
    .setPublicPath('/build/sonata_admin/adminlte2')
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    .enablePostCssLoader()
    .enableVersioning(false)
    .enableSourceMaps(false)
    .autoProvidejQuery()
    .disableSingleRuntimeChunk()

    // load shared controllers
    // .enableStimulusBridge(path.join(import.meta.dirname, '../../shared/controllers.json'))

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
        emitWarning: true,
      }),
    );

  Encore.configureJsMinimizerPlugin((options) => {
    options.terserOptions = {
      output: { comments: false },
    };
    options.extractComments = false;
  })

    .copyFiles([
      {
        from: path.join(import.meta.dirname, 'images'),
        pattern: /\.(png|gif)$/,
        to: 'images/[name].[ext]',
      },
      {
        from: path.join(import.meta.dirname, 'node_modules/admin-lte/dist/css/skins'),
        pattern: /skin-.*\.min.css/,
        to: 'admin-lte-skins/[name].[ext]',
      },
      {
        from: path.join(import.meta.dirname, 'node_modules/select2/dist/js/i18n'),
        pattern: /\.js/,
        to: 'select2-locale/[name].[ext]',
      },
    ])

    .addEntry('sonata_admin_adminlte2', path.join(import.meta.dirname, 'js/app.js'));

  const config = Encore.getWebpackConfig();

  // --- FORCE LOCAL NODE_MODULES ---
  config.resolve = config.resolve || {};
  config.resolve.modules = [
    // first search in local node_modules
    path.resolve(import.meta.dirname, '/node_modules'),
    'node_modules', // fallback
  ];

  return config;
}
