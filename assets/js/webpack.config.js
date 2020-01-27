let Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('./react')
    .setPublicPath('/')
    .addEntry('RestrictedController', './.components/RestrictedController/index.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableReactPreset()
;

module.exports = Encore.getWebpackConfig();
