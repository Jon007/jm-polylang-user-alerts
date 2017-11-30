<?php
add_action( 'admin_init', 'jmpua_options_init' );
add_action( 'admin_menu', 'jmpua_add_admin_menu' );
add_filter( 'plugin_action_links_jm-polylang-user-alerts/jm-polylang-user-alerts.php', 'jm_polylang_user_alerts_settings_link' );

/**
 * Add settings link to plugins page
 * @param array $links
 * @return array
 */
function jm_polylang_user_alerts_settings_link( $links ) {
	$links[] = '<a href="'. 
			get_admin_url( null, 'options-general.php?page=jm-polylang-user-alerts' ) . '">' .
			esc_html__( 'Settings', 'jm-polylang-user-alerts' ) . '</a>';
	return $links;
}
/**
 * Add settings link to Admin Settings menu
 */
function jmpua_add_admin_menu(  ) { 
	add_options_page( 'JM Polylang User Alerts', 'Polylang User Alerts', 'manage_options', 'jm-polylang-user-alerts', 'jmpua_options_page' );
}


/*
 * Define options
 */
function jmpua_options_init(  ) { 

	$section_group = 'jmpua_options';
	$section_name = 'jmpua_options';
	register_setting( $section_group, $section_name );

	$settings_section = 'jmpua_options';
	$page = $section_group;
	add_settings_section(
		$settings_section, 
		__( 'Message Options', 'jm-polylang-user-alerts' ),
		'jmpua_options_section_callback', 
		$page
	);

	add_settings_field( 
		'saleflash_shop', 
		__( 'Shop Sale Flash', 'jm-polylang-user-alerts' ), 
		'saleflash_shop_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Show saleflash message on Shop page.', 'jm-polylang-user-alerts' )
    )
	);
	add_settings_field( 
		'saleflash_cart', 
		__( 'Cart Sale Flash', 'jm-polylang-user-alerts' ), 
		'saleflash_cart_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Show saleflash message on Cart page.', 'jm-polylang-user-alerts' )
    )
	);
	add_settings_field( 
		'saleflash_checkout', 
		__( 'Checkout Sale Flash', 'jm-polylang-user-alerts' ), 
		'saleflash_checkout_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Show saleflash message on Checkout page.', 'jm-polylang-user-alerts' )
    )
	);

    add_settings_field( 
		'shipping_alert_cart', 
		__( 'Cart Shipping Alert', 'jm-polylang-user-alerts' ), 
		'shipping_alert_cart_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Show shipping alert below shipping section on Shopping Cart page.', 'jm-polylang-user-alerts' )
    )
	);
        
    add_settings_field( 
		'shipping_alert_checkout', 
		__( 'Checkout Shipping Alert', 'jm-polylang-user-alerts' ), 
		'shipping_alert_checkout_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Show shipping alert below shipping section on Checkout page.', 'jm-polylang-user-alerts' )
    )
	);

    
	add_settings_field( 
		'message_class', 
		__( 'Message Wrapper', 'jm-polylang-user-alerts' ), 
		'message_class_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Additional css class to add to messages.', 'jm-polylang-user-alerts' )
    )
	);

    add_settings_field( 
		'xtra_messages', 
		__( 'Additional Message Types', 'jm-polylang-user-alerts' ), 
		'xtra_messages_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Comma separated list of additional message strings to use in shortcode [user-alert name="{message type}"].', 'jm-polylang-user-alerts' )
    )
	);

    add_settings_field( 
		'country_messages', 
		__( 'Additional Country Messages', 'jm-polylang-user-alerts' ), 
		'country_messages_render', 
		$section_group, 
		$settings_section,
		array(
			__( 'Comma separated list of additional country code messages to create, in order to show when user is first detected from that country, eg "Notice for French users: airport strikes expected may impact delivery".'
                . '.. remember to delete that message after the strike is over', 'jm-polylang-user-alerts' )
    )
	);
}
/* Load or default the options, once */
function jmpua_get_options() {
    static $options;    
    if (! $options){
		//Pull from WP options database table
		$options = get_option('jmpua_options');
        //set defaults if not set
		if (!is_array($options)) {
			$options['message_class'] = '';
			$options['xtra_messages'] = '';
			$options['country_messages'] = '';
			$options['saleflash_shop'] = true;
			$options['saleflash_cart'] = true;
			$options['saleflash_checkout'] = false;
			$options['shipping_alert_cart'] = false;
			$options['shipping_alert_checkout'] = true;
			//update_option('jm_polylang_user_alerts_options', $options);
		}
    }
    return $options;
}
/* Option display callbacks */
function saleflash_shop_render( $s ) {  render_checkbox('saleflash_shop', $s); }
function saleflash_cart_render( $s ) {  render_checkbox('saleflash_cart', $s); }
function saleflash_checkout_render( $s ) {  render_checkbox('saleflash_checkout', $s); }
function shipping_alert_cart_render( $s ) {  render_checkbox('shipping_alert_cart', $s); }
function shipping_alert_checkout_render( $s ) {  render_checkbox('shipping_alert_checkout', $s); }
function message_class_render( $s ) {  render_input('message_class', $s); }
function xtra_messages_render( $s ) {  render_multiline('xtra_messages', $s); }
function country_messages_render( $s ) {  render_multiline('country_messages', $s); }
/* Option render controls - standard input box*/
function render_input($optionName, $s){
	$options = jmpua_get_options();
	?>
	<input type="text" name="jmpua_options[<?php 
        echo($optionName) ?>]" id="<?php echo($optionName) ?>" value="<?php 
        if (isset($options[$optionName])){echo $options[$optionName];} ?>" /> 
	<?php echo(implode(' ', $s));
}
/* Option render controls - standard textarea */
function render_multiline($optionName, $s){
	$options = jmpua_get_options();
	?>
	<textarea style="width:100%" name="jmpua_options[<?php 
        echo($optionName) ?>]" id="<?php echo($optionName) ?>"><?php 
        if (isset($options[$optionName])){echo $options[$optionName];} ?></textarea>
	<?php echo(implode(' ', $s));
}
/* Option render controls - standard checkbox*/
function render_checkbox($optionName, $s){
	$options = jmpua_get_options();
	?>
	<input type="checkbox" name="jmpua_options[<?php echo($optionName) ?>]" id="<?php echo($optionName) ?>" <?php 
	 	checked(isset($options[$optionName] ), true);
	?> value="1">
	<?php echo(implode(' ', $s));
}
/* Option section title */
function jmpua_options_section_callback(  ) { 
	_e( 'Options for the translated messages:', 'jm-polylang-user-alerts' );
}


