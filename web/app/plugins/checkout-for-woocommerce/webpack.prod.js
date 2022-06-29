// Imports
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const WebpackShellPluginNext = require( 'webpack-shell-plugin-next' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const { merge } = require( 'webpack-merge' );
const common = require( './webpack.common.js' );

const version = process.env.npm_package_version;

const productionDir = './dist';
const outPath = `${productionDir}/checkout-for-woocommerce`;
const zipName = `checkout-for-woocommerce-${version}.zip`;

const prodConfig = {
    mode: 'production',
    output: {
        filename: `js/[name]-${version}.min.js`,
    },
    plugins: [
        new MiniCssExtractPlugin( {
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: `css/[name]-${version}.min.css`,
        } ),
        new WebpackShellPluginNext( {
            onBuildStart: {
                scripts: [
                    `rm -rf ${productionDir} && mkdir -p ${productionDir}`,
                ],
            },
            onBuildEnd: {
                scripts: [
                    // eslint-disable-next-line max-len
                    `npx cpy --parents '.' '!./dist' '!./tests' '!./cypress' '!./build' '!./**/node_modules' '!./**/phpunit' '!./cypress.env.json' '!./cypress.overrides.json' ${outPath} && cd ${productionDir} && zip --recurse-paths ${zipName} ./checkout-for-woocommerce`,
                ],
            },
        } ),
    ],
    optimization: {
        minimizer: [
            '...',
            new CssMinimizerPlugin(),
        ],
    },
};

module.exports = merge( common,  prodConfig );
