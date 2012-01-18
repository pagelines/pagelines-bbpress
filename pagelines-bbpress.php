<?php
/*
Plugin Name: PageLines BBPress
Plugin URI: http://www.pagelines.com/
Description: Adds support for BBPress
Version: 0.1
Author: PageLines
Author URI: http://www.pagelines.com
PageLines: true
*/

class PageLinesBBPress {
	
	function __construct() {
		
		if ( ! function_exists( 'is_bbpress' ) )
			return;
		add_filter( 'pagelines_meta_blacklist', array( &$this, 'remove_meta' ), 10, 1 );
		add_action( 'wp_print_styles', array( &$this, 'head_css' ) );
		add_filter( 'postsmeta_settings_array', array( &$this, 'bb_meta' ), 10, 1 );
		add_filter( 'pagelines_sections_dirs', array( &$this, 'bb_add_section' ));
		add_action( 'template_redirect', array( &$this, 'bb_integration' ) );	
	}
	
	/**
	 *	Remove BB css and add our own.
	 */
	function head_css() {
			
		if ( !is_bbpress() )
			return;
		wp_deregister_style( 'bbpress-style' );
		$style = plugins_url('style.css', __FILE__);
		wp_register_style('plbb-styles', $style);
		wp_enqueue_style( 'plbb-styles');		
	}
	
	/**
	 *	Remove PageLines settings from bbpress settings areas.
	 */
	function remove_meta( $list ) {
	
		$list[] = 'forum';
		$list[] = 'topic';
		$list[] = 'reply';
		
		return $list;
	}
	
	/**
	 *	Add bbpress custom template into section system.
	 */
	function bb_add_section( $dirs ) {

		$dirs['custom'] = sprintf( '%s', plugin_dir_path( __FILE__ ) );
		
		return $dirs;
	}
	
	/**
	 *	Add integration to bbpress pages
	 */
	function bb_integration() {
			
			if ( ! is_bbpress() )
				return;
		
			if ( bbp_is_forum_archive() )
				new PageLinesIntegration( 'forum_archive' );
			else

			if ( bbp_is_single_forum() )
				new PageLinesIntegration( 'forum' );
			else	

			if ( bbp_is_single_topic() )
				new PageLinesIntegration( 'topic' );
			else
				if ( bbp_is_topic_archive() )
					new PageLinesIntegration( 'topics' );
				
			else
				new PageLinesIntegration( 'forum_archive' );
	}
	
	/**
	 *	Add tabs to Special Meta
	 */
	function bb_meta( $d ) {

		global $metapanel_options;

		$meta = array(
		
		'forum_archive' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'forum_archive', 'forum_archive' ),
			'icon'		=> PL_ADMIN_ICONS.'/equalizer.png'
		),

		'forum' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'forum', 'forum' ),
			'icon'		=> PL_ADMIN_ICONS.'/equalizer.png'
		),

		'topic_archive' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'topics', 'topics' ),
			'icon'		=> PL_ADMIN_ICONS.'/equalizer.png'
		),

		'topic' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'topic', 'topic' ),
			'icon'		=> PL_ADMIN_ICONS.'/equalizer.png'
		),
		);

		$d = array_merge($d, $meta);

		return $d;
	}

} // /class

/**
 *	Reset sections.
 */
function plbb_sections_reset() {

	global $load_sections;
	delete_transient( 'pagelines_sections_cache' );
	$load_sections->pagelines_register_sections( true, false );
}

/**
 *	Activate/deactivate hooks.
 */
register_activation_hook( __FILE__ , 'plbb_sections_reset' );
register_deactivation_hook( __FILE__ , 'plbb_sections_reset' );

/**
 *	Initiate class
 */
new PageLinesBBPress;