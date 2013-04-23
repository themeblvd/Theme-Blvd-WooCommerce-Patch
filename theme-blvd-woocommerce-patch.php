<?php
/*
Plugin Name: Theme Blvd WooCommerce Patch
Description: This plugins adds basic compatibility with Theme Blvd themes and WooCommerce.
Version: 1.1.0
Author: Jason Bobich
Author URI: http://jasonbobich.com
License: GPL2
*/

/*
Copyright 2012 JASON BOBICH

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'TB_WOOCOMMERCE_PLUGIN_VERSION', '1.1.0' );
define( 'TB_WOOCOMMERCE_PLUGIN_DIR', dirname( __FILE__ ) ); 
define( 'TB_WOOCOMMERCE_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Hooks for after theme has been setup.
 *
 * @since 1.0.0
 */

function tb_woocommerce_hooks(){
	
	// If no WooCommerce or Theme Blvd framework, get out of here.
	if( ! defined( 'WOOCOMMERCE_VERSION' ) || ! defined( 'TB_FRAMEWORK_VERSION' ) )
		return;
	
	// Remove default WooCommerce wrappers
	remove_all_actions( 'woocommerce_before_main_content' );
	remove_all_actions( 'woocommerce_after_main_content' );
	remove_all_actions( 'woocommerce_sidebar' );

	// Hook in wrappers based on Theme Blvd framework version.
	if( version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		// Theme Blvd framework v2.0-2.1
		add_action( 'woocommerce_before_main_content', 'tb_woocommerce_hooks_before' );
		add_action( 'woocommerce_after_main_content', 'tb_woocommerce_hooks_after' );
	} else {
		// Theme Blvd framework v2.2+
		add_action( 'woocommerce_before_main_content', 'tb_woocommerce_before_main_content' );
		add_action( 'woocommerce_after_main_content', 'tb_woocommerce_after_main_content' );
	}

}
add_action( 'after_setup_theme', 'tb_woocommerce_hooks' );

/**
 * Before main content
 *
 * @since 1.0.0
 */

function tb_woocommerce_hooks_before(){
	themeblvd_main_start();
	themeblvd_main_top();
	themeblvd_breadcrumbs();
	themeblvd_before_layout();
	echo '<div id="sidebar_layout">';
	echo '<div class="sidebar_layout-inner">';
	echo '<div class="grid-protection">';
	themeblvd_sidebars( 'left' );
	echo '<div id="content" role="main">';
	echo '<div class="inner">';
	echo '<div class="article-wrap">';
	echo '<article>';
}

/**
 * After main content
 *
 * @since 1.0.0
 */

function tb_woocommerce_hooks_after(){
	echo '</article>';
	echo '</div><!-- .article-wrap (end) -->';
	echo '</div><!-- .inner (end) -->';
	echo '</div><!-- #content (end) -->';
	themeblvd_sidebars( 'right' );
	echo '</div><!-- .grid-protection (end) -->';
	echo '</div><!-- .sidebar_layout-inner (end) -->';
	echo '</div><!-- .sidebar-layout-wrapper (end) -->';
	themeblvd_main_bottom();
	themeblvd_main_end();
}

/**
 * Before main content, used in Theme Blvd framework v2.2+
 *
 * @since 1.1.0
 */

function tb_woocommerce_before_main_content(){
	?>
	<div id="sidebar_layout" class="clearfix">
		<div class="sidebar_layout-inner">
			<div class="row-fluid grid-protection">

				<?php get_sidebar( 'left' ); ?>
				
				<!-- CONTENT (start) -->
	
				<div id="content" class="<?php echo themeblvd_get_column_class('content'); ?> clearfix" role="main">
					<div class="inner">
						<?php themeblvd_content_top(); ?>
						<div class="article-wrap">
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
}

/**
 * After main content, used in Theme Blvd framework v2.2+
 *
 * @since 1.1.0
 */

function tb_woocommerce_after_main_content(){
	?>
							</article>
						</div><!-- .article-wrap (end) -->
					</div><!-- .inner (end) -->
				</div><!-- #content (end) -->
					
				<!-- CONTENT (end) -->	
				
				<?php get_sidebar( 'right' ); ?>
			
			</div><!-- .grid-protection (end) -->
		</div><!-- .sidebar_layout-inner (end) -->
	</div><!-- .#sidebar_layout (end) -->
	<?php
}

