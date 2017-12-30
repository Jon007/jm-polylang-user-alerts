# jm-polylang-user-alerts
An easy way to extend Polylang to add extra multilingual messages to your installation.

## Usage:

- use as shortcode eg `[user_alert]`
- use named messsages `[user_alert name="saleflash"]`
- customise/translate messages in Polylang strings translation table
- optional special wooCommerce messages such as additional shipping note

## Background

Often you want to manage some customer alerts and keep them up to date and consistent across different languages and pages, for example:

- This month’s special offer
- December shipping note
- Country-specific note:  note to customers from France, airport strike may affect deliveries… etc

Each of these types of note may appear on multiple pages, and with a manual update it is easy to miss a page out or forget to update the translation.

Polylang (and WPML) language tools have a string translation table that allows the site manager to review and update a whole group of strings at the same time.
So, why not add a group of strings to this table and use the existing APIs for a convenient implementation.

As it turns out this is really easy with Polylang.

## REGISTER THE STRINGS

To add strings to the table all you need to do is this:

```
pll_register_string('saleflash', 'saleflash', 'Polylang User Alerts', TRUE);
pll_register_string('shippingnotice', 'shippingnotice', 'Polylang User Alerts', TRUE);
```

## DEFINE A SHORTCODE

A shortcode is a piece of text that can be inserted into html when editing a page.  When the page is rendered, the contents are replaced by the results of a function.

Our simple shortcode might look like this:

```
[user_alert name="saleflash"]
```

To register it, we just need to map the user_alert shortcode to a function like this:

```
 add_shortcode('user_alert', 'user_alert');
```

And actually define the function, which takes an array of attributes which are the attributes provided on the page – in this example it will be (‘name’ => ‘saleflash’).

The minimum implementation would be:

```
function user_alert($atts = array()){
  return pll__($a['name']);
}
```

the `pll__()` function works like the standard `__()` function. Both detect the current language and attempt to find the language version of the requested string, `__()` looks in the compiled translation files and should be used for strings set at design time, `pll__()` looks in the string translation table and can be used for admin settings etc.

Within any wordpress code:

- all string literals in source code should be wrapped in `__('my string', $myTextDomain)`
- all settings strings which can be changed by the site manager eg in the admin GUI should be wrapped in `pll__($myString)`.

see:
https://jonmoblog.wordpress.com/2017/12/03/quick-multi-lingual-messages-for-wordpress-woocommerce/
