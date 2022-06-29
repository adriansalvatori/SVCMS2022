// Imports
const path = require('path');
const fs = require('fs');
const WebpackNotifierPlugin = require('webpack-notifier');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = mode => {
	const localModulesDir = path.resolve(__dirname, "node_modules");

	let plugins = [
		new MiniCssExtractPlugin({
			// Options similar to the same options in webpackOptions.output
			// both options are optional
			filename: `style${(mode !== "development") ? '.min' : ''}.css`
		}),
	];

	if(mode === "development") {
		plugins.push(new WebpackNotifierPlugin({ alwaysNotify: true }));
	} else {
		plugins.push(new OptimizeCssAssetsPlugin())
	}

	return {
		mode: mode,
		context: __dirname,
		output: {
			filename: `theme${(mode !== "development") ? ".min" : ''}.js`,
			path: __dirname,
		},
		devtool: (mode === "development") ? "source-map" : "",
		entry: {
			"default": ["./sources/ts/Theme.ts", "./sources/scss/style.scss"]
		},
		stats: {
			colors: true
		},
		resolve: {
			extensions: ['.scss', '.css', '.ts', '.js'],
			modules: [localModulesDir]
		},
		module: {
			rules: [
				{
					test: /\.ts$/,
					loader: ['ts-loader']
				},
				{
					test: /\.(scss|css)$/,
					use: [
						MiniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: true,
								minimize: false,
								url: false
							}
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: true
							}
						}
					]
				}
			]
		},
		plugins: plugins
	};
};