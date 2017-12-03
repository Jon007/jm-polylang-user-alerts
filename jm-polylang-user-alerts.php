<?php
/*
 * Plugin Name: JM Polylang User Alerts
 * Text Domain: jm-polylang-user-alerts
 * Domain Path: /languages
 * Plugin URI: https://github.com/Jon007/jm-polylang-user-alerts/
 * Assets URI: https://github.com/Jon007/jm-polylang-user-alerts/assets/
 * Author: Jonathan Moore
 * Author URI: https://jonmoblog.wordpress.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: An easy way to extend Polylang to add extra multilingual messages to your installation.
 * Tags: locale, language, translate, message, polylang, woocommerce
 * Contributors: jonathanmoorebcsorg
 * Version: 1.0
 * Stable Tag: 1.0
 * Requires At Least: 4.7
 * Tested Up To: 4.9
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.5
 * Requires PHP: 5.3
 * Version Components: {major}.{minor}.{bugfix}-{stage}{level}
 *
 *	{major}		Major code changes / re-writes or significant feature changes.
 *	{minor}		New features / options were added or improved.
 *	{bugfix}	Bugfixes or minor improvements.
 *	{stage}{level}	dev < a (alpha) < b (beta) < rc (release candidate) < # (production).
 *
 * See PHP's version_compare() documentation at http://php.net/manual/en/function.version-compare.php.
 * 
 * This script is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 3 of the License, or (at your option) any later
 * version.
 * 
 * This script is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details at
 * http://www.gnu.org/licenses/.
 * 
 * Copyright 2017 Jonathan Moore (https://jonmoblog.wordpress.com/)
 */

if ( ! defined( 'ABSPATH' ) ){ 
	die( 'Nothing to see here...' );
}

include_once( plugin_dir_path(__FILE__) . 'jm-polylang-user-alerts-settings.php' );

