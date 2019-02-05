var Encore = require('@symfony/webpack-encore');

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
    //.splitEntryChunks()
    //.enableVersioning(Encore.isProduction())
;

let config = Encore.getWebpackConfig();
var path = require('path');
config.resolve.alias.jquery = path.join(__dirname, 'node_modules/jquery/dist/jquery');

module.exports = config;
