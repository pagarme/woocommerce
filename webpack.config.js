const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

module.exports = {
  ...defaultConfig,
  plugins: [
    ...defaultConfig.plugins.filter(
      (plugin) =>
        plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
    ),
    new DependencyExtractionWebpackPlugin(),
  ],
  entry: {
		pix: './assets/javascripts/front/reactCheckout/payments/pix/index.js',
		billet: './assets/javascripts/front/reactCheckout/payments/billet/index.js',
	},
};