var Encore = require('@symfony/webpack-encore');
var path = require('path');

Encore
    .setOutputPath('src/Resources/public/dist')
    .setPublicPath('/bundles/sonataadmin/dist')
    .setManifestKeyPrefix('dist')
    .addEntry('sonata_admin', './src/Resources/public/js/sonata_admin.js')
    .autoProvidejQuery()
    .enableSassLoader()
    .enableSingleRuntimeChunk()
    .splitEntryChunks()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .addAliases({
        jquery: path.resolve(__dirname, 'node_modules/jquery/dist/jquery')
    })
    //.splitEntryChunks()
    //.enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