/**
 * Get current sidebar layout for a WooCommerce page.
 *
 * @since 1.1.0
 */

function tb_woocommerce_get_sidebar_layout(){
	
	$sidebar_layout = '';
	
	$woo_default = themeblvd_get_option('tb_woocommerce_layout_default');
	if( ! $woo_default )
		$woo_default = 'sidebar_right';

	if( is_woocommerce() ) {
		if( is_product() )
			$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_single');
		else if( is_shop() )
			$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_shop');
		else if( is_product_category() || is_product_tag() )
			$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_archive');
		else if( is_checkout () || is_order_received_page() )
			$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_checkout');
		else if( is_cart() )
			$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_cart');
		else if( is_account_page() )
			$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_account');
	}
	
	if( ! $sidebar_layout || $sidebar_layout == 'default' )
		$sidebar_layout = $woo_default;

	return $sidebar_layout;
}

/**
 * Filter sidebar layout of framework for our WooCommerce 
 * sidebar layout.
 *
 * @since 1.0.0
 */

function tb_woocommerce_sidebar_layout( $sidebar_layout ){
	
	global $post;

	// Only run if WooCommerce plugin is installed
	if( function_exists( 'is_woocommerce' ) ) {

		// Figure out if this a static page we need force as a WooCommerce page.
		$force_woocommerce = false;
		if( is_page() && get_post_meta( $post->ID, '_tb_woocommerce_page', true ) === 'true' )
			$force_woocommerce = true;

		// Adjust sidebar layout if necessary.
		if( is_woocommerce() || $force_woocommerce )
			$sidebar_layout = tb_woocommerce_get_sidebar_layout();

	}

	return $sidebar_layout;
}
add_filter( 'themeblvd_sidebar_layout', 'tb_woocommerce_sidebar_layout' );

/**
 * Force WooCommerce sidebar_right layout.
 *
 * @since 1.0.0
 */

function tb_woocommerce_sidebar_id( $config ){
	
	global $post;
	
	// Only run if WooCommerce plugin is installed
	if( function_exists( 'is_woocommerce' ) ) {
	
		// Figure out if this a static page we need force as a WooCommerce page.
		$force_woocommerce = false;
		if( is_page() && get_post_meta( $post->ID, '_tb_woocommerce_page', true ) === 'true' )
			$force_woocommerce = true;
		
		// Re-configure sidebar to be shown for right sidebar location if 
		// this is a WooCommerce-forced page. 
		if( is_woocommerce() || $force_woocommerce ) {
			
			// Determine if sidebar has widgets
			$error = false;
			if( ! is_active_sidebar( 'tb_woocommerce' ) )
				$error = true;
			
			// Adjust config
			$sidebar_layout = tb_woocommerce_get_sidebar_layout();
			$config['sidebars'][$sidebar_layout] = array(
				'id' => 'tb_woocommerce',
				'error' => $error
			);
		}	
	}
	
	return $config;
}
add_filter( 'themeblvd_frontend_config', 'tb_woocommerce_sidebar_id' );

/**
 * Register WooCommerce Sidebar
 *
 * @since 1.0.0
 */

function tb_woocommerce_register_sidebar(){
	$args = array(
		'name' 			=> __('WooCommerce Sidebar', 'tb_woocommerce'),
		'description'	=> __('This sidebar will show on all WooCommerce pages and will always be on the right.', 'tb_woocommerce'),
	    'id' 			=> 'tb_woocommerce',
	    'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget' 	=> '</div></aside>',
		'before_title' 	=> '<h3 class="widget-title">',
		'after_title' 	=> '</h3>'
	);
	register_sidebar( $args );
}
add_action( 'plugins_loaded', 'tb_woocommerce_register_sidebar' );


/**
 * Add option to select if this is a WooCommerce page when 
 * setting up static pages and inserting WooCommerce shortcodes.
 *
 * @since 1.0.0
 */

