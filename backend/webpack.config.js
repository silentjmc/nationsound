const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

const isProduction = Encore.isProduction();
const publicPath = isProduction ? '/admin/build' : '/build';
//const publicPath = isProduction ? '.' : '/build';
//

Encore
    .setOutputPath('public/build/')
    //.setPublicPath('/build')
    //.setManifestKeyPrefix('build/')
    .setPublicPath(publicPath)
    .setManifestKeyPrefix('')
    .addEntry('app', './assets/app.js')
    .addEntry('register', './assets/js/register-form.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
