const path = require("path");
const fs = require("fs");
const defaultConfig = require("@wordpress/scripts/config/webpack.config.js");
const { getWebpackEntryPoints } = require("@wordpress/scripts/utils/config");
const CopyWebpackPlugin = require("copy-webpack-plugin");

var generalConfig = {
  ...defaultConfig,
  entry: {
    ...getWebpackEntryPoints(),
  },
  plugins: [
    ...(defaultConfig.plugins || []),
    new CopyWebpackPlugin({
      patterns: [
        { from: "src/static", to: "static", noErrorOnMissing: true }, // Copies global images
      ],
    }),
  ],
};

var customConfig = {
  ...defaultConfig,
  entry: {
    postDuplicator: "./src/index.js",
  },
  output: {
    filename: "[name].js",
    path: path.resolve(process.cwd(), "build"),
  },
  plugins: [
    ...(defaultConfig.plugins || []),
    new CopyWebpackPlugin({
      patterns: [
        { from: "src/static", to: "static", noErrorOnMissing: true }, // Copies global images
      ],
    }),
  ],
};

module.exports = [generalConfig, customConfig];
