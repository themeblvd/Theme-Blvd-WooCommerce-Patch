<?php
/*
Plugin Name: Theme Blvd WooCommerce Patch
Description: This plugins adds basic compatibility with Theme Blvd themes and WooCommerce.
Version: 1.0.0
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

/**
 * Hooks for after theme has been setup.
 *
 * @since 1.0.0
 */

function tb_woocommerce_hooks(){
	// Only move forward if this is a Theme Blvd theme.
	if( defined( 'TB_FRAMEWORK_VERSION' ) ) {
		remove_all_actions( 'woocommerce_before_main_content' );
		remove_all_actions( 'woocommerce_after_main_content' );
		remove_all_actions( 'woocommerce_sidebar' );
		add_action( 'woocommerce_before_main_content', 'tb_woocommerce_hooks_before' );
		add_action( 'woocommerce_after_main_content', 'tb_woocommerce_hooks_after' );
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
 * Force WooCommerce sidebar_right layout.
 *
 * @since 1.0.0
 */

function tb_woocommerce_sidebar_layout( $sidebar_layout ){
	
	global $post;
	
	// Only run if WooCommerce plugin is installed
	if( function_exists( 'is_woocommerce' ) ) {
		
		// Figure out if this a static page we need force as a WooCommerce page.
		$force_woocommerce = false;
		if( is_page() ) { 
			$woocommerce_page = get_post_meta( $post->ID, '_tb_woocommerce_page', true );
			if( $woocommerce_page === 'true' )
				$force_woocommerce = true;
		}
		
		// Adjust sidebar layout if necessary.
		if( is_woocommerce() || $force_woocommerce )
			$sidebar_layout = 'sidebar_right';
			
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
		if( is_page() ) { 
			$woocommerce_page = get_post_meta( $post->ID, '_tb_woocommerce_page', true );
			if( $woocommerce_page === 'true' ) 
				$force_woocommerce = true;
		}
		
		// Re-configure sidebar to be shown for right sidebar location if 
		// this is a WooCommerce-forced page. 
		if( is_woocommerce() || $force_woocommerce ) {
			
			// Determine if sidebar has widgets
			$error = false;
			if( ! is_active_sidebar( 'tb_woocommerce' ) )
				$error = true;
			
			// Adjust config	
			$config['sidebars']['sidebar_right'] = array(
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
		'name' 		=> __( 'WooCommerce Page', 'tb_woocommerce' ),
		'desc'		=> __( 'Select if this is a WooCommerce page or not. If so, the WooCommerce Sidebar will be applied along with a forced "Sidebar Right" layout. Also, make sure you haven\'t selected a page template if you\'re using this page as a WooCommerce page.', TB_GETTEXT_DOMAIN ),
		'type' 		=> 'radio',
		'std'		=> 'false',
		'options'	=> array(
			'false' => __( 'No, this is not a WooCommerce page.', 'tb_woocommerce' ),
			'true' => __( 'Yes, this is a WooCommerce page.', 'tb_woocommerce' )
		)
	);
	return $setup;
}
add_filter( 'themeblvd_page_meta', 'tb_woocommerce_page_options' );