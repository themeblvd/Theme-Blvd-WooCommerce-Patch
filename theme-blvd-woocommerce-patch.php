<?php
/*
Plugin Name: Theme Blvd WooCommerce Patch
Description: This plugins adds basic compatibility with Theme Blvd themes and WooCommerce.
Version: 1.1.1
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

define( 'TB_WOOCOMMERCE_PLUGIN_VERSION', '1.1.1' );
define( 'TB_WOOCOMMERCE_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_WOOCOMMERCE_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Hooks for after theme has been setup.
 *
 * @since 1.1.0
 */

function tb_woocommerce_init(){

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

	// Pagination
	if( apply_filters( 'tb_woocommerce_pagination', true ) ) {
		remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination' );
		add_action( 'woocommerce_after_shop_loop', 'themeblvd_pagination' );
	}

	// Breadcrumbs
	if( apply_filters( 'tb_woocommerce_breadcrumbs', true ) ) {
		add_filter( 'themeblvd_pre_breadcrumb_parts', 'tb_woocommerce_breadcrumb_parts' );
		add_action( 'wp', 'tb_woocommerce_hide_frontpage_breadcrumbs' );
	}

	// Page Options
	add_filter( 'themeblvd_page_meta', 'tb_woocommerce_page_options' );

	// Appearance > Theme Options > WooCommerce
	tb_woocommerce_options();

	// Sidebars
	tb_woocommerce_register_sidebar();
	add_filter( 'themeblvd_sidebar_layout', 'tb_woocommerce_sidebar_layout' );
	add_filter( 'themeblvd_frontend_config', 'tb_woocommerce_sidebar_id' );

	// Assets
	add_action( 'wp_enqueue_scripts', 'tb_woocommerce_styles' );

}
add_action( 'after_setup_theme', 'tb_woocommerce_init' );

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
 * This function determines if the current page is ANY
 * WooCommerce page, including our plugin's "forced"
 * WooCommerce page option.
 *
 * Note: Using is_woocommerce() does not take into account
 * all of the assigned pages.
 *
 * @since 1.1.0
 */

function is_tb_woocommerce(){

	global $post;

	// Is WooCommerce plugin even activated?
	if( ! function_exists( 'is_woocommerce' ) )
		return false;

	// Shop page or product archives?
	if( is_woocommerce() )
		return true;

	// One of the WooCommerce assigned pages?
	if( is_checkout() || is_order_received_page() || is_cart() || is_account_page() )
		return true;

	// Or, is this one of our forced WooCommerce pages?
	if( is_page() && get_post_meta( $post->ID, '_tb_woocommerce_page', true ) === 'true' )
		return true;

	return false;
}

/**
 * Get current sidebar layout for a WooCommerce page.
 *
 * @since 1.1.0
 */

