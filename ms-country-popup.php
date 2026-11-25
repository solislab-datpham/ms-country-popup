<?php
/**
 * Plugin Name: MS Country Select Popup
 * Description: Popup chọn quốc gia theo IP (Cloudflare GeoIP) + WooCommerce
 * Version: 1.3.0
 */

if ( ! defined('ABSPATH') ) exit;

/* =====================================================
 * Admin Settings Page
 * ===================================================== */
if ( is_admin() ) {
	require_once plugin_dir_path(__FILE__) . 'admin-settings.php';
}

/*
 * Page-cache contract: the HTML is identical for every visitor. The browser reads the
 * confirmed_country cookie and decides whether to show the popup and which label to print,
 * so a cached page never replays another visitor's (or the anonymous) country state.
 */
function ms_cp_prevent_country_response_cache() {
	do_action( 'litespeed_control_set_nocache', 'Country selection sets a cookie' );
	nocache_headers();
}

function ms_cp_set_confirmed_country( $country ) {
	// Readable by JavaScript on purpose: cached pages cannot ask PHP for it.
	$saved = setcookie(
		'confirmed_country',
		$country,
		array(
			'expires'  => time() + 30 * DAY_IN_SECONDS,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);
	if ( $saved ) {
		$_COOKIE['confirmed_country'] = $country;
	}
	return $saved;
}

/* =====================================================
 * Handle cross-domain ?country= param → set cookie
 * ===================================================== */
add_action('init', function () {
	if ( ! isset( $_GET['country'] ) || ! is_string( $_GET['country'] ) ) {
		return;
	}
	$country = sanitize_text_field( wp_unslash( $_GET['country'] ) );
	if ( preg_match( '/^[A-Z]{2}$/', $country ) ) {
		ms_cp_prevent_country_response_cache();
		ms_cp_set_confirmed_country( $country );
	}
});

/* =====================================================
 * Helper: Get country code from Cloudflare GeoIP
 * ===================================================== */
function ms_get_geo_country() {

	if (
		! empty($_SERVER['HTTP_CF_IPCOUNTRY']) &&
		$_SERVER['HTTP_CF_IPCOUNTRY'] !== 'XX'
	) {
		return sanitize_text_field($_SERVER['HTTP_CF_IPCOUNTRY']);
	}

	return '';
}

/* =====================================================
 * Enqueue CSS / JS (cache bust)
 * ===================================================== */
add_action('wp_enqueue_scripts', function () {

	$css_path = plugin_dir_path(__FILE__) . 'assets/popup.css';
	$js_path  = plugin_dir_path(__FILE__) . 'assets/popup.js';

	wp_enqueue_style(
		'country-popup-css',
		plugin_dir_url(__FILE__) . 'assets/popup.css',
		[],
		file_exists($css_path) ? filemtime($css_path) : null
	);

	// Decide before first paint, so confirmed visitors never see the popup flash.
	wp_register_script( 'country-popup-state', false, [], null, false );
	wp_enqueue_script( 'country-popup-state' );
	wp_add_inline_script(
		'country-popup-state',
		"document.documentElement.classList.toggle('is-country-unconfirmed',!/(?:^|;\\s*)confirmed_country=[A-Z]{2}(?:;|$)/.test(document.cookie))"
	);

	wp_enqueue_script(
		'country-popup-js',
		plugin_dir_url(__FILE__) . 'assets/popup.js',
		['jquery'],
		file_exists($js_path) ? filemtime($js_path) : null,
		true
	);

	// Lấy settings từ admin
	$default_domain   = get_option('ms_cp_default_domain', '');
	$default_currency = get_option('ms_cp_default_currency', 'USD');
	$country_rules    = get_option('ms_cp_country_rules', []);

	if ( ! is_array($country_rules) ) $country_rules = [];

	wp_localize_script('country-popup-js', 'CountryPopup', [
		'ajax'            => admin_url('admin-ajax.php'),
		'geo'             => ms_get_geo_country(),
		'defaultDomain'   => $default_domain,
		'defaultCurrency' => $default_currency,
		'countryRules'    => (object) $country_rules,
	]);
});

/* =====================================================
 * Popup HTML
 * ===================================================== */
add_action('wp_footer', function () {
	$text_select_title = get_option('ms_cp_text_select_title', 'Select your country to continue');
	$text_select_desc  = get_option('ms_cp_text_select_desc', 'Please select your shipping location to continue to our online store.');
	$text_search       = get_option('ms_cp_text_search', 'Type to search for country');
	$text_button       = get_option('ms_cp_text_button', 'CHỌN QUỐC GIA');
	?>
	<div class="js-country-picker" id="country-popup">
		<div class="popup-box">
			<div class="select-country" style="display: none;">
				<div class="country-popup-title"><?= esc_html($text_select_title) ?></div>
				<div class="country-search">
					<i class="icon-search" aria-hidden="true"></i>
					<input class="js-country-search" id="first-country-search" type="text" placeholder="<?= esc_attr($text_search) ?>">
				</div>
				<div class="js-country-list" id="first-continent-list"></div>
			</div>
			<div class="select-country-title">
				<?= esc_html($text_select_desc) ?>
			</div>
			<div class="selected-country">
				<span class="js-country-selected" id="first-selected-country-text"></span>
				<div id="toggle-selector">
					<div class="selector-icon">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 15 15" aria-hidden="true" focusable="false"><path fill="currentColor" stroke="currentColor" stroke-width="1.2" d="m7.875 7.07-.19.062-.19-.063-1.46-.487 1.65 2.752.986-1.643.666-1.109-1.462.487Z"></path></svg>
					</div>
				</div>
			</div>
			<button class="js-country-confirm" id="first-confirm-country" type="button" data-selected-label="CHANGE SHIPPING COUNTRY"><?= esc_html($text_button) ?></button>
		</div>
	</div>
	<?php
});



/* =====================================================
 * Save confirmed country (AJAX)
 * ===================================================== */
add_action('wp_ajax_ms_save_country', 'ms_save_country');
add_action('wp_ajax_nopriv_ms_save_country', 'ms_save_country');
function ms_save_country() {
	ms_cp_prevent_country_response_cache();

	$country = isset( $_POST['country'] ) && is_string( $_POST['country'] )
		? sanitize_text_field( wp_unslash( $_POST['country'] ) )
		: '';
	if ( ! preg_match( '/^[A-Z]{2}$/', $country ) ) {
		wp_send_json_error();
	}
	// ✅ Redirect URL
	$country_rules  = get_option('ms_cp_country_rules', []);
	$default_domain = get_option('ms_cp_default_domain', '');
	
	$redirect_url = $default_domain;
	if ( is_array($country_rules) && isset($country_rules[$country]) && !empty($country_rules[$country]['domain']) ) {
		$redirect_url = $country_rules[$country]['domain'];
	}

	$is_cross_domain = false;
	if ( $redirect_url ) {
		$current_host = $_SERVER['HTTP_HOST'];
		$target_host  = parse_url($redirect_url, PHP_URL_HOST);
		if ( $target_host && $target_host !== $current_host ) {
			$is_cross_domain = true;
		}
	}

	if ( ! $is_cross_domain ) {
		if ( ! ms_cp_set_confirmed_country( $country ) ) {
			wp_send_json_error();
		}
		// ✅ WooCommerce sync
		if ( function_exists('WC') && WC()->customer ) {
			WC()->customer->set_billing_country( $country );
			WC()->customer->set_shipping_country( $country );
			WC()->customer->save();
		}
	}

	// Append ?country= param cho cross-domain cookie
	if ( $redirect_url ) {
		$redirect_url = add_query_arg('country', $country, $redirect_url);
	}

	wp_send_json_success([ 'redirect_url' => $redirect_url ]);
}
/* =====================================================
 * Apply country to WooCommerce (fallback)
 * ===================================================== */
add_filter('woocommerce_checkout_fields', function ($fields) {
	if ( ! empty($_COOKIE['confirmed_country']) ) {
		$country = sanitize_text_field($_COOKIE['confirmed_country']);
		$fields['billing']['billing_country']['default']   = $country;
		$fields['shipping']['shipping_country']['default'] = $country;
	}
	return $fields;
});

/* =====================================================
 * Force WC customer country early (important for tax / shipping)
 * ===================================================== */
add_action('woocommerce_init', function () {
	if ( ! empty($_COOKIE['confirmed_country']) && WC()->customer ) {
		$country = sanitize_text_field($_COOKIE['confirmed_country']);
		if ( WC()->customer->get_billing_country() !== $country ) {
			WC()->customer->set_billing_country( $country );
			WC()->customer->set_shipping_country( $country );
			WC()->customer->save();
		}
	}
});




/* =====================================================
 * Shortcode: Country switcher (VN / VND)
 * ===================================================== */
add_shortcode('ms_country_switcher', function () {
	// Empty on purpose: popup.js prints "VN / VND" from the visitor's own cookie.
	return '<a class="ms-country-switcher ux-lightbox js-country-switcher" href="#ms-country-popup"></a>';
});
/* =====================================================
 * Popup content (Flatsome Lightbox)
 * ===================================================== */
add_action('wp_footer', function () {
	$text_title        = get_option('ms_cp_text_title', 'Change Location');
	$text_country_lbl  = get_option('ms_cp_text_country_label', 'Quốc gia đang chọn');
	$text_lang_lbl     = get_option('ms_cp_text_lang_label', 'Ngôn ngữ');
	$text_lang_val     = get_option('ms_cp_text_lang_value', 'Tiếng Việt');
	$text_search       = get_option('ms_cp_text_search', 'Type to search for country');
	$text_button       = get_option('ms_cp_text_button', 'CHỌN QUỐC GIA');
	?>
	<div class="lightbox-by-id lightbox-content mfp-hide lightbox-white js-country-picker" id="ms-country-popup">
		<div class="ms-country-popup-content">
			<div class="country-popup-title"><?= esc_html($text_title) ?></div>
			<div class="selected-country">
				<div class="selected-country-name">
					<span class="ms-country-label"><?= esc_html($text_country_lbl) ?></span>
					<strong class="js-country-selected" id="selected-country-text"></strong>
				</div>
				<div class="selected-country-language">
					<span class="ms-country-label"><?= esc_html($text_lang_lbl) ?></span>
					<strong><?= esc_html($text_lang_val) ?></strong>
				</div>
			</div>
			<div class="country-search">
				<i class="icon-search" aria-hidden="true"></i>
				<input class="js-country-search" id="country-search" type="text" placeholder="<?= esc_attr($text_search) ?>">
			</div>
			<div class="js-country-list" id="continent-list"></div>
		</div>
		<button class="js-country-confirm" id="confirm-country" type="button" data-selected-label="CONFIRM" disabled><?= esc_html($text_button) ?></button>
	</div>
	<?php
});
