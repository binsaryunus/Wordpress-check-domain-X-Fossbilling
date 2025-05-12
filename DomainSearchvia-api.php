<?php
/**
 * Plugin Name: API Domain Search
 * Plugin URI: https://apiku.id/
 * Description: Provides a domain search form that checks availability across all configured TLDs in Billing, displays pricing, and offers checkout links.
 * Version: 1.3
 * Author: APIKU.ID
 * Plugin URI: https://apiku.id/
 * Author URI: https://apiku.id
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.x
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

// Option name
define('BB_DC_OPTION', 'bb_dc_settings');

/** Default settings */
function bb_dc_get_settings() {
    $defaults = [
        'api_base_url'         => '',
        'default_checkout_url' => '',
        'placeholder'          => 'example.com',
        'shortcode_tag'        => 'bb_domain_search',
    ];
    return wp_parse_args(get_option(BB_DC_OPTION, []), $defaults);
}

/** Save settings */
function bb_dc_save_settings($data) {
    update_option(BB_DC_OPTION, $data);
}

/** Add settings page */
add_action('admin_menu', function() {
    add_options_page('BoxBilling Domain Search', 'Domain Search', 'manage_options', 'bb-dc-settings', 'bb_dc_settings_page');
});

/** Render settings page */
function bb_dc_settings_page() {
    if (!current_user_can('manage_options')) wp_die('Access denied');
    $opts = bb_dc_get_settings();
    if (isset($_POST['bb_dc_submit'])) {
        check_admin_referer('bb_dc_save', 'bb_dc_nonce');
        $opts['api_base_url']         = esc_url_raw($_POST['bb_api_base_url'] ?? $opts['api_base_url']);
        $opts['default_checkout_url'] = esc_url_raw($_POST['bb_default_checkout_url'] ?? $opts['default_checkout_url']);
        $opts['placeholder']          = sanitize_text_field($_POST['bb_placeholder'] ?? $opts['placeholder']);
        $opts['shortcode_tag']        = sanitize_key($_POST['bb_shortcode_tag'] ?? $opts['shortcode_tag']);
        bb_dc_save_settings($opts);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>API Domain Search Settings</h1>
        <form method="post">
            <?php wp_nonce_field('bb_dc_save','bb_dc_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="bb_api_base_url">API Base URL</label></th>
                    <td><input name="bb_api_base_url" id="bb_api_base_url" type="url" class="regular-text" value="<?php echo esc_attr($opts['api_base_url']); ?>" />
                    <p class="description"> Guest API base, e.g. https://billing.example.com/api/guest/servicedomain</p></td>
                </tr>
                <tr>
                    <th><label for="bb_default_checkout_url">Default Checkout URL</label></th>
                    <td><input name="bb_default_checkout_url" id="bb_default_checkout_url" type="url" class="regular-text" value="<?php echo esc_attr($opts['default_checkout_url']); ?>" />
                    <p class="description">URL to your billing system domain registration page.</p></td>
                </tr>
                <tr>
                    <th><label for="bb_placeholder">Placeholder</label></th>
                    <td><input name="bb_placeholder" id="bb_placeholder" type="text" class="regular-text" value="<?php echo esc_attr($opts['placeholder']); ?>" />
                    <p class="description">Input placeholder text.</p></td>
                </tr>
                <tr>
                    <th><label for="bb_shortcode_tag">Shortcode Tag</label></th>
                    <td><input name="bb_shortcode_tag" id="bb_shortcode_tag" type="text" class="regular-text" value="<?php echo esc_attr($opts['shortcode_tag']); ?>" />
                    <p class="description">Shortcode name, e.g. <code>bb_domain_search</code>.</p></td>
                </tr>
            </table>
            <?php submit_button('Save Settings','primary','bb_dc_submit'); ?>
        </form>
    </div>
    <?php
}

/** Register shortcode dynamically */
add_action('init', function() {
    $tag = bb_dc_get_settings()['shortcode_tag'];
    if ($tag) add_shortcode($tag, 'bb_dc_render_form');
});

/** Render search form */
function bb_dc_render_form() {
    $opts = bb_dc_get_settings();
    $placeholder = esc_attr($opts['placeholder']);
    $checkout    = esc_url($opts['default_checkout_url']);
    $ajaxurl     = admin_url('admin-ajax.php');
    $nonce       = wp_create_nonce('bb_dc_action');
    ob_start(); ?>
    <form id="bb-dc-form" style="margin-bottom:1em;">
        <input type="text" id="bb-dc-sld" placeholder="<?php echo $placeholder; ?>" required />
        <button type="submit">Check</button>
    </form>
    <div id="bb-dc-result"></div>
    <style>
    .bb-dc-table { width:100%;border-collapse:collapse; }
    .bb-dc-table th,.bb-dc-table td{border:1px solid #ddd;padding:8px;text-align:left;}
    .bb-dc-button{display:inline-block;background:#007bff;color:#fff;padding:0.5em 1em;border-radius:4px;text-decoration:none;}
    .bb-dc-button:hover{background:#0056b3;}
    </style>
    <script>
    (function(){
        var form = document.getElementById('bb-dc-form');
        var input= document.getElementById('bb-dc-sld');
        var res  = document.getElementById('bb-dc-result');
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var sld = input.value.trim();
            if(!sld) return;
            res.innerHTML = 'Loading...';
            var data = {
                action: 'bb_dc_check',
                sld: sld,
                checkout_url: '<?php echo $checkout; ?>',
                nonce: '<?php echo $nonce; ?>'
            };
            fetch('<?php echo $ajaxurl; ?>', {
                method:'POST',
                credentials:'same-origin',
                headers: { 'Content-Type':'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            }).then(r=>r.json()).then(json=>{
                if(json.success) res.innerHTML = json.data.html;
                else res.innerHTML = '<div style="color:red">Error: '+json.data.message+'</div>';
            }).catch(_=>res.innerHTML = '<div style="color:red">Request failed</div>');
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

/** AJAX handler */
add_action('wp_ajax_bb_dc_check', 'bb_dc_check');
add_action('wp_ajax_nopriv_bb_dc_check', 'bb_dc_check');
function bb_dc_check() {
    $nonce = $_POST['nonce'] ?? '';
    if (! wp_verify_nonce($nonce, 'bb_dc_action')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }
    $input = sanitize_text_field($_POST['sld'] ?? '');
    if (strpos($input, '.') !== false) {
        list($sld) = explode('.', $input, 2);
    } else {
        $sld = $input;
    }
    if (! preg_match('/^[A-Za-z0-9\-]+$/', $sld)) {
        wp_send_json_error(['message' => 'Invalid domain']);
    }
    $opts = bb_dc_get_settings();
    $api  = rtrim($opts['api_base_url'], '/');

    // Fetch TLDs
    $tlds_resp = wp_remote_post(
        $api . '/tlds',
        [
            'body'    => wp_json_encode(['allow_register' => true]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 20,
        ]
    );
    if (is_wp_error($tlds_resp)) {
        wp_send_json_error(['message' => $tlds_resp->get_error_message()]);
    }
    $tlds_data = json_decode(wp_remote_retrieve_body($tlds_resp), true);
    if (empty($tlds_data['result']) || ! is_array($tlds_data['result'])) {
        wp_send_json_error(['message' => 'Failed to retrieve TLDs']);
    }
    $tlds_list = $tlds_data['result'];

    // Prepare results table rows
    $rows = [];
    foreach ($tlds_list as $item) {
        $tld = ltrim($item['tld'], '.');
        // Check availability via API
        $check_resp = wp_remote_post(
            $api . '/check',
            [
                'body'    => wp_json_encode(['sld' => $sld, 'tld' => '.' . $tld]),
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 20,
            ]
        );
        $available = false;
        if (! is_wp_error($check_resp)) {
            $check_data = json_decode(wp_remote_retrieve_body($check_resp), true);
            $available  = isset($check_data['result']) && $check_data['result'] === true;
        }
        // Determine price
        $price = '-';
        if ($available && isset($item['price_registration'])) {
            $price = number_format_i18n((float) $item['price_registration'], 0);
        }
        // Build action button or unavailable text
        if ($available) {
            $url    = esc_url_raw($opts['default_checkout_url'] . (strpos($opts['default_checkout_url'], '?') === false ? '?' : '&') . 'domain=' . urlencode($sld . '.' . $tld));
            $action = "<a class='bb-dc-button' href='{$url}'>Checkout</a>";
        } else {
            $action = '<span style="color:#d00">Unavailable</span>';
        }
        $rows[] = "<tr><td>{$sld}.{$tld}</td><td>Rp {$price}</td><td>{$action}</td></tr>";
    }

    $html = '<table class="bb-dc-table"><thead><tr><th>Domain</th><th>Price</th><th>Action</th></tr></thead><tbody>'
          . implode('', $rows)
          . '</tbody></table>';

    wp_send_json_success(['html' => $html]);
}