function tb_woocommerce_get_sidebar_layout(){

	global $post;
	$sidebar_layout = '';

	$woo_default = themeblvd_get_option('tb_woocommerce_layout_default');
	if( ! $woo_default )
		$woo_default = 'sidebar_right';

	if( is_product() ) {
		$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_single');
	} else if( is_shop() ) {
		$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_shop');
	} else if( is_product_category() || is_product_tag() ) {
		$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_archive');
	} else if( is_page() ) {

		// Check the sidebar layout assigned to the static page
		$page_layout = get_post_meta( $post->ID, '_tb_sidebar_layout', true );

		// And only apply our WooCommerce sidebar layout if the
		// "default" setting is in place.
		if( $page_layout == 'default' ) {
			if( is_checkout () || is_order_received_page() )
				$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_checkout');
			else if( is_cart() )
				$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_cart');
			else if( is_account_page() )
				$sidebar_layout = themeblvd_get_option('tb_woocommerce_layout_account');
		} else {
			$sidebar_layout = $page_layout;
		}
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

	if( is_tb_woocommerce() )
		$sidebar_layout = tb_woocommerce_get_sidebar_layout();

	return $sidebar_layout;
}

/**
 * Force WooCommerce sidebar_right layout.
 *
 * @since 1.0.0
 */

function tb_woocommerce_sidebar_id( $config ){

	global $post;

	// Re-configure sidebar to be if this is a WooCommerce page.
	if( is_tb_woocommerce() ) {

		// If the user selected a specific sidebar layout
		// when editing the current page, abort mission.
		if( is_page() && get_post_meta( $post->ID, '_tb_sidebar_layout', true ) != 'default' )
			return $config;

		// This is the ID of the sidebar this plugin registers.
		$woo_sidebar_id = apply_filters('tb_woocommerce_sidebar_id', 'tb_woocommerce');

		// Determine if sidebar has widgets
		$error = false;
		if( ! is_active_sidebar( $woo_sidebar_id ) )
			$error = true;

		// Adjust config
		$sidebar_layout = tb_woocommerce_get_sidebar_layout();
		$config['sidebars'][$sidebar_layout] = array(
			'id' 	=> $woo_sidebar_id,
			'error' => $error
		);
	}

	return $config;
}

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
	register_sidebar( apply_filters( 'tb_woocommerce_sidebar_args', $args ) );
}


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
		'desc'		=> __( 'If you run into a situation where you need to force the WooCommerce sidebar and sidebar layout to this page, you can do so here.<br /><br />Pages that you\'ve assigned at <em>WooCommerce > Settings > Pages</em> don\'t need to be forced.', 'tb_woocommerce' ),
		'type' 		=> 'radio',
		'std'		=> 'false',
		'options'	=> array(
			'false' => __( 'No, don\'t force as a WooCommerce page.', 'tb_woocommerce' ),
			'true' => __( 'Yes, force the WooCommerce sidebar setup.', 'tb_woocommerce' )
		)
	);
	return $setup;
}

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

	$desc = __('Under Appearance > Widgets, you have a specific sidebar for WooCommerce pages called "WooCommerce Sidebar." In this section, you can select sidebar layouts for specific WooCommerce pages that will determine if that sidebar shows on the right, left, or at all.<br /><br />Note: In order for the settings below to be applied for cart, checkout, and customer account pages, you must have their sidebar layouts set to "Default Sidebar Layout" when editing those pages.', 'tb_woocommerce');
	themeblvd_add_option_section( 'woocommerce', 'sidebar_layouts', 'Sidebar Layouts', $desc, $options );

	/*--------------------------------------------*/
	/* Breadcrumbs
	/*--------------------------------------------*/

	if( version_compare(TB_FRAMEWORK_VERSION, '2.2.0', '>=' ) ) {

		$options = array(
			'tb_woocommerce_breadcrumb_shop' => array(
				'name' 		=> __( 'Shop link display', 'tb_woocommerce' ),
				'desc' 		=> __( 'Select the scenarios where you\'d like a link to the main shop page inserted into the Breadcrumb trail.<br /><br />This option won\'t have any effect if you\'ve set your shop page as a static frontpage under <em>Settings > Reading > Frontpage displays</em>.<br /><br />Note: The main shop page is set from <em>WooCommerce > Settings > Pages</em>.', 'tb_woocommerce' ),
				'id' 		=> 'tb_woocommerce_breadcrumb_shop',
				'type' 		=> 'multicheck',
				'std' 		=> array(
					'archives'	=> true,
					'single'	=> true,
					'cart'		=> true,
					'checkout'	=> true,
					'account'	=> true,
					'forced'	=> true
				),
				'options' 	=> array(
					'archives' 	=> __('Product archives', 'tb_woocommerce'),
					'single' 	=> __('Single product pages', 'tb_woocommerce'),
					'cart' 		=> __('Cart page', 'tb_woocommerce'),
					'checkout' 	=> __('Checkout pages', 'tb_woocommerce'),
					'account' 	=> __('Customer account pages', 'tb_woocommerce'),
					'forced' 	=> __('Forced WooCommerce pages', 'tb_woocommerce')
				)
			)
		);
		themeblvd_add_option_section( 'woocommerce', 'breadcrumbs', 'Breadcrumbs', '', apply_filters('tb_woocommerce_sidebar_layout_options', $options ) );
	}
}

/**
 * CSS for WooCommerce and Theme Blvd framework to
 * sit decently together.
 *
 * The idea here is not to add pretty styles, but
 * just sort of tame any Bootstrap-related styles
 * before getting to the woo-commerce stylesheet.
 *
 * @since 1.1.0
 */

function tb_woocommerce_styles() {
	wp_enqueue_style( 'tb_woocommerce', TB_WOOCOMMERCE_PLUGIN_URI.'/tb-woocommerce.css', array('themeblvd'), TB_WOOCOMMERCE_PLUGIN_VERSION );
}

/**
 * Hide breadcrumbs on main shop page when it's set
 * as a static frontpage.
 *
 * This is needed because WooCommerce takes over the main
 * query. So, our standard option on that page for hiding
 * breadcrumbs won't work, as it's no longer "the page".
 *
 * @since 1.1.0
 */

function tb_woocommerce_hide_frontpage_breadcrumbs() {
	if( is_post_type_archive('product') && get_option('page_on_front') === woocommerce_get_page_id('shop') )
		remove_all_actions( 'themeblvd_breadcrumbs' );
}

/**
 * Add breacrumb parts to Theme Blvd breadcrumbs
 * based on WooCommerce pages.
 *
 * @since 1.1.0
 */

