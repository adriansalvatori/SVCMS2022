// Imports
const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

const sourcesDir = './sources';

module.exports = {
    context: __dirname,
    output: {
        path: path.resolve( __dirname, 'assets/dist' ),
    },
    externals: {
        jquery: 'jQuery',
        'js-cookie': 'Cookies',
    },
    entry: {
        'checkoutwc-vendor': [
            require.resolve( 'dom4/build/dom4.max.js' ),
            require.resolve( 'jquery-first-event/dist/index.js' ),
            require.resolve( 'modaal/dist/js/modaal.min' ),
            require.resolve( 'EasyTabs/lib/jquery.easytabs.min' ),
            require.resolve( 'garlicjs/dist/garlic.min.js' ),
            require.resolve( 'parsleyjs/dist/parsley.min.js' ),
            require.resolve( 'parsleyjs/dist/i18n/de.js' ),
            require.resolve( 'parsleyjs/dist/i18n/da.js' ),
            require.resolve( 'parsleyjs/dist/i18n/es.js' ),
            require.resolve( 'parsleyjs/dist/i18n/el.js' ),
            require.resolve( 'parsleyjs/dist/i18n/el.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/fi.js' ),
            require.resolve( 'parsleyjs/dist/i18n/fi.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/fr.js' ),
            require.resolve( 'parsleyjs/dist/i18n/it.js' ),
            require.resolve( 'parsleyjs/dist/i18n/ja.js' ),
            require.resolve( 'parsleyjs/dist/i18n/nl.js' ),
            require.resolve( 'parsleyjs/dist/i18n/no.js' ),
            require.resolve( 'parsleyjs/dist/i18n/hu.js' ),
            require.resolve( 'parsleyjs/dist/i18n/pl.js' ),
            require.resolve( 'parsleyjs/dist/i18n/sv.js' ),
            require.resolve( 'parsleyjs/dist/i18n/sv.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/he.js' ),
            require.resolve( 'parsleyjs/dist/i18n/he.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/sk.js' ),
            require.resolve( 'parsleyjs/dist/i18n/sk.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/sl.js' ),
            require.resolve( 'parsleyjs/dist/i18n/sl.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/hu.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/pt-br' ),
            require.resolve( 'parsleyjs/dist/i18n/pt-pt' ),
            require.resolve( 'parsleyjs/dist/i18n/zh_cn.js' ),
            require.resolve( 'parsleyjs/dist/i18n/ru.js' ),
            require.resolve( 'parsleyjs/dist/i18n/ru.extra' ),
            require.resolve( 'parsleyjs/dist/i18n/en.js' ),
        ],
        'checkoutwc-front': [
            `${sourcesDir}/ts/checkout.ts`,
            `${sourcesDir}/scss/frontend/checkout.scss`,
        ],
        'checkoutwc-order-pay': [
            `${sourcesDir}/ts/order-pay.ts`,
            `${sourcesDir}/scss/frontend/order-pay.scss`,
        ],
        'checkoutwc-thank-you': [
            `${sourcesDir}/ts/thank-you.ts`,
            `${sourcesDir}/scss/frontend/thank-you.scss`,
        ],
        'checkoutwc-admin': [
            require.resolve( 'jquery-validation/dist/jquery.validate.js' ),
            `${sourcesDir}/ts/admin/admin.ts`,
            `${sourcesDir}/scss/admin/admin.scss`,
        ],
        'checkoutwc-admin-onboarding': [
            `${sourcesDir}/ts/admin/onboarding.tsx`,
            `${sourcesDir}/scss/admin/onboarding.scss`,
        ],
        'checkoutwc-side-cart': [
            `${sourcesDir}/ts/side-cart.ts`,
            `${sourcesDir}/scss/frontend/side-cart.scss`,
        ],
    },
    resolve: {
        extensions: [ '.ts', '.tsx', '.js', '.json', '.scss' ],
    },
    stats: {
        colors: true,
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                loader: 'ts-loader',
            },
            {
                test: /\.(scss|css)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: true,
                            url: false,
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: true,
                        },
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: true,
                        },
                    },
                ],
            },
        ],
    },
    plugins: [
        new CleanWebpackPlugin(),
    ],
};
