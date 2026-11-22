<?php
if ( ! defined('ABSPATH') ) exit;

/* =====================================================
 * 1. ADD MENU PAGE
 * ===================================================== */
add_action('admin_menu', function () {
	add_options_page(
		'MS Country Popup Settings',
		'MS Country Popup',
		'manage_options',
		'ms-country-popup',
		'ms_cp_render_settings_page'
	);
});

/* =====================================================
 * 2. HANDLE FORM SAVE (custom handler, no Settings API)
 * ===================================================== */
add_action('admin_init', function () {
	if (
		! isset($_POST['ms_cp_save_settings']) ||
		! current_user_can('manage_options')
	) return;

	check_admin_referer('ms_cp_settings_nonce');

	// --- Default Settings ---
	$default_domain   = esc_url_raw( $_POST['ms_cp_default_domain'] ?? '' );
	$default_currency = strtoupper( sanitize_text_field( $_POST['ms_cp_default_currency'] ?? 'USD' ) );
	update_option('ms_cp_default_domain', $default_domain);
	update_option('ms_cp_default_currency', $default_currency);

	// --- Country Rules (Domain & Currency) ---
	$rules_clean = [];
	if ( ! empty($_POST['ms_cp_country_rules']) && is_array($_POST['ms_cp_country_rules']) ) {
		foreach ( $_POST['ms_cp_country_rules'] as $row ) {
			if ( ! is_array($row) ) continue;
			$code     = strtoupper( sanitize_text_field( $row['code'] ?? '' ) );
			$domain   = esc_url_raw( $row['domain'] ?? '' );
			$currency = strtoupper( sanitize_text_field( $row['currency'] ?? '' ) );

			if ( $code ) {
				$rules_clean[ $code ] = [
					'domain'   => $domain,
					'currency' => $currency
				];
			}
		}
	}
	update_option('ms_cp_country_rules', $rules_clean);

	// --- UI Texts ---
	$trans_keys = [
		'ms_cp_text_title', 'ms_cp_text_country_label', 'ms_cp_text_lang_label',
		'ms_cp_text_lang_value', 'ms_cp_text_search', 'ms_cp_text_button',
		'ms_cp_text_select_title', 'ms_cp_text_select_desc'
	];
	foreach ( $trans_keys as $k ) {
		if ( isset($_POST[$k]) ) {
			update_option($k, sanitize_text_field(wp_unslash($_POST[$k])));
		}
	}

	// Xóa các options cũ không còn dùng (tùy chọn)
	delete_option('ms_cp_currency_exceptions');
	delete_option('ms_cp_domain_exceptions');

	// Redirect with success message
	wp_redirect( add_query_arg('settings-updated', 'true', wp_get_referer()) );
	exit;
});

/* =====================================================
 * 3. ENQUEUE ADMIN ASSETS
 * ===================================================== */
add_action('admin_enqueue_scripts', function ( $hook ) {
	if ( $hook !== 'settings_page_ms-country-popup' ) return;

	$css_path = plugin_dir_path(__FILE__) . 'assets/admin.css';
	$js_path  = plugin_dir_path(__FILE__) . 'assets/admin.js';

	wp_enqueue_style(
		'ms-cp-admin-css',
		plugin_dir_url(__FILE__) . 'assets/admin.css',
		[],
		file_exists($css_path) ? filemtime($css_path) : null
	);
	wp_enqueue_script(
		'ms-cp-admin-js',
		plugin_dir_url(__FILE__) . 'assets/admin.js',
		['jquery'],
		file_exists($js_path) ? filemtime($js_path) : null,
		true
	);
});

/* =====================================================
 * 4. RENDER SETTINGS PAGE
 * ===================================================== */