function tb_woocommerce_breadcrumb_parts( $parts ) {

	global $post;
	global $wp_query;

	// Get out of here, everyone else.
	if( ! is_tb_woocommerce() )
		return $parts;

	// Set shop name
	$shop_name = woocommerce_get_page_id( 'shop' ) ? get_the_title( woocommerce_get_page_id( 'shop' ) ) : '';
	$shop_link = array(
		'link' 	=> get_post_type_archive_link('product'),
		'text' 	=> $shop_name,
		'type'	=> 'shop'
	);

	// Show shop link in trail?
	if( woocommerce_get_page_id('shop') && get_option('page_on_front') === woocommerce_get_page_id('shop') ) {
		$show_shop_link = array(
			'archives'	=> false,
			'single'	=> false,
			'cart'		=> false,
			'checkout'	=> false,
			'account'	=> false,
			'forced'	=> false
		);
	} else {
		$show_shop_link = themeblvd_get_option('tb_woocommerce_breadcrumb_shop');
	}

	// Shop page, product archives, and single products
	if( is_woocommerce() ) {

		$parts = array(); // Reset $parts

		if( is_product_category() ) {

			if( $show_shop_link['archives'] )
				$parts[] = $shop_link;

			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

			$parents = array();
			$parent = $term->parent;
			while( $parent ) {
				$parents[] = $parent;
				$new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) );
				$parent = $new_parent->parent;
			}

			if( ! empty( $parents ) ) {
				$parents = array_reverse( $parents );
				foreach ( $parents as $parent ) {
					$item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
					$parts[] = array(
						'link' 	=> get_term_link( $item->slug, 'product_cat' ),
						'text' 	=> esc_html( $item->name ),
						'type'	=> 'product-cat'
					);
				}
			}

			$queried_object = $wp_query->get_queried_object();
			$parts[] = array(
				'link' 	=> '',
				'text' 	=> esc_html( $queried_object->name ),
				'type'	=> 'product-cat'
			);

		} else if( is_product_tag() ) {

			if( $show_shop_link['archives'] )
				$parts[] = $shop_link;

			$queried_object = $wp_query->get_queried_object();
			$parts[] = array(
				'link' 	=> '',
				'text' 	=> sprintf(__( 'Products tagged &ldquo;%s&rdquo;', 'tb_woocommerce' ), $queried_object->name),
				'type'	=> 'product-tag'
			);

		} else if( is_post_type_archive('product') ) {

			if( is_search() ) {
				$parts[] = $shop_link;
				$parts[] = array(
					'link' 	=> '',
					'text' 	=> themeblvd_get_local('crumb_search').' "'.get_search_query().'"',
					'type'	=> 'search'
				);
			} else {
				$parts[] = array(
					'link' 	=> '',
					'text' 	=> $shop_name,
					'type'	=> 'shop'
				);
			}

		} else if( is_product() ) {

			if( $show_shop_link['single'] )
				$parts[] = $shop_link;

			if( $terms = wp_get_object_terms( $post->ID, 'product_cat' ) ) {

				$term = current( $terms );
				$parents = array();
				$parent = $term->parent;

				while( $parent ) {
					$parents[] = $parent;
					$new_parent = get_term_by( 'id', $parent, 'product_cat' );
					$parent = $new_parent->parent;
				}

				if( ! empty( $parents ) ) {
					$parents = array_reverse($parents);
					foreach( $parents as $parent ) {
						$item = get_term_by( 'id', $parent, 'product_cat');
						$parts[] = array(
							'link' 	=> get_term_link( $item->slug, 'product_cat' ),
							'text' 	=> $item->name,
							'type'	=> 'product-cat'
						);
					}
				}

				$parts[] = array(
					'link' 	=> get_term_link( $term->slug, 'product_cat' ),
					'text' 	=> $term->name,
					'type'	=> 'product-cat'
				);
			}

			$parts[] = array(
				'link' 	=> '',
				'text' 	=> get_the_title(),
				'type'	=> 'product'
			);
		}
	}

	// Cart, checkout, account, and forced WooCommerce pages
	if( is_page() ) {

		// Shop link added before page hierarchy
		$new_parts = array();
		if( is_checkout() || is_order_received_page() ) {
			if( $show_shop_link['checkout'] ) {
				$new_parts[] = $shop_link;
			}
		} else if( is_cart() ) {
			if( $show_shop_link['cart'] ) {
				$new_parts[] = $shop_link;
			}
		} else if( is_account_page() ) {
			if( $show_shop_link['account'] ) {
				$new_parts[] = $shop_link;
			}
		} else {
			if( $show_shop_link['forced'] ) {
				$new_parts[] = $shop_link;
			}
		}

		// Merge shop link to original parts. No need to re-do
		// the work for figuring Pages breadcrumb trail again
		// when the framework already did it.
		$parts = array_merge( $new_parts, $parts );
	}

	return $parts;
}