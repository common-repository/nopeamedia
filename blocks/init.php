<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function nopme_columns_block_assets() { // phpcs:ignore
	// Styles.
	wp_enqueue_style(
		'pdf_columns-cgb-style-css', // Handle.
		plugins_url( 'blocks/dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ) // Dependency to include the CSS after it.
		// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);
}

// Hook: Frontend assets.
add_action( 'enqueue_block_assets', 'nopme_columns_block_assets' );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function nopme_columns_editor_assets() { // phpcs:ignore
	// Scripts.
	wp_enqueue_script(
		'pdf-blocks-js', // Handle.
		plugins_url( 'blocks/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n' ), // Dependencies, defined above.
		// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: File modification time.
		true // Enqueue the script in the footer.
	);

	// Styles.
	wp_enqueue_style(
		'pdf-blocks-css', // Handle.
		plugins_url( 'blocks/dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
		// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	//Load translations
    wp_set_script_translations( 'pdf-blocks-js', 'nopea-media', NOPME_PLUGIN_PATH. '/languages');
}

// Hook: Editor assets.
add_action( 'enqueue_block_editor_assets', 'nopme_columns_editor_assets' );


/**
*
*
*/
function nopme_pdf_block_category($categories, $post) {
	return array_merge(
		$categories,
		array(
			array(
					'slug' => 'pdf-blocks',
					'title' => __('PDF Blocks', 'nopea-media')
			),
		)
	);
}

add_filter('block_categories', 'nopme_pdf_block_category', 10,2 );


