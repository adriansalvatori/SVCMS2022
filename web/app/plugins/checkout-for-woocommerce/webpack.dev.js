// Imports
const { merge } = require( 'webpack-merge' );
const WebpackNotifierPlugin = require( 'webpack-notifier' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const common = require( './webpack.common.js' );

const version = process.env.npm_package_version;

module.exports = merge( common, {
    mode: 'development',
    output: {
        filename: `js/[name]-${version}.js`,
    },
    devtool: 'source-map',
    plugins: [
        new WebpackNotifierPlugin( { alwaysNotify: true } ),
        new MiniCssExtractPlugin( {
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: `css/[name]-${version}.css`,
        } ),
    ],
} );
