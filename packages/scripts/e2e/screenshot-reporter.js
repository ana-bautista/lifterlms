/**
 * Test Reporter that takes screenshots when a test fails.
 *
 * @link https://github.com/smooth-code/jest-puppeteer/issues/131#issuecomment-424073620
 */

import path from 'path';
import mkdirp from 'mkdirp';

/**
 * Take a Screenshot.
 *
 * @param {string} name Screenshot name.
 * @return {Void}
 */
async function takeScreenshot( name ) {

	const
		dir        = './tmp/e2e-screenshots',
		toFilename = s => s.replace( /[^a-z0-9.-]+/gi, '-' ),
		filePath   = toFilename( `${ new Date().toISOString() }-${ name }.png` );

	mkdirp.sync( dir );

	await page.screenshot( {
		path: path.join( dir, filePath ),
		fullPage: true,
	} );

};

/**
 * Jasmine reporter does not support async.
 *
 * Store the screenshot promise and wait for it before each test.
 */
let screenshotPromise = Promise.resolve();
beforeEach( () => screenshotPromise );
afterAll( () => screenshotPromise );

/**
 * Add the test Reporter.
 *
 * @return {Void}
 */
jasmine.getEnv().addReporter( {

	specDone: result => {
		if ( 'false' !== process.env.PUPPETEER_HEADLESS && 'failed' === result.status ) {
			screenshotPromise = screenshotPromise
				.catch()
				.then( () => takeScreenshot( result.fullName ) );
		}
	},

} );
