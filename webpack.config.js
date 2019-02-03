var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/dist')
    .setPublicPath('/bundles/sonataadmin/dist')
    .setManifestKeyPrefix('dist')
    .addEntry('sonata_admin', './src/Resources/public/js/sonata_admin.js')
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery'
    })
    .enableSassLoader()
    .enableSingleRuntimeChunk()
    .splitEntryChunks()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.splitEntryChunks()
    //.enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();