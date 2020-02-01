var Encore = require('@symfony/webpack-encore');
var path = require('path');

const PluginPriorities = require('@symfony/webpack-encore/lib/plugins/plugin-priorities.js');
const webpack = require('webpack');

Encore
    .setOutputPath('src/Resources/public/dist')
    .setPublicPath('/bundles/sonataadmin/dist')
    .setManifestKeyPrefix('dist')
    .addEntry('sonata_admin', './src/Resources/public/js/sonata_admin.js')
    .autoProvidejQuery()
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableVersioning(false)
    .addAliases({
        jquery: path.resolve(__dirname, 'node_modules/jquery/dist/jquery')
    })

    // @TODO: remove for performance enhancement and add enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()
    //.enableSingleRuntimeChunk()
    //.splitEntryChunks()
    // @TODO: remove for performance enhancement and enable splitEntryChunks()
    .addPlugin(new webpack.optimize.LimitChunkCountPlugin({maxChunks: 1}), PluginPriorities.DefinePlugin)
;

module.exports = Encore.getWebpackConfig();
