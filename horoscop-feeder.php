<?php
/*
	Plugin Name: Horoscope Feeder
	Plugin URI: http://www.horoscop.ro/plugins/horoscop-feeder/
	Description: Horoscop Feeder is feed reader for www.horoscop.ro website, with customisable  widget and embed code. Should be available to install for every user witch want to see horoscop online in our websites.
	Version: 1.0.1
	Author: Tamas Romeo
	Author Email: romeo.tamas@gmail.com
	License:

	  Copyright 2011 Tamas Romeo (romeo.tamas@gmail.com)

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

require_once(dirname(__FILE__)."/horoscop-fields.php");
require_once(dirname(__FILE__)."/horoscop-widget.php");

class horoscop_feeder extends horoscop_fields {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'Horoscope Feeder';
	const slug = 'horoscop_feeder';
	const vers = '1.0';
	const feed = 'http://www.horoscop.ro/feed/';
	public $logos = array();
	
	/**
	 * Constructor
	 */
	function __construct() {
		/* Define horoscope logos */
		$this->logos["scorpion"] = plugins_url( 'logos/scorpion.png' , __FILE__ );
		$this->logos["rac"] = plugins_url( 'logos/rac.png' , __FILE__ );
		$this->logos["vărsător"] = plugins_url( 'logos/varsator.png' , __FILE__ );
		$this->logos["capricorn"] = plugins_url( 'logos/capricorn.png' , __FILE__ );
		$this->logos["gemeni"] = plugins_url( 'logos/gemeni.png' , __FILE__ );
		$this->logos["fecioară"] = plugins_url( 'logos/fecioara.png' , __FILE__ );
		$this->logos["taur"] = plugins_url( 'logos/taur.png' , __FILE__ );
		$this->logos["balanță"] = plugins_url( 'logos/balanta.png' , __FILE__ );
		$this->logos["săgetător"] = plugins_url( 'logos/sagetator.png' , __FILE__ );
		$this->logos["leu"] = plugins_url( 'logos/leu.png' , __FILE__ );
		$this->logos["berbec"] = plugins_url( 'logos/berbec.png' , __FILE__ );
		$this->logos["pești"] = plugins_url( 'logos/pesti.png' , __FILE__ );
		
		register_activation_hook( __FILE__, array( &$this, 'install_horoscop_feeder' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'uninstall_horoscop_feeder' ) );
		add_action( 'init', array( &$this, 'init_horoscop_feeder' ) );
	}
	
	/**
	 * Runs when the plugin is initialized
	 */
	function init_horoscop_feeder() {
		global $h_settings;
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();
		// Register the shortcode [horoscope-feeder]
		add_shortcode( 'horoscope-feeder', array( &$this, 'render_shortcode' ) );
		if ( is_admin() ) {
			add_action('admin_menu', array(&$this, 'addMenu'));
			add_action('admin_init', array(&$this, 'register_horoscope_settings'));
		} else {
			//this will run when on the frontend
		}
		add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_colour_picker') );
		add_action( 'horoscop_action', array( &$this, 'action_callback_method_name' ) );
		add_filter( 'horoscop_filter', array( &$this, 'filter_callback_method_name' ) );    
		$h_settings = get_option("horoscop_settings");
	}
  
	function install_horoscop_feeder() {
		global $h_settings;
		$default_settings = array(
			"version" => self::vers,
			"urls" =>  self::feed,
			"count" => 12,
			"width" => "215px",
			"show_date" => 1,
			"show_desc" => 1,
			"show_author" => 0,
			"show_thumb" => 1,
			"open_newtab" => 1,
			"strip_title" => 60,
			"strip_desc" => 140,
			"read_more" => "[...]",
			"link_size" => "14px",
			"link_color" => "#FF0000",
			"text_color" => "#666666",
			"background_color" => "#EEEEEE",
			"item_align" => "none",
			"item_width" => "215px",
			"item_height" => "140px",
			"enable_ticker" => 1,
			"ticker_speed" => 5,
			"visible_items" => 1
		);
		$h_settings = get_option("horoscop_settings");
		if($h_settings === FALSE) {
			add_option('horoscop_settings', $default_settings);
		}
	}
	
	function uninstall_horoscop_feeder() {
		$h_settings = get_option("horoscop_settings");
		if($h_settings) {
			delete_option('horoscop_settings');
		}		
	}

	/**
	 * Runs when the plugin is activated
	 */
    public function addMenu(){
        add_options_page('Horoscope Options', 'Horoscope', 'manage_options', 'horoscop-option-page', array(&$this, 'optionPage'));
    }
	
    public function optionPage(){
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php echo self::name; ?> options page</h2>
			<form action="options.php" method="post" class="horoscope_form">
				<?php
					settings_fields('horoscop_option_group');
					do_settings_sections('horoscop-option-page');
				?>
				<?php submit_button(); ?>
				<h3>See how to look</h3>
			</form>
			<?php $this->rss_parser(); ?>
		</div>
		<?php
    }
	
	public function register_horoscope_settings() {
		global $h_settings;
		register_setting( 'horoscop_option_group', 'h_settings', array(&$this, 'post_callback'));
        add_settings_section( 'section_init', 'Horoscope Feeder setting', array(&$this, 'print_input_info'), 'horoscop-option-page' );
		add_settings_field(
			'_urls',
			'Url',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_init',
			array(
				"type"=>"hidden",
				"style"=>"width:300px;",
				"group"=>"normal",
				"id"=>"urls",
				"value"=>self::feed,
				"class"=>"",
				"desc"=>"Set url to fetch"
			)
		);
		
		add_settings_section( 'section_fourth', 'General setting', '', 'horoscop-option-page' );
		add_settings_field(
			'_count',
			'Count',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"count",
				"value"=>"6",
				"class"=>"",
				"desc"=>"Integer value"
			)
		);
		add_settings_field(
			'_width',
			'Width',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"width",
				"value"=>"230px",
				"class"=>"",
				"desc"=>"Integer value Ex: 100px or 50%"
			)
		);
		add_settings_field(
			'_show_date',
			'Show date',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"show_date",
				"value"=>"1",
				"class"=>"",
				"desc"=>"You can display post date",
				"values" => array("No", "Yes")
			)
		);
		add_settings_field(
			'_show_desc',
			'Show description',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"show_desc",
				"value"=>"1",
				"class"=>"",
				"desc"=>"You can display post description",
				"values" => array("No", "Yes")
			)
		);
		add_settings_field(
			'_show_author',
			'Show author',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"show_author",
				"value"=>"0",
				"class"=>"",
				"desc"=>"You can display post author",
				"values" => array("No", "Yes")
			)
		);
		add_settings_field(
			'_show_thumb',
			'Show thumbs',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"show_thumb",
				"value"=>"1",
				"class"=>"",
				"desc"=>"You can display horoscope logos",
				"values" => array("No", "Yes")
			)
		);
		add_settings_field(
			'_open_newtab',
			'Open new tab',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"open_newtab",
				"value"=>"1",
				"class"=>"",
				"desc"=>"You can open link in new tab",
				"values" => array("No", "Yes")
			)
		);
		add_settings_field(
			'_strip_title',
			'Strip long title',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"strip_title",
				"value"=>"60",
				"class"=>"",
				"desc"=>"Set number of character for strip long title"
			)
		);
		add_settings_field(
			'_strip_desc',
			'Strip long description',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"strip_desc",
				"value"=>"150",
				"class"=>"",
				"desc"=>"Set number of character for strip long descriptions"
			)
		);
		add_settings_field(
			'_read_more',
			'Read more string',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_fourth',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"read_more",
				"value"=>"[...]",
				"class"=>"You can define custom string for [read more]",
				"desc"=>"String value"
			)
		);
		
		add_settings_section( 'section_twice', 'Items settings', array(&$this, 'print_items_info'), 'horoscop-option-page' );
		add_settings_field(
			'_link_size',
			'Item link font size',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_twice',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"link_size",
				"value"=>"14px",
				"class"=>"",
				"desc"=>"You can set post title size. Ex: 14px "
			)
		);
		add_settings_field(
			'_link_color',
			'Item link color',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_twice',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"picker",
				"id"=>"link_color",
				"value"=>"#cccccc",
				"class"=>"",
				"desc"=>"Click field to choose another color"
			)
		);
		add_settings_field(
			'_text_color',
			'Item text font color',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_twice',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"picker",
				"id"=>"text_color",
				"value"=>"#333333",
				"class"=>"",
				"desc"=>"Click field to choose another color"
			)
		);
		add_settings_field(
			'_background_color',
			'Item background color',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_twice',
			array(
				"type"=>"text",
				"style"=>"width:70px;",
				"group"=>"picker",
				"id"=>"background_color",
				"value"=>"#ffffff",
				"class"=>"",
				"desc"=>"Click field to choose another color"
			)
		);
		add_settings_field(
			'_item_align',
			'Item align method',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_twice',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"item_align",
				"value"=>"none",
				"class"=>"",
				"desc"=>"You can align items in panel to Left, Right or None",
				"values" => array("none"=>"None", "left"=>"Left", "right"=>"Right")
			)
		);
		if(isset($h_settings["item_align"]) && $h_settings["item_align"]=="none") {
			add_settings_field(
				'_item_width',
				'Item width',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_twice',
				array(
					"type"=>"hidden",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"item_width",
					"value"=>"200px",
					"class"=>"",
					"desc"=>"If item is aligned, you can set item width. Ex:200px"
				)
			);
			add_settings_field(
				'_item_height',
				'Item height',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_twice',
				array(
					"type"=>"hidden",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"item_height",
					"value"=>"80px",
					"class"=>"",
					"desc"=>"If item is aligned, you can set item height. Ex:80px"
				)
			);
		} else {
			add_settings_field(
				'_item_width',
				'Item width',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_twice',
				array(
					"type"=>"text",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"item_width",
					"value"=>"200px",
					"class"=>"",
					"desc"=>"If item is aligned, you can set item width. Ex:200px"
				)
			);
			add_settings_field(
				'_item_height',
				'Item height',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_twice',
				array(
					"type"=>"text",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"item_height",
					"value"=>"80px",
					"class"=>"",
					"desc"=>"If item is aligned, you can set item height. Ex:80px"
				)
			);
		}

		add_settings_section( 'section_second', 'Ticker settings', array(&$this, 'print_ticker_info'), 'horoscop-option-page' );
		add_settings_field(
			'_enable_ticker',
			'Enable ticker',
			array(&$this, 'create_field'),
			'horoscop-option-page',
			'section_second',
			array(
				"type"=>"checkbox",
				"style"=>"width:70px;",
				"group"=>"normal",
				"id"=>"enable_ticker",
				"value"=>"1",
				"class"=>"",
				"desc"=>"You can enable vertical slide",
				"values" => array("No", "Yes")
			)
		);
		if(isset($h_settings["enable_ticker"]) && $h_settings["enable_ticker"]==1) {
			add_settings_field(
				'_ticker_speed',
				'Ticker speed',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_second',
				array(
					"type"=>"text",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"ticker_speed",
					"value"=>"5",
					"class"=>"",
					"desc"=>"Number of second to ticker change items"
				)
			);
			add_settings_field(
				'_visible_items',
				'Ticker visible items',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_second',
				array(
					"type"=>"text",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"visible_items",
					"value"=>"2",
					"class"=>"",
					"desc"=>"Number of items visible when ticker is enabled"
				)
			);
		} else {
			add_settings_field(
				'_ticker_speed',
				'Ticker speed',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_second',
				array(
					"type"=>"hidden",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"ticker_speed",
					"value"=>"5",
					"class"=>"",
					"desc"=>"Number of second to ticker change items"
				)
			);
			add_settings_field(
				'_visible_items',
				'Ticker visible items',
				array(&$this, 'create_field'),
				'horoscop-option-page',
				'section_second',
				array(
					"type"=>"hidden",
					"style"=>"width:70px;",
					"group"=>"normal",
					"id"=>"visible_items",
					"value"=>"2",
					"class"=>"",
					"desc"=>"Number of items visible when ticker is enabled"
				)
			);
		}
	}
	
	public function post_callback($input) {
		global $h_settings;
		/*
			Some restrictions
		*/
		if(isset($h_settings["enable_ticker"]) && $h_settings["enable_ticker"]) {
			$h_settings["item_align"] = "none";
		}
		if(is_array($input) && count($input)>0) {
			foreach($input as $id => $value) {
				$h_settings[$id] = esc_attr($value);
			}
		}
		/*
		Here for custom sanitize
		*/
		update_option('horoscop_settings', $h_settings);
	}
	
    public function print_input_info(){
		echo '<div style="padding-left:10px;">All configuration can be made here. Enter your setting below and save changes. You can use shortcode [horoscope-feeder] with arguments.<br /><br />
		[horoscope-feeder count="5" width="215px" show_date=1 show_desc=1 show_author=0 show_thumb=1 open_newtab=1 strip_title=60 strip_desc=140 link_color="#FF0000" link_size="14px" text_color="#666666" background_color="#D5D5D5" item_align="none" item_width="215px" item_height="140px" enable_ticker=1 ticker_speed=5 visible_items=1]</div>';
    }
	
	public function print_ticker_info() {
		echo "If enable ticker, you can't align items. Items align can be set to None";
	}
	
	public function print_items_info() {
		echo "If set align to Left or Right, ticker can be set to off. Ticker works good only non aligned items.";
	}

	function action_callback_method_name() {
		// TODO define your action method here
	}

	function filter_callback_method_name() {
		// TODO define your filter method here
	}

	function render_shortcode($atts) {
		extract($atts);
		$args = array();
		if(is_array($atts) && count($atts)>0) {
			foreach($atts as $name => $value) {
				$args[$name] = $value;
			}
		}
		return $this->rss_parser($args, false);
	}
  
	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
		if ( is_admin() ) {
			$this->load_file( self::slug . '-admin-script', '/js/admin.js', true );
			$this->load_file( self::slug . '-admin-style', '/css/admin.css' );
		} else {
			$this->load_file( self::slug . '-script', '/js/widget.js', true );
			$this->load_file( self::slug . '-style', '/css/widget.css' );
		}
	}
	
	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path, $is_script = false ) {
		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;
		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, array('jquery') );
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			}
		}
	}
	
	public function enqueue_colour_picker(){
		wp_enqueue_script('farbtastic');
		wp_enqueue_style( 'farbtastic' );
	}
	
	function rss_parser($args = NULL, $echo = TRUE){
		$out_html = "";
		global $h_settings;
		$instance = $h_settings;
		if(!is_null($args) && is_array($args) && count($args)>0) {
			foreach($args as $instance_name => $instance_value) {
				$instance[$instance_name] = $instance_value;
			}
		}
		$urls = stripslashes($instance['urls']);
		$count = intval($instance['count']);
		$width = stripslashes($instance['width']);
		$show_date = intval($instance['show_date']);
		$show_desc = intval($instance['show_desc']);
		$show_author = intval($instance['show_author']);
		$show_thumb = stripslashes($instance['show_thumb']);
		$open_newtab = intval($instance['open_newtab']);
		$strip_desc = intval($instance['strip_desc']);
		$strip_title = intval($instance['strip_title']);
		$read_more = htmlspecialchars($instance['read_more']);
		$color_style = ""; //stripslashes($instance['color_style']);
		$enable_ticker = intval($instance['enable_ticker']);
		$visible_items = intval($instance['visible_items']);
		$ticker_speed = intval($instance['ticker_speed']) * 1000;
		$text_color = stripslashes($instance['text_color']);
		$link_color = stripslashes($instance['link_color']);
		$link_size = stripslashes($instance['link_size']);
		$bgcolor = stripslashes($instance['background_color']);
		$item_align = stripslashes($instance['item_align']);
		$item_width = stripslashes($instance['item_width']);
		$item_height = stripslashes($instance['item_height']);

		if(empty($urls)){
			return '';
		}

		if($width == 0 || empty($width)) {
			$width = "";
		} else {
			$width = "width:".$width."!important;";
		}

		if($bgcolor == "" || empty($bgcolor)) {
			$bgcolor = "";
		} else {
			$bgcolor = "background-color:".$bgcolor."!important;";
		}

		if($link_color == "" || empty($link_color)) {
			$link_color = "";
		} else {
			$link_color = "color:".$link_color."!important;";
		}

		if($text_color == "" || empty($text_color)) {
			$text_color = "";
		} else {
			$text_color = "color:".$text_color."!important;";
		}

		if($link_size == "" || empty($link_size)) {
			$link_size = "";
		} else {
			$link_size = "font-size:".$link_size."!important;";
		}
		
		if($item_width == "" || empty($item_width)) {
			$item_width = "";
		} else {
			$item_width = "width:".$item_width."!important;";
		}

		if($item_height == "" || empty($item_height)) {
			$item_height = "";
		} else {
			$item_height = "height:".$item_height."!important;";
		}
		
		if($enable_ticker) {
			$item_align = "none";
		}
		
		if($item_align == "none") {
			$item_align = "";
			$item_height = "";
			$item_width = "";
		} else if($item_align == "left") {
			$item_align = "float:left!important;";
		} else if($item_align == "right") {
			$item_align = "float:right!important;";
		}
		
		$rand = array();
		$url = explode(',', $urls);
		$ucount = count($url);
		$i = 0;
		$feedUrl = trim($url[$i]);

		$feedUrl = trim($url[$i]);
		if(isset($url[$i])) {
			add_filter( 'wp_feed_cache_transient_lifetime' , 'return_300' );
			$rss = fetch_feed($feedUrl);
			remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_300' );
		} else {
			$out_html = '<div>No feed detected.</div>';
			if($echo) { echo $out_html; return; } else return $out_html;
		}
		
		if (!is_wp_error( $rss ) ) {
			$maxitems = $rss->get_item_quantity($count);
			$rss_items = $rss->get_items( 0, $maxitems );
			$rss_title = esc_attr(strip_tags($rss->get_title()));
			$rss_desc = esc_attr(strip_tags($rss->get_description()));
		} else {
			$out_html =  '<div>Error in feed.</div>';
			if($echo) { echo $out_html; return; } else return $out_html;
		}
		
		/* START wrap*/
		$out_html .= '<div style="clear:both;"></div><div class="horoscope_feeder_reader">';
		$out_html .= '<div style="padding:3px;'.$width.$bgcolor.$text_color.'" class="horoscope-reader-wrap ' . (($enable_ticker == 1 ) ? 'horoscope-reader-vticker' : '' ) . '" data-visible="' . $visible_items . '" data-speed="' . $ticker_speed . '"><div>';
		
		if ($maxitems == 0){
			$out_html .= '<div>No items in feed.</div>';
		}else{
			$j=1;
			foreach ($rss_items as $item){
				/* Link */
				$link = $item->get_link();
				while ( stristr($link, 'http') != $link ){ $link = substr($link, 1); }
				$link = esc_url(strip_tags($link));
				
				/* Title */
				$titlewithdate = false;
				$title = esc_attr(strip_tags($item->get_title()));
				preg_match('/(.*)\\d{2}\.\\d{2}\.\\d{4}/iU', $title, $match);
				if(count($match) > 0) {
					$titlewithdate = true;
					$feed_date = str_replace($match[1], "", $match[0]);
				}
				
				if ( empty($title) )
					$title = __('No Title');
				
				if($strip_title != 0){
					$titleLen = strlen($title);
					$title = wp_html_excerpt( $title, $strip_title );
					$title = ($titleLen > $strip_title) ? $title . ' ...' : $title;
					if($titlewithdate && $feed_date!="") {
						$title = str_replace(" ".$feed_date, "", $title);
					}
				}
				
				/* Date */
				if($titlewithdate) {
					$date = $feed_date;
				} else {
					$date = $item->get_date('j F Y');
				}
				
				/* Horoscope logos */
				$thumb = '';
				$with_logos = '';
				if ($show_thumb == 1 ){
					$thumburl = "";
					$explode_title = explode(" ", strtolower($title));
					foreach($explode_title as $title_split) {
						if(isset($this->logos[$title_split])) {
							$thumburl = $this->logos[$title_split];
						}
					}
					if(!empty($thumburl)) {
						$thumb = '<img src="' . $thumburl . '" alt="' . $title . '" class="horoscope-reader-thumb" align="left" style="margin-right:6px;" />';
						$with_logos = ' with-logos';
					}
				}

				/* New tab */
				$newtab = ($open_newtab) ? ' target="_blank"' : '';
				
				/* Description */
				$desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option('blog_charset') ) ) ) );
				
				if($strip_desc != 0){
					$desc = wp_html_excerpt( $desc, $strip_desc );
					$rmore = (!empty($read_more)) ?  '<a style="text-decoration:none;" rel="nofollow" href="' . $link . '" ' . $newtab . ' title="Read more">' . $read_more . '</a>' : '';
					
					if ( '[...]' == substr( $desc, -5 ) )
						$desc = substr( $desc, 0, -5 );
					elseif ( '[&hellip;]' != substr( $desc, -10 ) )
						$desc .= '';
						
					$desc = esc_html( $desc );
				}
				$desc = $thumb . $desc . ' ' . $rmore;
				
				/* Author */
				$author = $item->get_author();
				if ( is_object($author) ) {
					$author = $author->get_name();
					$author = esc_html(strip_tags($author));
				}
				
				$out_html .=  "\n\n\t";
				
				$out_html .=  '<div class="horoscope-reader-item ' . (($j%2 == 0) ? 'even' : 'odd') . '" style="'.$item_align.' '.$item_width.' '.$item_height.'">';
				$out_html .=  '<div class="horoscope-reader-title"><a style="'.$link_color.$link_size.'" href="' . $link . '"' . $newtab . ' title="Posted on ' . $date . '">' . $title . '</a></div>';
				$out_html .=  '<div class="horoscope-reader-meta">';
				
				if($show_date && !empty($date))
					$out_html .=  '<time class="horoscope-reader-date">' . $date . '</time>';
				if($show_author && !empty($author))
					$out_html .=  ' - <cite class="horoscope-reader-author">' . $author . '</cite>';
				$out_html .=  '</div>';
				if($show_desc)
					$out_html .=  '<p class="horoscope-reader-summary horoscope-reader-clearfix '.$with_logos.'" style="margin-top:3px!important;">' . $desc . '</p>';
				$out_html .=  '</div>';
				$j++;
			}
		}

		/* END wrap*/
		$out_html .=  "<div style='clear:both;'></div>\n\n</div></div></div>\n\n" ;
		if($echo) echo $out_html; else return $out_html;
	}
}
function return_300() {
	return 300;
}

$_horoscope = new horoscop_feeder();
?>