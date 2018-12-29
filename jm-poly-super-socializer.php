<?php
/* 
 * Customizations for Heator Super-Socializer https://wordpress.org/plugins/super-socializer/
 */

function register_socializer_strings(){
    if (function_exists('pll_register_string')) {
        pll_register_string('Login Message', 'Login with Facebook, LinkedIn or Google', __('Super Socializer', 'photoline-inkston'), true);
        pll_register_string('Privacy Message', 'I agree to my personal data being stored and used as per Privacy Policy', __('Super Socializer', 'photoline-inkston'), true);
        pll_register_string('Privacy Policy Link Text', 'Privacy Policy', __('Super Socializer', 'photoline-inkston'), true);
    }
}
add_action('init', 'register_socializer_strings');

/*
 * Filter the login options to translate messages and return correct privacy policy url
 *  - this approach doesn't work because super socializer initializes directly without waiting for events
function filter_option_the_champ_login($champ_login_options, $option_name){

    //it is too early in initialization for get_privacy_policy_url() to work..
    //$champ_login_options['privacy_policy_url'] = get_privacy_policy_url();
    if (function_exists('pll__')) {
        $champ_login_options['title'] = pll__('Login with Facebook, LinkedIn or Google');
        $champ_login_options['privacy_policy_optin_text'] = pll__('I agree to my personal data being stored and used as per Privacy Policy');
        $champ_login_options['ppu_placeholder']  = pll__('Privacy Policy');
    }
    return $champ_login_options;
}
 */
//add_filter( 'option_the_champ_login', 'filter_option_the_champ_login', 10, 2);

function override_socializer_strings(){
    global $theChampLoginOptions;
    if (function_exists('get_privacy_policy_url')){
        $theChampLoginOptions['privacy_policy_url'] = get_privacy_policy_url();
    }
    if (function_exists('pll__')) {
        $theChampLoginOptions['title'] = pll__('Login with Facebook, LinkedIn or Google');
        $theChampLoginOptions['privacy_policy_optin_text'] = pll__('I agree to my personal data being stored and used as per Privacy Policy');
        $theChampLoginOptions['ppu_placeholder']  = pll__('Privacy Policy');
    }
}
add_action('wp_loaded', 'override_socializer_strings');