function tb_woocommerce_page_options( $setup ){
	$setup['options'][] = array(
		'id'		=> '_tb_woocommerce_page',
		'name' 		=> __( 'Force WooCommerce Page', 'tb_woocommerce' ),
		'desc'		=> __( 'If you run into a situation where you need to force the WooCommerce sidebar and sidebar layout to this page, you can do so here.', 'tb_woocommerce' ),
		'type' 		=> 'radio',
		'std'		=> 'false',
		'options'	=> array(
			'false' => __( 'No, this is not a WooCommerce page.', 'tb_woocommerce' ),
			'true' => __( 'Yes, force the WooCommerce sidebar setup.', 'tb_woocommerce' )
		)
	);
	return $setup;
}
add_filter( 'themeblvd_page_meta', 'tb_woocommerce_page_options' );

/**
 * Add options to theme options page for selecting sidebar 
 * layouts for various woocommerce pages.
 *
 * @since 1.1.0
 */

function tb_woocommerce_options(){

	if( ! defined('TB_FRAMEWORK_VERSION') || version_compare(TB_FRAMEWORK_VERSION, '2.1.0', '<') )
		return;

	// Add new main-level tab "WooCommerce"
	themeblvd_add_option_tab( 'woocommerce', 'WooCommerce' );

	/*--------------------------------------------*/
	/* Sidebar Layouts
	/*--------------------------------------------*/

	// Generate sidebar layout options
	$sidebar_layouts = array();
	if( is_admin() ) {
		$layouts = themeblvd_sidebar_layouts();
		if( isset( $layouts['full_width'] ) )
			$sidebar_layouts['full_width'] = $layouts['full_width']['name'].' '.__('(no sidebar)', 'tb_woocommerce');
		if( isset( $layouts['sidebar_right'] ) )
			$sidebar_layouts['sidebar_right'] = $layouts['sidebar_right']['name'];
		if( isset( $layouts['sidebar_left'] ) )
			$sidebar_layouts['sidebar_left'] = $layouts['sidebar_left']['name'];
	}

	$default = array(
	   'tb_woocommerce_layout_default' => array( 
			'name' 		=> __( 'WooCommerce Default', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a default fallback sidebar layout for WooCommerce pages.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_default',
			'std' 		=> 'sidebar_right',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		)
	);

	$sidebar_layouts = array_merge(array('default' => __('WooCommerce Default', 'tb_woocommerce')), $sidebar_layouts );

	$options = array(
		'tb_woocommerce_layout_shop' => array( 
			'name' 		=> __( 'The main shop page', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a sidebar layout for the main shop page.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_shop',
			'std' 		=> 'default',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		),
		'tb_woocommerce_layout_archive' => array( 
			'name' 		=> __( 'Product archives', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a sidebar layout for product archive pages. For example, this would include viewing a category or tag of products.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_archive',
			'std' 		=> 'default',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		),
		'tb_woocommerce_layout_single' => array( 
			'name' 		=> __( 'Single product pages', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a sidebar layout for single product pages.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_single',
			'std' 		=> 'default',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		),
		'tb_woocommerce_layout_cart' => array( 
			'name' 		=> __( 'Cart page', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a sidebar layout for the shopping cart page.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_cart',
			'std' 		=> 'default',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		),
		'tb_woocommerce_layout_checkout' => array( 
			'name' 		=> __( 'Checkout pages', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a sidebar layout for Checkout Page, Pay Page, and Thanks page.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_checkout',
			'std' 		=> 'default',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		),
		'tb_woocommerce_layout_account' => array( 
			'name' 		=> __( 'Customer account pages', 'tb_woocommerce' ),
			'desc' 		=> __( 'Select a sidebar layout for the customer account pages.', 'tb_woocommerce' ),
			'id' 		=> 'tb_woocommerce_layout_account',
			'std' 		=> 'default',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		)
	);

	$options = apply_filters('tb_woocommerce_sidebar_layout_options', array_merge($default, $options) );
	
	$desc = __('Under Appearance > Widgets, you have a specific sidebar for WooCommerce pages called "WooCommerce Sidebar". In this section, you can select sidebar layouts for specific WooCommerce pages that will determine if that sidebar shows on the right, left, or at all.', 'tb_woocommerce');
	themeblvd_add_option_section( 'woocommerce', 'sidebar_layouts', 'Sidebar Layouts', $desc, $options );

}
add_action( 'after_setup_theme', 'tb_woocommerce_options' );