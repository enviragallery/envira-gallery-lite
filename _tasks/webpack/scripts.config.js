
const webpack = require('webpack');

module.exports = {
	module: {
		rules: [
			{
				test: /\.(js|jsx)$/,
				exclude: /(node_modules)/,
				loader: 'babel-loader',
				query: {
					presets: ['@babel/preset-env'],
				},
			},
		],
	},
	mode: 'production',
	externals: {
		jquery: 'jQuery',
	},
	optimization: {
		minimize: false,
	},
	plugins: [
		new webpack.ProvidePlugin({
			$: 'jquery',
			jQuery: 'jquery',
			responsivelyLazy: 'responsivelyLazy'
		}),
	],
};
