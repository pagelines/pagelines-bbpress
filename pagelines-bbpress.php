<?php
/*
Plugin Name: bbPress for PageLines
Plugin URI: http://www.pagelines.com/
Description: Adds support for BBPress inside PageLines Framework.
Version: 2.0.2
Author: PageLines
Author URI: http://www.pagelines.com
PageLines: true
Demo: http://demo.pagelines.me/forums/
*/

class PageLinesBBPress {
	
	function __construct() {
		
		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename(dirname( __FILE__ )));
		
		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )));
		
		$this->base_file = sprintf( '%s/%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )), basename( __FILE__ ));
		
		// register plugin hooks...
		$this->plugin_hooks();

		add_filter( 'pagelines_meta_blacklist', array( &$this, 'remove_meta' ), 10, 1 );
		add_filter( 'pagelines_lesscode', array( &$this, 'bb_less' ), 10, 1 );
		add_filter( 'postsmeta_settings_array', array( &$this, 'bb_meta' ), 10, 1 );
		add_filter( 'admin_init', array( &$this, 'plbb_activate' ));
		add_filter( 'pagelines_sections_dirs', array( &$this, 'bb_add_section' ));
		add_action( 'template_redirect', array( &$this, 'bb_integration' ), 999);
	}
	/**
	 *	Plugin hooks
	 */
	function plugin_hooks() {
		
		register_activation_hook( $this->base_file, array( &$this, 'plbb_sections_reset' ) );
		register_deactivation_hook( $this->base_file, array( &$this, 'plbb_sections_reset' ) );
	}
	
	/**
	 *	Include less file
	 */
	function bb_less( $less ) {
		
		
		$less .= pl_file_get_contents( sprintf( '%s/color.less', $this->base_dir ) );
		
		return $less;
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

		$dirs['bbpress'] = $this->base_dir;
		
		return $dirs;
	}
	
	/**
	 *	Add integration to bbpress pages
	 */
	function bb_integration() {
			
			if ( ! function_exists( 'ploption' ) )
				return;

			if ( ! function_exists( 'is_bbpress' ) )
				return;
		
			if ( ! is_bbpress() )
				return;

			if ( bbp_is_forum_archive() )
				new PageLinesIntegration( 'forum_archive' );
			elseif ( bbp_is_single_forum() )
				new PageLinesIntegration( 'forum' );
			elseif ( bbp_is_single_topic() )
				new PageLinesIntegration( 'topic' );
			elseif ( bbp_is_topic_archive() )
				new PageLinesIntegration( 'topics' );			
			else
				new PageLinesIntegration( 'topic' );
	}
	
	/**
	 *	Add tabs to Special Meta
	 */
	function bb_meta( $d ) {

		global $metapanel_options;

		$meta = array(
		
		'forum_archive' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'forum_archive', 'forum_archive' ),
			'icon'		=> $this->base_url.'/icon.png'
		),

		'forum' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'forum', 'forum' ),
			'icon'		=> $this->base_url.'/icon.png'
		),

		'topic_archive' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'topics', 'topics' ),
			'icon'		=> $this->base_url.'/icon.png'
		),

		'topic' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'topic', 'topic' ),
			'icon'		=> $this->base_url.'/icon.png'
		),
		);

		$d = array_merge($d, $meta);

		return $d;
	}

	/**
	 *	Reset sections.
	 */
	function plbb_sections_reset() {
		
		if ( ! function_exists( 'ploption' ) )
			return;
		global $load_sections;
		delete_transient( 'pagelines_sections_cache' );
		$load_sections->pagelines_register_sections( true, false );
	}

	function plbb_activate() {
		
		if ( ! function_exists( 'ploption' ) )
			return;

		$bb_templates = array(
			'forum_archive',
			'forum',
			'topic_archive',
			'topic',
			'reply_archive',
			'reply'
		);
		$map = get_option( PAGELINES_TEMPLATE_MAP );

		foreach ( $bb_templates as $template ) {

			$default = array( 
				'name' => $template,
				'sections' => array( 
					'PageLinesBBLoop')
					);

			if ( ! isset( $map['main']['templates'][$template] ) )
				$map['main']['templates'][$template] = $default;

			if ( ! in_array( 'PageLinesBBLoop', $map['main']['templates'][$template]['sections'] ) )
				$map['main']['templates'][$template]['sections'][] = 'PageLinesBBLoop';

			foreach ( $map['main']['templates'][$template]['sections'] as $n => $t )
				if( 'PageLinesPostLoop' == $t )
					unset( $map['main']['templates'][$template]['sections'][$n]);
					
			$map['main']['templates'][$template] = array_unique( $map['main']['templates'][$template] );
		}

		update_option( PAGELINES_TEMPLATE_MAP, $map );
	}
} // /class


/**
 *	Initiate class
 */
new PageLinesBBPress;