function ms_cp_render_settings_page() {
	$default_domain   = get_option('ms_cp_default_domain', '');
	$default_currency = get_option('ms_cp_default_currency', 'USD');
	$country_rules    = get_option('ms_cp_country_rules', []);

	$text_title        = get_option('ms_cp_text_title', 'Change Location');
	$text_country_lbl  = get_option('ms_cp_text_country_label', 'Quốc gia đang chọn');
	$text_lang_lbl     = get_option('ms_cp_text_lang_label', 'Ngôn ngữ');
	$text_lang_val     = get_option('ms_cp_text_lang_value', 'Tiếng Việt');
	$text_search       = get_option('ms_cp_text_search', 'Type to search for country');
	$text_button       = get_option('ms_cp_text_button', 'CHỌN QUỐC GIA');
	$text_select_title = get_option('ms_cp_text_select_title', 'Select your country to continue');
	$text_select_desc  = get_option('ms_cp_text_select_desc', 'Please select your shipping location to continue to our online store.');

	if ( ! is_array($country_rules) ) $country_rules = [];
	?>
	<div class="wrap ms-cp-admin">
		<h1>MS Country Popup Settings</h1>

		<?php if ( isset($_GET['settings-updated']) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field('ms_cp_settings_nonce'); ?>
			<input type="hidden" name="ms_cp_save_settings" value="1">

			<!-- ===== DEFAULT ROUTING & CURRENCY ===== -->
			<h2>Default Routing & Currency</h2>
			<p class="description">Cài đặt mặc định cho các quốc gia không nằm trong bảng ngoại lệ.</p>
			<table class="form-table">
				<tr>
					<th><label for="ms_cp_default_domain">Default Domain</label></th>
					<td>
						<input type="text" id="ms_cp_default_domain" name="ms_cp_default_domain"
						       value="<?= esc_attr($default_domain) ?>"
						       class="regular-text" placeholder="https://cenesdesign.com">
						<p class="description">Domain mặc định khi user bấm Confirm.</p>
					</td>
				</tr>
				<tr>
					<th><label for="ms_cp_default_currency">Default Display Currency</label></th>
					<td>
						<input type="text" id="ms_cp_default_currency" name="ms_cp_default_currency"
						       value="<?= esc_attr($default_currency) ?>"
						       class="regular-text" placeholder="USD" maxlength="5">
						<p class="description">Đơn vị tiền tệ mặc định hiển thị cạnh tên quốc gia trong danh sách Popup.</p>
					</td>
				</tr>
			</table>

			<hr>

			<!-- ===== COUNTRY ROUTING RULES ===== -->
			<h2>Country Exceptions (Routing & Currency)</h2>
			<p class="description">Tùy chỉnh riêng Domain và Đơn vị tiền tệ hiển thị cho các quốc gia cụ thể.</p>
			<table class="ms-cp-table" id="rules-table">
				<thead>
					<tr>
						<th>Country Code (ISO)</th>
						<th>Domain URL</th>
						<th>Display Currency</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php $i = 0; foreach ( $country_rules as $code => $data ) : ?>
						<tr>
							<td><input type="text" name="ms_cp_country_rules[<?= $i ?>][code]" value="<?= esc_attr($code) ?>" placeholder="VN" maxlength="2"></td>
							<td><input type="text" name="ms_cp_country_rules[<?= $i ?>][domain]" value="<?= esc_attr($data['domain'] ?? '') ?>" placeholder="https://cenes.vn" class="regular-text"></td>
							<td><input type="text" name="ms_cp_country_rules[<?= $i ?>][currency]" value="<?= esc_attr($data['currency'] ?? '') ?>" placeholder="VND" maxlength="5"></td>
							<td><button type="button" class="button ms-cp-remove-row">✕</button></td>
						</tr>
					<?php $i++; endforeach; ?>
				</tbody>
			</table>
			<button type="button" class="button ms-cp-add-row" data-table="rules-table">+ Add Exception Rule</button>

			<hr>

			<!-- ===== UI TEXT / TRANSLATIONS ===== -->
			<h2>UI Text / Translations</h2>
			<p class="description">Tùy chỉnh các đoạn văn bản hiển thị trên popup (rất hữu ích khi cài đặt ngôn ngữ riêng cho từng website).</p>
			
			<table class="form-table">
				<tr>
					<th><label>Popup Title</label></th>
					<td><input type="text" name="ms_cp_text_title" value="<?= esc_attr($text_title) ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label>Select Country Title</label></th>
					<td><input type="text" name="ms_cp_text_select_title" value="<?= esc_attr($text_select_title) ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label>Select Country Description</label></th>
					<td><input type="text" name="ms_cp_text_select_desc" value="<?= esc_attr($text_select_desc) ?>" class="large-text"></td>
				</tr>
				<tr>
					<th><label>Country Label</label></th>
					<td><input type="text" name="ms_cp_text_country_label" value="<?= esc_attr($text_country_lbl) ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label>Language Label</label></th>
					<td><input type="text" name="ms_cp_text_lang_label" value="<?= esc_attr($text_lang_lbl) ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label>Language Value</label></th>
					<td><input type="text" name="ms_cp_text_lang_value" value="<?= esc_attr($text_lang_val) ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label>Search Placeholder</label></th>
					<td><input type="text" name="ms_cp_text_search" value="<?= esc_attr($text_search) ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label>Confirm Button</label></th>
					<td><input type="text" name="ms_cp_text_button" value="<?= esc_attr($text_button) ?>" class="regular-text"></td>
				</tr>
			</table>

			<hr>
			<?php submit_button('Save Settings'); ?>
		</form>
	</div>
	<?php
}