function jmpua_options_page(  ) { 
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
    $translations_link = admin_url() . '/admin.php?page=mlang_strings&s&group=Polylang+User+Alerts&paged=1';
	echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';	
	?>
	<form action='options.php' method='post'>

		<h2>JM Polylang User Alerts</h2>
		<p><a target="_blank" href="https://github.com/Jon007/jm-polylang-user-alerts/">jm-polylang-user-alerts</a> <?php _e('is a translation helper tool from', 'jm-polylang-user-alerts')?> <a target="_blank" href="https://jonmoblog.wordpress.com/">Jonathan Moore</a>.</p>
		
		<?php
		settings_fields( 'jmpua_options' );
		do_settings_sections( 'jmpua_options' );
		submit_button();
		?>

	</form>
<h2>Usage</h2>
<p>After saving the settings, <a href="<?php echo($translations_link) ?>">click here</a> to manage the message strings and their translations in the Polylang string translations table.</p>
<p>Please see <a href="https://github.com/Jon007/jm-polylang-user-alerts/">jm-polylang-user-alerts on Github</a> for more details.</p>
<h2>Notes</h2>
<p>This tool is provided free as-is, use and modify as you like.</p>
<p>Polylang is required.</p>
<p>WooCommerce is recommended: 
<ul><li>if used without WooCommerce 3 then WooCommerce related settings will have no effect. 
  Due to the huge number of api changes in version 3, earlier versions of WooCommerce will be ignored.</li>
<li>if used with WooCommerce then note that additional plugin such as Hyyan WooCommerce Polylang integration is needed to allow WooCommerce to work correctly with Polylang.</li></ul>	<?php
}