if ( ! class_exists( 'jm_polylang_user_alerts' ) ) {

	class jm_polylang_user_alerts {

		private static $instance;
		private static $wp_min_version = 4.7;
		private static $polylang_min_version = 2.0;
        
		/**
		 * enqueue scripts and hook buttons according to the options set
		 */
		public function __construct() {

			//optionally, implement different functions on front and back end 
			//$is_admin = is_admin();
			//$on_front = apply_filters( 'jm_polylang_user_alerts_front_end', true );

            add_action('init', array($this, 'activate'));

			add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
			add_action( 'admin_init', array( __CLASS__, 'check_wp_version' ) );
			add_action( 'admin_init', array( __CLASS__, 'check_polylang_version' ) );

			//register scripts and elements to be available on both back and front end
			add_action('admin_head', array( __CLASS__, 'jm_polylang_user_alerts_register_head'));
			add_action('wp_enqueue_scripts', array( __CLASS__, 'jm_polylang_user_alerts_scripts_method'));			

            //defines shortcode [user_alert] mapped to the user_alert function
            add_shortcode('user_alert', array( __CLASS__, 'user_alert'));
            
            //hook woocommerce events: has no effect if woocommerce not activated
            $options = jmpua_get_options();
            if (isset($options['saleflash_cart'])){
                add_action( 'woocommerce_before_cart', array( __CLASS__, 'saleflash' ));
            }
            if (isset($options['saleflash_checkout'])){
                add_action( 'woocommerce_before_checkout_form',  array( __CLASS__, 'saleflash' ) );
            }
            if (isset($options['shipping_alert_cart'])){
                add_action( 'woocommerce_cart_totals_after_shipping', array( __CLASS__, 'shippingalert' ), 60  );
            }
            if (isset($options['shipping_alert_checkout'])){
                add_action( 'woocommerce_review_order_after_shipping', array( __CLASS__, 'shippingalert' ), 60);
            }               
        }

		/**
		 * register all the strings on activation
		 */
        public static function activate(){
            /* setup inkston polylang strings */
            if (function_exists('pll_register_string')){
                //pll_register_string($name, $string, $group, $multiline);
                pll_register_string('saleflash', 'saleflash', 'Polylang User Alerts', TRUE);
                pll_register_string('shippingnotice', 'shippingnotice', 'Polylang User Alerts', TRUE);
                
                self::register_extra_messages('xtra_messages');
                self::register_extra_messages('country_messages');
            }
        }
		/**
		 * register addition messages strings configured in settings
         * @param string $setting - admin setting optionally containing comma separated list of messages
		 */
        public static function register_extra_messages($setting){
            $options = jmpua_get_options();
            $xtra_messages = $options[$setting];
            if ($xtra_messages && ($xtra_messages=explode(",",$xtra_messages) )){
                foreach ($xtra_messages as $xtra_message) {                        
                    pll_register_string($xtra_message, $xtra_message, 'Polylang User Alerts', TRUE); 
                }
            }                                
        }
        
        /* functions hooked to woocommerce actions */
        public static function saleflash(){
            echo(self::user_alert(array('name' => 'saleflash')));
        }
        public static function shippingalert(){
            $output = self::user_alert(array('name' => 'shippingnotice'));
            if ($output!=''){
                //if the output is only html comment debugging code, don't display the row
                if (substr($output, 0, 4) === '<!--') {
                    echo $output;
                } else {
                    echo('<tr><td colspan="2">' . $output . '</td></tr>');
                }
            }
        }
        
		/**
		 * shortcode format function to output user alert
         * @param array $atts - keyed array of shortcode parameters
         * @return string  html shortcode alert
		 */
        public static function user_alert($atts = array()){
            $a = shortcode_atts( array(
                'name' => 'saleflash',
                ), $atts );
            $translation = pll__($a['name']);
            $output='';
            
            /* don't output visible message if no translation available */
            if ($translation == '' || $translation == $a['name']){
                //on non-production environments script debug should be enabled
                if (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ){
                    $output = '<!-- shortcode user-alert : no translation available for ' . 
                    $a['name'] . ' -->';
                }
            } else {
                $messageclass = self::get_option('messageclass', 'jmpua_options');
                $output = '<div class="' . $messageclass . ' user-alert '  . $a['name'] . '">' . 
                    wpautop($translation) . '</div>';
            }
            return $output;            
        }
        
		/**
		 * called in admin mode: do any extra admin stuff then our standard Front End scripts
		 */
		public static function jm_polylang_user_alerts_register_head() {
			self::jm_polylang_user_alerts_scripts_method();
		}

		/**
		 * adds .min versions of scripts unless SCRIPT_DEBUG defined 
		 * also uses file timestamp as version to force update when changed
		 */
		public static function jm_polylang_user_alerts_scripts_method() {
//          avoid adding scripts to payload unless absolutely necessary
//          
//			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
//
//			$csfile='css/jm-polylang-user-alerts' . $suffix . '.css' ;
//			wp_register_style('jm_polylang_user_alerts-css',	plugin_dir_url(__FILE__) . $csfile , false, 
//					filemtime( plugin_dir_path(__FILE__) . $csfile), 'all' );
//			wp_enqueue_style( 'jm_polylang_user_alerts-css');
//			
//			$jsfile='js/jm-polylang-user-alerts' . $suffix . '.js' ;
//			wp_enqueue_script( 'jm_polylang_user_alerts', plugin_dir_url(__FILE__) . $jsfile , array( 'jquery' ), 
//					filemtime( plugin_dir_path(__FILE__) . $jsfile ), true);
		}

        /**
         * Get the value of a settings field.
         *
         * @param string $option  settings field name
         * @param string $section the section name this field belongs to
         * @param string $default default text if it's not found
         *
         * @return mixed
         */
        public static function get_option($option, $section, $default = '')
        {
            $options = get_option($section);

            if (!empty($options[$option])) {  // equivalent to: isset($options[$option]) && $options[$option]
                return $options[$option];
            }   // when all settings are disabled
            elseif (isset($options[$option])) {
                return array();
            } else {
                return $default;
            }
        }

		public static function &get_instance() {
			if ( ! isset( self::$instance ) ){
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * load translations
		 */
		public static function load_textdomain() {
			load_plugin_textdomain( 'jm-polylang-user-alerts', false, 'jm-polylang-user-alerts/languages/' );
		}

		/**
		 * deactivates plugin if Wordpress version < 4.7
		 */
		public static function check_wp_version() {
			global $wp_version;
			if ( version_compare( $wp_version, self::$wp_min_version, '<' ) ) {
				$plugin = plugin_basename( __FILE__ );
				if ( is_plugin_active( $plugin ) ) {
					self::load_textdomain();
					if ( ! function_exists( 'deactivate_plugins' ) ) {
						require_once trailingslashit( ABSPATH ).'wp-admin/includes/plugin.php';
					}
					$plugin_data = get_plugin_data( __FILE__, false );	// $markup = false
					deactivate_plugins( $plugin, true );	// $silent = true
					wp_die( 
						'<p>'.sprintf( __( '%1$s requires %2$s version %3$s or higher and has been deactivated.',
							'jm-polylang-user-alerts' ), $plugin_data['Name'], 'WordPress', self::$wp_min_version ).'</p>'.
						'<p>'.sprintf( __( 'Please upgrade %1$s before trying to reactivate the %2$s plugin.',
							'jm-polylang-user-alerts' ), 'WordPress', $plugin_data['Name'] ).'</p>'
					);
				}
			}
		}

		/**
		 * deactivates plugin if Polylang version < 2
		 */
		public static function check_polylang_version() {
			if ( (! defined('POLYLANG_VERSION')) || version_compare( POLYLANG_VERSION, self::$polylang_min_version, '<' ) ) {
				$plugin = plugin_basename( __FILE__ );
				if ( is_plugin_active( $plugin ) ) {
					self::load_textdomain();
					if ( ! function_exists( 'deactivate_plugins' ) ) {
						require_once trailingslashit( ABSPATH ).'wp-admin/includes/plugin.php';
					}
					$plugin_data = get_plugin_data( __FILE__, false );	// $markup = false
					deactivate_plugins( $plugin, true );	// $silent = true
					wp_die( 
						'<p>'.sprintf( __( '%1$s requires %2$s version %3$s or higher and has been deactivated.',
							'jm-polylang-user-alerts' ), $plugin_data['Name'], 'Polylang', self::$polylang_min_version ).'</p>'.
						'<p>'.sprintf( __( 'Please install or upgrade %1$s before trying to reactivate the %2$s plugin.',
							'jm-polylang-user-alerts' ), 'Polylang', $plugin_data['Name'] ).'</p>'
					);
				}
			}
		}
        
	}//class
}//if class exists

//instantiate and make available so can be called easily
$JMPUA = jm_polylang_user_alerts::get_instance();
$GLOBALS['JMPUA'] = $JMPUA;
