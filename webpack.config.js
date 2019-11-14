var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('./views')
    .setPublicPath('/')
    .addEntry('RestrictedController/profileAction', './.fronted/RestrictedController/profileAction/index.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableReactPreset()
    .cleanupOutputBeforeBuild(['*.js'], (options) => {
        options.dry = true;
    })
;

module.exports = Encore.getWebpackConfig();
