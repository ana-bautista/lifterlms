/**
 * Webpack config
 *
 * @package LifterLMS_Groups/Scripts/Dev
 *
 * @since 1.3.0
 * @version 1.2.1
 */

// Deps.
const
	cssExtract = require( 'mini-css-extract-plugin' ),
	cssRTL     = require( 'webpack-rtl-plugin' ),
	config     = require( '@wordpress/scripts/config/webpack.config' ),
	depExtract = require( '@wordpress/dependency-extraction-webpack-plugin' )
	path       = require( 'path' );

/**
 * Used by dependency extractor to handle requests to convert names of scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 *
 * @param {string} request External script slug/id.
 * @return {String|Array} A string
 */
function requestToExternal( request ) {

	if ( 'llms-quill' === request ) {
		return 'Quill';
	} else if ( 'llms-izimodal' === request ) {
		return [ 'jQuery', 'iziModal' ];
	} else if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
		return request.split( '/' );
	}

}

/**
 * Used by dependency extractor to handle requests to scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 *
 * @param {string} request External script slug/id.
 * @return {String|Array} A string
 */
function requestToHandle( request ) {
	if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
		return 'llms';
	}
}

/**
 * Configure the `entry` object of the webpack config file.
 *
 * @since 1.2.1
 *
 * @param {String[]} js     Array of JS file slugs.
 * @return {Object} Webpack config entry object.
 */
function setupEntry( js ) {

	const entry = {};
	js.forEach( file => {
		entry[ file ] = path.resolve( process.cwd(), 'assets/src/js/', `${ file }.js` );
	} );

	return entry;

}

/**
 * Setup the `plugins` array of the webpack config file.
 *
 * @since 1.2.1
 *
 * @param {Object[]} plugins Array of plugin objects or classes.
 * @param {String[]} css     Array of CSS file slugs.
 * @param {String}   prefix  File prefix.
 * @return {Object[]} Array of plugin objects or classes.
 */
function setupPlugins( plugins, css, prefix ) {

	// Delete the css extractor implemented in the default config (we'll replace it with our own later).
	plugins.forEach( ( plugin, index ) => {
		if ( 'MiniCssExtractPlugin' === plugin.constructor.name ) {
			config.plugins.splice( index, 1 );
		}
	} );

	css.forEach( file => {

		// Extract CSS.
		plugins.push( new cssExtract( {
			filename: `css/${ prefix }[name].css`,
		} ) );

		// Generate an RTL CSS file.
		plugins.push( new cssRTL( {
			filename: `css/${ prefix }[name]-rtl.css`,
		} ) );

	} );

	// Add a custom dependency extractor.
	plugins.push( new depExtract( {
		requestToExternal,
		requestToHandle,
		injectPolyfill: true,
	} ) );

	return plugins;

}

/**
 * Generates a Webpack config object
 *
 * This is opinionated based on our opinions for directory structure.
 *
 * ESNext JS source files are located in `assets/src/js`.
 *
 * SASS/SCSS source files are located in `assets/src/sass`.
 *
 * SASS files should be imported via the JS source file.
 *
 * @since 1.3.0
 * @since 1.2.1 Reduce method size by using helper methods
 *
 * @param {String[]} options.css        Array of CSS file slugs.
 * @param {String[]} options.js         Array of JS file slugs.
 * @param {String}   options.prefix     File prefix.
 * @param {String}   options.outputPath Relative path to the output directory.
 * @return {Object} A webpack.config.js object.
 */
module.exports = ( { css = [], js = [], prefix = 'llms-', outputPath = 'assets/' } ) => {

	return {
		...config,
		entry: setupEntry( js ),
		output: {
			filename: `js/${ prefix }[name].js`,
			path: path.resolve( process.cwd(), outputPath ),
		},
		plugins: setupPlugins( config.plugins, css, prefix ),
	};

}
