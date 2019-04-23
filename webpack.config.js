const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('Resources/public/assets')
    .setPublicPath('/assets')
    .cleanupOutputBeforeBuild()

    // Pages
    .addEntry('fine-uploader', './Resources/assets/fine-uploader.js')

    .disableSingleRuntimeChunk()

    .enableSourceMaps(!Encore.isProduction())
;

const config = Encore.getWebpackConfig();
config.watchOptions = {
    poll: true,
};

module.exports = config;
