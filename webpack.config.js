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
    .configureBabel(function (babelConfig) {
        babelConfig.plugins.push("@babel/plugin-proposal-class-properties");
    })
;

module.exports = Encore.getWebpackConfig();
