const Encore = require("@symfony/webpack-encore");

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore.setOutputPath("public/build/")
  .setPublicPath("/build")
  .setManifestKeyPrefix("build/")
  .addEntry("app", "./assets/app.js")
  .enableStimulusBridge("./assets/controllers.json")
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel(null, {
    useBuiltIns: false,
  })
  .enableSassLoader()
  .copyFiles({
    from: "./assets/images",
    to: "images/[path][name].[hash:8].[ext]",
    pattern: /\.(png|jpg|jpeg|gif|ico|svg|webp)$/,
  });

module.exports = Encore.getWebpackConfig();
