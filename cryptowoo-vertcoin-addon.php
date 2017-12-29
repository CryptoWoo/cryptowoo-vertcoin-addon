<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Name: CryptoWoo Vertcoin Add-on
 * Plugin URI: https://github.com/Olsm/cryptowoo-vertcoin-addon
 * GitHub Plugin URI: Olsm/cryptowoo-bitcoin-cash-addon
 * Forked From: Olsm/cryptowoo-bitcoin-cash-addon
 * - Bitcoin Cash Addon was Forked From: CryptoWoo/cryptowoo-dash-addon, Author: flxstn
 * Description: Accept VTC payments in WooCommerce. Requires CryptoWoo main plugin and CryptoWoo HD Wallet Add-on.
 * Version: 1.1
 * Author: Olav Småriset
 * Author URI: https://github.com/Olsm
 * License: GPLv2
 * Text Domain: cryptowoo-vtc-addon
 * Domain Path: /lang
 * WC tested up to: 3.2.5
 *
 */

define( 'CWVTC_VER', '1.1' );
define( 'CWVTC_FILE', __FILE__ );
$plugin_dir     = WP_PLUGIN_DIR;
$hd_add_on_file = 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php';
$hd_add_on_dir  = $plugin_dir . '/' . $hd_add_on_file;
$cw_plugin_dir  = $plugin_dir . '/cryptowoo/cryptowoo.php';

// Load the plugin update library if it is not already loaded
if ( ! class_exists( 'CWVTC_License_Menu' ) && file_exists( plugin_dir_path( $hd_add_on_dir ) . 'am-license-menu.php' ) ) {
	require_once( plugin_dir_path( $hd_add_on_dir ) . 'am-license-menu.php' );

	class CWVTC_License_Menu extends CW_License_Menu {};

	CWVTC_License_Menu::instance( CWVTC_FILE, 'CryptoWoo Vertcoin Add-on', CWVTC_VER, 'plugin', 'https://www.cryptowoo.com/' );
}

/**
 * Plugin activation
 */
function cryptowoo_vtc_addon_activate() {

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$hd_add_on_file = 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php';
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $hd_add_on_file ) || ! file_exists( WP_PLUGIN_DIR . '/cryptowoo/cryptowoo.php' ) ) {

		// If WooCommerce is not installed then show installation notice
		add_action( 'admin_notices', 'cryptowoo_vtc_notinstalled_notice' );

		return;
	} elseif ( ! is_plugin_active( $hd_add_on_file ) ) {
		add_action( 'admin_notices', 'cryptowoo_vtc_inactive_notice' );

		return;
	}
}

register_activation_hook( __FILE__, 'cryptowoo_vtc_addon_activate' );
add_action( 'admin_init', 'cryptowoo_vtc_addon_activate' );

/**
 * CryptoWoo inactive notice
 */
function cryptowoo_vtc_inactive_notice() {

	?>
    <div class="error">
        <p><?php _e( '<b>CryptoWoo Vertcoin add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on has been deactivated.<br>
       				Please go to the Plugins menu and make sure that the CryptoWoo HD Wallet add-on is activated.', 'cryptowoo-vtc-addon' ); ?></p>
    </div>
	<?php
}


/**
 * CryptoWoo HD Wallet add-on not installed notice
 */
function cryptowoo_vtc_notinstalled_notice() {
	$addon_link = '<a href="https://www.cryptowoo.com/shop/cryptowoo-hd-wallet-addon/" target="_blank">CryptoWoo HD Wallet add-on</a>';
	?>
    <div class="error">
        <p><?php printf( __( '<b>CryptoWoo Vertcoin add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on is not installed.<br>
					The CryptoWoo Vertcoin add-on will only work in combination with the CryptoWoo main plugin and the %s.', 'cryptowoo-vtc-addon' ), $addon_link ); ?></p>
    </div>
	<?php
}

function cwvtc_hd_enabled() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php' ) && is_plugin_active( 'cryptowoo/cryptowoo.php' );
}

if ( cwvtc_hd_enabled() ) {
	// Coin symbol and name
	add_filter( 'woocommerce_currencies', 'cwvtc_woocommerce_currencies', 10, 1 );
	add_filter( 'cw_get_currency_symbol', 'cwvtc_get_currency_symbol', 10, 2 );
	add_filter( 'cw_get_enabled_currencies', 'cwvtc_add_coin_identifier', 10, 1 );

	// BIP32 prefixes
	add_filter( 'address_prefixes', 'cwvtc_address_prefixes', 10, 1 );

	// Custom block explorer URL
	add_filter( 'cw_link_to_address', 'cwvtc_link_to_address', 10, 4 );

	// Options page validations
	add_filter( 'validate_custom_api_genesis', 'cwvtc_validate_custom_api_genesis', 10, 2 );
	add_filter( 'validate_custom_api_currency', 'cwvtc_validate_custom_api_currency', 10, 2 );
	add_filter( 'cryptowoo_is_ready', 'cwvtc_cryptowoo_is_ready', 10, 3 );
	add_filter( 'cw_get_shifty_coins', 'cwvtc_cw_get_shifty_coins', 10, 1 );
	add_filter( 'cw_misconfig_notice', 'cwvtc_cryptowoo_misconfig_notice', 10, 2 );

	// HD wallet management
	add_filter( 'index_key_ids', 'cwvtc_index_key_ids', 10, 1 );
	add_filter( 'mpk_key_ids', 'cwvtc_mpk_key_ids', 10, 1 );
	add_filter( 'get_mpk_data_mpk_key', 'cwvtc_get_mpk_data_mpk_key', 10, 3 );
	add_filter( 'get_mpk_data_network', 'cwvtc_get_mpk_data_network', 10, 3 );
	add_filter( 'cw_discovery_notice', 'cwvtc_add_currency_to_array', 10, 1 );

	// Currency params
	add_filter( 'cw_get_currency_params', 'cwvtc_get_currency_params', 10, 2 );

	// Order sorting and prioritizing
	add_filter( 'cw_sort_unpaid_addresses', 'cwvtc_sort_unpaid_addresses', 10, 2 );
	add_filter( 'cw_prioritize_unpaid_addresses', 'cwvtc_prioritize_unpaid_addresses', 10, 2 );
	add_filter( 'cw_filter_batch', 'cwvtc_filter_batch', 10, 2 );

	// Add discovery button to currency option
	//add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_vtc_mpk', 'hd_wallet_discovery_button' );
	add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_vtc_mpk', 'hd_wallet_discovery_button' );

	// Exchange rates
	add_filter( 'cw_force_update_exchange_rates', 'cwvtc_force_update_exchange_rates', 10, 2 );
	add_filter( 'cw_cron_update_exchange_data', 'cwvtc_cron_update_exchange_data', 10, 2 );

	// Catch failing processing API (only if processing_fallback is enabled)
	add_filter( 'cw_get_tx_api_config', 'cwvtc_cw_get_tx_api_config', 10, 3 );

	// Insight API URL
	add_filter( 'cw_prepare_insight_api', 'cwvtc_override_insight_url', 10, 4 );

	// Add Blockdozer processing
	add_filter( 'cw_update_tx_details', 'cwvtc_cw_update_tx_details', 10, 5 );

	// Wallet config
	add_filter( 'wallet_config', 'cwvtc_wallet_config', 10, 3 );
	add_filter( 'cw_get_processing_config', 'cwvtc_processing_config', 10, 3 );

	// Options page
	add_action( 'plugins_loaded', 'cwvtc_add_fields', 10 );
}

/**
 * Vertcoin font color for aw-cryptocoins
 * see cryptowoo/assets/fonts/aw-cryptocoins/cryptocoins-colors.css
 */
function cwvtc_coin_icon_color() { ?>
    <style type="text/css">
        i.cc.VTC:before, i.cc.VTC-alt:before {
            content: "\e92c";
        }

        i.cc.VTC, i.cc.VTC-alt {
            color: #1b5c2e;
        }
    </style>
<?php }

add_action( 'wp_head', 'cwvtc_coin_icon_color' );

/**
 * Processing API configuration error
 *
 * @param $enabled
 * @param $options
 *
 * @return mixed
 */
function cwvtc_cryptowoo_misconfig_notice( $enabled, $options ) {
	$enabled['VTC'] = $options['processing_api_vtc'] === 'disabled' && ( (bool) CW_Validate::check_if_unset( 'cryptowoo_vtc_mpk', $options ) );

	return $enabled;
}

/**
 * Add currency name
 *
 * @param $currencies
 *
 * @return mixed
 */
function cwvtc_woocommerce_currencies( $currencies ) {
	$currencies['VTC'] = __( 'Vertcoin', 'cryptowoo' );

	return $currencies;
}


/**
 * Add currency symbol
 *
 * @param $currency_symbol
 * @param $currency
 *
 * @return string
 */
function cwvtc_get_currency_symbol( $currency_symbol, $currency ) {
	return $currency === 'VTC' ? 'VTC' : $currency_symbol;
}


/**
 * Add coin identifier
 *
 * @param $coin_identifiers
 *
 * @return array
 */
function cwvtc_add_coin_identifier( $coin_identifiers ) {
	$coin_identifiers['VTC'] = 'vtc';

	return $coin_identifiers;
}


/**
 * Add address prefix
 *
 * @param $prefixes
 *
 * @return array
 */
function cwvtc_address_prefixes( $prefixes ) {
	$prefixes['VTC']          = '47';
	$prefixes['VTC_MULTISIG'] = '05';

	return $prefixes;
}


/**
 * Add wallet config
 *
 * @param $wallet_config
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwvtc_wallet_config( $wallet_config, $currency, $options ) {
	if ( $currency === 'VTC' ) {
		$wallet_config                       = array(
			'coin_client'   => 'vertcoin',
			'request_coin'  => 'VTC',
			'multiplier'    => (float) $options['multiplier_vtc'],
			'safe_address'  => false,
			'decimals'      => 8,
			'mpk_key'       => 'cryptowoo_vtc_mpk',
			'fwd_addr_key'  => 'safe_vtc_address',
			'threshold_key' => 'forwarding_threshold_vtc'
		);
		$wallet_config['hdwallet']           = CW_Validate::check_if_unset( $wallet_config['mpk_key'], $options, false );
		$wallet_config['coin_protocols'][]   = 'vtc';
		$wallet_config['forwarding_enabled'] = false;
	}

	return $wallet_config;
}

/**
 * Add InstantSend and "raw" zeroconf settings to processing config
 *
 * @param $pc_conf
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwvtc_processing_config( $pc_conf, $currency, $options ) {
	if ( $currency === 'VTC' ) {
		$pc_conf['instant_send']       = isset( $options['vtc_instant_send'] ) ? (bool) $options['vtc_instant_send'] : false;
		$pc_conf['instant_send_depth'] = 5; // TODO Maybe add option

		// Maybe accept "raw" zeroconf
		$pc_conf['min_confidence'] = isset( $options['cryptowoo_vtc_min_conf'] ) && (int) $options['cryptowoo_vtc_min_conf'] === 0 && isset( $options['vtc_raw_zeroconf'] ) && (bool) $options['vtc_raw_zeroconf'] ? 0 : 1;
	}

	return $pc_conf;
}


/**
 * Override links to payment addresses
 *
 * @param $url
 * @param $address
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwvtc_link_to_address( $url, $address, $currency, $options ) {
	if ( $currency === 'VTC' ) {
		$url = "http://explorer.vertcoin.info/address/{$address}";
		if ( $options['preferred_block_explorer_vtc'] === 'custom' && isset( $options['custom_block_explorer_vtc'] ) ) {
			$url = preg_replace( '/{{ADDRESS}}/', $address, $options['custom_block_explorer_vtc'] );
			if ( ! wp_http_validate_url( $url ) ) {
				$url = '#';
			}
		}
	}

	return $url;
}

/**
 * Do explorer.vertcoin.info api processing if chosen
 *
 * @param $batch_data
 * @param $batch_currency
 * @param $orders
 * @param $processing
 * @param $options
 *
 * @return string
 */
function cwvtc_cw_update_tx_details( $batch_data, $batch_currency, $orders, $processing, $options ) {
	if ( $batch_currency == "VTC" && $options['processing_api_vtc'] == "vertcoin.info" ) {
		$options['custom_api_vtc']     = "http://explorer.vertcoin.info/";
		$batch                         = $orders[0]->address;
		$batch_data[ $batch_currency ] = cwvtc_vertcoin_api_tx_update( $batch, $orders[0], $options );
		usleep( 333333 ); // Max ~3 requests/second TODO remove when we have proper rate limiting

        $chain_height = cwvtc_vertcoin_api_get_block_height($options);

        // Convert to correct format for insight_tx_analysis
        if (isset($batch_data["VTC"]) && is_object($batch_data["VTC"])) {
            $vtcData = $batch_data["VTC"];
            // There is only an incoming payment if address exist
            if (isset($vtcData->address)) {
	            $address = $vtcData->address;
	            $txs = $vtcData->last_txs;
	            if ($vtcData->received > 0 && $vtcData->balance > 0) {
		            $txs[0]->confirmations = 1;
	            } else {
		            $txs[0]->confirmations = 0;
	            }
	            $txs[0]->time = strtotime($orders[0]->created_at);
	            $txs[0]->txid = $txs[0]->addresses;
	            $vout = new stdClass();
	            $vout->scriptPubKey->addresses = [$address];
	            $vout->value = $vtcData->received;
	            $txs[0]->vout = [$vout];
	            $batch_data[$address] = $txs;
            }
        } else {
            // ToDo: log error
            $batch_data = [];
        }

        $batch_data = CW_Insight::insight_tx_analysis($orders, $batch_data, $options, $chain_height, true);
	}

	return $batch_data;
}

function cwvtc_vertcoin_api_get_block_height($options) {
    $currency = "VTC";

	$bh_transient = sprintf('block-height-%s', $currency);
	if(false !== ($block_height = get_transient($bh_transient))) {
		return (int)$block_height;
	}

	$error = '';

	// Get data
	$url = $options['custom_api_vtc'] . "api/getblockcount";

	$result = wp_remote_get($url);

	if (is_wp_error($result)) {

		$error = $result->get_error_message();

		// Action hook for Insight API error
		do_action('cryptowoo_api_error', 'Insight API error: '.$error);

		// Update rate limit transient
		$limit_transient[$currency] = isset($limit_transient[$currency]['count']) ? array('count' => (int)$limit_transient[$currency]['count'] + 1,
		                                                                                  'api' => 'insight') : array('count' => 1,
		                                                                                                              'api' => 'insight');
		// Keep error data until the next full hour (rate limits refresh every full hour). We'll try again after that time.
		set_transient('cryptowoo_limit_rates', $limit_transient, CW_AdminMain::seconds_to_next_hour());

	} else {
		$result = json_decode($result['body']);
	}

	if(isset($result) && is_integer($result)) {
		$block_height = $result;
		set_transient($bh_transient, $block_height, 180); // Cache for 3 minutes
	} else {
		$block_height = 0;
	}

	if ((bool)$error) {
		file_put_contents(CW_LOG_DIR . 'cryptowoo-tx-update.log', date('Y-m-d H:i:s') . " Insight get_block_height {$error}\r\n", FILE_APPEND);
	}
	return (int)$block_height;
}

function cwvtc_vertcoin_api_tx_update($address, $order, $options) {
    $currency = "VTC";
	$error = $result = false;

	// Rate limit transient
	$limit_transient = get_transient('cryptowoo_limit_rates');

	// Get data
	$url = $options['custom_api_vtc'] . "ext/getaddress/" . $address;

	$result = wp_remote_get($url);

	if (is_wp_error($result)) {
	    $error = $result->get_error_message();

	    // Error "A valid URL was not provided means Vertcoin address did not receive any payment
	    if ($error == "A valid URL was not provided.") {
            $error = false;
            $result = [];
        } else {
		    $error = $error . $url ;

		    // Action hook for API error
		    do_action('cryptowoo_api_error', 'API error: '.$error);

		    // Update rate limit transient
		    if(isset($limit_transient[$currency]['count'])) {
			    $limit_transient[$currency] = array(
				    'count' => (int)$limit_transient[$currency]['count'] + 1,
				    'api' => 'explorer.vertcoin.info'
			    );
		    } else {
			    $limit_transient[$currency] = array(
				    'count' => 1,
				    'api' => 'explorer.vertcoin.info'
			    );
		    }
		    // Keep error data until the next full hour (rate limits refresh every full hour). We'll try again after that time.
		    set_transient('cryptowoo_limit_rates', $limit_transient, CW_AdminMain::seconds_to_next_hour());
		    file_put_contents(CW_LOG_DIR . 'cryptowoo-tx-update.log', date('Y-m-d H:i:s') . " Insight full address error {$error}\r\n", FILE_APPEND);
        }
	} else {
		$result = json_decode($result['body']);
	}
	// Delete rate limit transient if the last call was successful
	if (false !== $limit_transient && false === $error) {
		delete_transient('cryptowoo_limit_rates');
	}
	return false !== $error ? $error : $result;
}


/**
 * Override genesis block
 *
 * @param $genesis
 * @param $field_id
 *
 * @return string
 */
function cwvtc_validate_custom_api_genesis( $genesis, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_vtc', 'processing_fallback_url_vtc' ) ) ) {
		$genesis = '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f';
		//$genesis  = '00000000839a8e6886ab5951d76f411475428afc90947ee320161bbf18eb6048'; // 1
	}

	return $genesis;
}


/**
 * Override custom API currency
 *
 * @param $currency
 * @param $field_id
 *
 * @return string
 */
function cwvtc_validate_custom_api_currency( $currency, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_vtc', 'processing_fallback_url_vtc' ) ) ) {
		$currency = 'VTC';
	}

	return $currency;
}


/**
 * Add currency to cryptowoo_is_ready
 *
 * @param $enabled
 * @param $options
 * @param $changed_values
 *
 * @return array
 */
function cwvtc_cryptowoo_is_ready( $enabled, $options, $changed_values ) {
	$enabled['VTC']           = (bool) CW_Validate::check_if_unset( 'cryptowoo_vtc_mpk', $options, false );
	$enabled['VTC_transient'] = (bool) CW_Validate::check_if_unset( 'cryptowoo_vtc_mpk', $changed_values, false );

	return $enabled;
}


/**
 * Add currency to is_cryptostore check
 *
 * @param $cryptostore
 * @param $woocommerce_currency
 *
 * @return bool
 */
function cwvtc_is_cryptostore( $cryptostore, $woocommerce_currency ) {
	return (bool) $cryptostore ?: $woocommerce_currency === 'VTC';
}

add_filter( 'is_cryptostore', 'cwvtc_is_cryptostore', 10, 2 );

/**
 * Add currency to Shifty button option field
 *
 * @param $select
 *
 * @return array
 */
function cwvtc_cw_get_shifty_coins( $select ) {
	$select['VTC'] = sprintf( __( 'Display only on %s payment pages', 'cryptowoo' ), 'Vertcoin' );

	return $select;
}


/**
 * Add HD index key id for currency
 *
 * @param $index_key_ids
 *
 * @return array
 */
function cwvtc_index_key_ids( $index_key_ids ) {
	$index_key_ids['VTC'] = 'cryptowoo_vtc_index';

	return $index_key_ids;
}


/**
 * Add HD mpk key id for currency
 *
 * @param $mpk_key_ids
 *
 * @return array
 */
function cwvtc_mpk_key_ids( $mpk_key_ids ) {
	$mpk_key_ids['VTC'] = 'cryptowoo_vtc_mpk';

	return $mpk_key_ids;
}


/**
 * Override mpk_key
 *
 * @param $mpk_key
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwvtc_get_mpk_data_mpk_key( $mpk_key, $currency, $options ) {
	if ( $currency === 'VTC' ) {
		$mpk_key = "cryptowoo_vtc_mpk";
	}

	return $mpk_key;
}


/**
 * Override mpk_data->network
 *
 * @param $mpk_data
 * @param $currency
 * @param $options
 *
 * @return object
 * @throws Exception
 */
function cwvtc_get_mpk_data_network( $mpk_data, $currency, $options ) {
	if ( $currency === 'VTC' ) {
		$mpk_data->network = BitWasp\Bitcoin\Network\NetworkFactory::create( '47', '05', '80' )->setHDPubByte('0488b21e')->setHDPrivByte('0488ade4')->setNetMagicBytes('fabfb5da');
	}

	return $mpk_data;
}

/**
 * Add currency force exchange rate update button
 *
 * @param $results
 *
 * @return array
 */
function cwvtc_force_update_exchange_rates( $results ) {
	$results['vtc'] = CW_ExchangeRates::update_altcoin_fiat_rates( 'VTC', false, true );

	return $results;
}

/**
 * Add currency to background exchange rate update
 *
 * @param $data
 * @param $options
 *
 * @return array
 */
function cwvtc_cron_update_exchange_data( $data, $options ) {
	$vtc = CW_ExchangeRates::update_altcoin_fiat_rates( 'VTC', $options );

	// Maybe log exchange rate updates
	if ( (bool) $options['logging']['rates'] ) {
		if ( $vtc['status'] === 'not updated' || strpos( $vtc['status'], 'disabled' ) ) {
			$data['vtc'] = strpos( $vtc['status'], 'disabled' ) ? $vtc['status'] : $vtc['last_update'];
		} else {
			$data['vtc'] = $vtc;
		}
	}

	return $data;
}

/**
 * Add currency to currencies array
 *
 * @param $currencies
 *
 * @return array
 */
function cwvtc_add_currency_to_array( $currencies ) {
	$currencies[] = 'VTC';

	return $currencies;
}


/**
 * Override currency params in xpub validation
 *
 * @param $currency_params
 * @param $field_id
 *
 * @return object
 */
function cwvtc_get_currency_params( $currency_params, $field_id ) {
	if ( strcmp( $field_id, 'cryptowoo_vtc_mpk' ) === 0 ) {
		$currency_params                     = new stdClass();
		$currency_params->strlen             = 111;
		$currency_params->mand_mpk_prefix    = 'xpub';   // bip32.org & Electrum prefix
		$currency_params->mand_base58_prefix = '0488b21e'; // Vertcoin
		$currency_params->currency           = 'VTC';
		$currency_params->index_key          = 'cryptowoo_vtc_index';
	}

	return $currency_params;
}

/**
 * Add VTC addresses to sort unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwvtc_sort_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'VTC' ) === 0 ) {
		$top_n[3]['VTC'][] = $address;
	}

	return $top_n;
}

/**
 * Add VTC addresses to prioritize unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwvtc_prioritize_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'VTC' ) === 0 ) {
		$top_n[3][] = $address;
	}

	return $top_n;
}

/**
 * Add VTC addresses to address_batch
 *
 * @param array $address_batch
 * @param mixed $address
 *
 * @return array
 */
function cwvtc_filter_batch( $address_batch, $address ) {
	if ( strcmp( $address->payment_currency, 'VTC' ) === 0 ) {
		$address_batch['VTC'][] = $address->address;
	}

	return $address_batch;
}


/**
 * Fallback on failing API
 *
 * @param $api_config
 * @param $currency
 *
 * @return array
 */
function cwvtc_cw_get_tx_api_config( $api_config, $currency ) {
	// ToDo: add Blockcypher
	if ( $currency === 'VTC' ) {
		if ( $api_config->tx_update_api === 'vertcoin.info' ) {
			$api_config->tx_update_api   = 'insight';
			$api_config->skip_this_round = false;
		} else {
			$api_config->tx_update_api   = 'vertcoin.info';
			$api_config->skip_this_round = false;
		}
	}

	return $api_config;
}

/**
 * Override Insight API URL if no URL is found in the settings
 *
 * @param $insight
 * @param $endpoint
 * @param $currency
 * @param $options
 *
 * @return mixed
 */
function cwvtc_override_insight_url( $insight, $endpoint, $currency, $options ) {
	if ( $currency === 'VTC' && isset( $options['processing_fallback_url_vtc'] ) && wp_http_validate_url( $options['processing_fallback_url_vtc'] ) ) {
		$fallback_url = $options['processing_fallback_url_vtc'];
		$urls         = $endpoint ? CW_Formatting::format_insight_api_url( $fallback_url, $endpoint ) : CW_Formatting::format_insight_api_url( $fallback_url, '' );
		$insight->url = $urls['surl'];
	}

	return $insight;
}

/**
 * Add Redux options
 */
function cwvtc_add_fields() {
	$woocommerce_currency = get_option( 'woocommerce_currency' );

	/*
	 * Required confirmations
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'cryptowoo_vtc_min_conf',
		'type'       => 'spinner',
		'title'      => sprintf( __( '%s Minimum Confirmations', 'cryptowoo' ), 'Vertcoin' ),
		'desc'       => sprintf( __( 'Minimum number of confirmations for <strong>%s</strong> transactions - %s Confirmation Threshold', 'cryptowoo' ), 'Vertcoin', 'Vertcoin' ),
		'default'    => 1,
		'min'        => 1,
		'step'       => 1,
		'max'        => 1,
	) );

	// ToDo: Enable raw zeroconf
	/*
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'vtc_raw_zeroconf',
		'type'       => 'switch',
		'title'      => __( 'Vertcoin "Raw" Zeroconf', 'cryptowoo' ),
		'subtitle'   => __( 'Accept unconfirmed Vertcoin transactions as soon as they are seen on the network.', 'cryptowoo' ),
		'desc'       => sprintf( __( '%sThis practice is generally not recommended. Only enable this if you know what you are doing!%s', 'cryptowoo' ), '<strong>', '</strong>' ),
		'default'    => false,
		'required'   => array(
			//array('processing_api_vtc', '=', 'custom'),
			array( 'cryptowoo_vtc_min_conf', '=', 0 )
		),
	) );
	*/


	/*
	 * ToDo: Zeroconf order amount threshold
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_max_unconfirmed_vtc',
		'type'       => 'slider',
		'title'      => sprintf( __( '%s zeroconf threshold (%s)', 'cryptowoo' ), 'Vertcoin', $woocommerce_currency ),
		'desc'       => '',
		'required'   => array( 'cryptowoo_vtc_min_conf', '<', 1 ),
		'default'    => 100,
		'min'        => 0,
		'step'       => 10,
		'max'        => 500,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_vtc_zconf_notice',
		'type'       => 'info',
		'style'      => 'info',
		'notice'     => false,
		'required'   => array( 'cryptowoo_vtc_min_conf', '>', 0 ),
		'icon'       => 'fa fa-info-circle',
		'title'      => sprintf( __( '%s Zeroconf Threshold Disabled', 'cryptowoo' ), 'Vertcoin' ),
		'desc'       => sprintf( __( 'This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo' ), 'Vertcoin' ),
	) );
	 */


	/*
	// Remove 3rd party confidence
	Redux::removeField( 'cryptowoo_payments', 'custom_api_confidence', false );

	/*
	 * Confidence warning
	 * /
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
			'id'    => 'vtc_confidence_warning',
			'type'  => 'info',
			'title' => __('Be careful!', 'cryptowoo'),
			'style' => 'warning',
			'desc'  => __('Accepting transactions with a low confidence value increases your exposure to double-spend attacks. Only proceed if you don\'t automatically deliver your products and know what you\'re doing.', 'cryptowoo'),
			'required' => array('min_confidence_vtc', '<', 95)
	));

	/*
	 * Transaction confidence
	 * /

	Redux::setField( 'cryptowoo_payments', array(
			'section_id'        => 'processing-confidence',
			'id'      => 'min_confidence_vtc',
			'type'    => 'switch',
			'title'   => sprintf(__('%s transaction confidence (%s)', 'cryptowoo'), 'Vertcoin', '%'),
			//'desc'    => '',
			'required' => array('cryptowoo_vtc_min_conf', '<', 1),

	));


	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confidence',
		'id'      => 'min_confidence_vtc_notice',
		'type'    => 'info',
		'style' => 'info',
		'notice'    => false,
		'required' => array('cryptowoo_vtc_min_conf', '>', 0),
		'icon'  => 'fa fa-info-circle',
		'title'   => sprintf(__('%s "Raw" Zeroconf Disabled', 'cryptowoo'), 'Vertcoin'),
		'desc'    => sprintf(__('This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo'), 'Vertcoin'),
	));

	// Re-add 3rd party confidence
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
		'id'       => 'custom_api_confidence',
		'type'     => 'switch',
		'title'    => __('Third Party Confidence Metrics', 'cryptowoo'),
		'subtitle' => __('Enable this to use the chain.so confidence metrics when accepting zeroconf transactions with your custom Bitcoin, Litecoin, or Dogecoin API.', 'cryptowoo'),
		'default'  => false,
	));
    */

	// Remove blockcypher token field
	Redux::removeField( 'cryptowoo_payments', 'blockcypher_token', false );

	/*
	 * Processing API
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'processing_api_vtc',
		'type'              => 'select',
		'title'             => sprintf( __( '%s Processing API', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'          => sprintf( __( 'Choose the API provider you want to use to look up %s payments.', 'cryptowoo' ), 'Vertcoin' ),
		'options'           => array(
			'vertcoin.info' => 'explorer.vertcoin.info',
			'custom'     => __( 'Custom (insight)', 'cryptowoo' ),
			'disabled'   => __( 'Disabled', 'cryptowoo' ),
		),
		'desc'              => '',
		'default'           => 'disabled',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_processing_api',
		'select2'           => array( 'allowClear' => false ),
	) );

	/*
	 * Processing API custom URL warning
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-api',
		'id'         => 'processing_api_vtc_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'processing_api_vtc', 'equals', 'custom' ),
			array( 'custom_api_vtc', 'equals', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s processing API', 'cryptowoo' ), 'Vertcoin' ),
	) );

	/*
	 * Custom processing API URL
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'custom_api_vtc',
		'type'              => 'text',
		'title'             => sprintf( __( '%s Insight API URL', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'          => sprintf( __( 'Connect to any %sInsight API%s instance.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%sexplorer.vertcoin.info/ext/getaddress/%sRoot URL: %explorer.vertcoin.info%s', 'cryptowoo-vtc-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'explorer.vertcoin.info',
		'required'          => array( 'processing_api_vtc', 'equals', 'custom' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid VTC Insight API URL', 'cryptowoo' ),
		'default'           => '',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );

	// Re-add blockcypher token field
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'blockcypher_token',
		'type'              => 'text',
		'ajax_save'         => false, // Force page load when this changes
		'desc'              => sprintf( __( '%sMore info%s', 'cryptowoo' ), '<a href="http://dev.blockcypher.com/#rate-limits-and-tokens" title="BlockCypher Docs: Rate limits and tokens" target="_blank">', '</a>' ),
		'title'             => __( 'BlockCypher Token (optional)', 'cryptowoo' ),
		'subtitle'          => sprintf( __( 'Use the API token from your %sBlockCypher%s account.', 'cryptowoo' ), '<strong><a href="https://accounts.blockcypher.com/" title="BlockCypher account vtcboard" target="_blank">', '</a></strong>' ),
		'validate_callback' => 'redux_validate_token'
	) );

	// API Resource control information
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api-resources',
		'id'                => 'processing_fallback_url_vtc',
		'type'              => 'text',
		'title'             => sprintf( __( 'Blockdozer Vertcoin API Fallback', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'          => sprintf( __( 'Fallback to any %sInsight API%s instance in case the Blockdozer API fails. Retry Blockdozer upon beginning of the next hour. Leave empty to disable.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%sexplorer.vertcoin.info/ext/getaddress/XtuVUju4Baaj7YXShQu4QbLLR7X2aw9Gc8%sRoot URL: %sexplorer.vertcoin.info%s', 'cryptowoo-vtc-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'explorer.vertcoin.info',
		'required'          => array( 'processing_api_vtc', 'equals', 'blockcypher' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid VTC Insight API URL', 'cryptowoo' ),
		'default'           => 'explorer.vertcoin.info',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );
	/*
	 * Preferred exchange rate provider
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rates-exchange',
		'id'                => 'preferred_exchange_vtc',
		'type'              => 'select',
		'title'             => 'Vertcoin Exchange (VTC/BTC)',
		'subtitle'          => sprintf( __( 'Choose the exchange you prefer to use to calculate the %sVertcoin to Bitcoin exchange rate%s', 'cryptowoo' ), '<strong>', '</strong>.' ),
		'desc'              => sprintf( __( 'Cross-calculated via BTC/%s', 'cryptowoo' ), $woocommerce_currency ),
		'options'           => array(
			'bittrex'    => 'Bittrex',
			'poloniex'   => 'Poloniex',
			'shapeshift' => 'ShapeShift'
		),
		'default'           => 'poloniex',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_exchange_api',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * Exchange rate multiplier
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'    => 'rates-multiplier',
		'id'            => 'multiplier_vtc',
		'type'          => 'slider',
		'title'         => sprintf( __( '%s exchange rate multiplier', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'      => sprintf( __( 'Extra multiplier to apply when calculating %s prices.', 'cryptowoo' ), 'Vertcoin' ),
		'desc'          => '',
		'default'       => 1,
		'min'           => .01,
		'step'          => .01,
		'max'           => 2,
		'resolution'    => 0.01,
		'validate'      => 'comma_numeric',
		'display_value' => 'text'
	) );

	/*
	 * Preferred blockexplorer
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_vtc',
		'type'       => 'select',
		'title'      => sprintf( __( '%s Block Explorer', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'   => sprintf( __( 'Choose the block explorer you want to use for links to the %s blockchain.', 'cryptowoo' ), 'Vertcoin' ),
		'desc'       => '',
		'options'    => array(
			'autoselect' => __( 'Autoselect by processing API', 'cryptowoo' ),
			'vertcoin.info' => 'explorer.vertcoin.info',
			'custom'     => __( 'Custom (enter URL below)' ),
		),
		'default'    => 'vertcoin.info',
		'select2'    => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_vtc_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'preferred_block_explorer_vtc', '=', 'custom' ),
			array( 'custom_block_explorer_vtc', '=', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s block explorer', 'cryptowoo' ), 'Vertcoin' ),
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rewriting',
		'id'                => 'custom_block_explorer_vtc',
		'type'              => 'text',
		'title'             => sprintf( __( 'Custom %s Block Explorer URL', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'          => __( 'Link to a block explorer of your choice.', 'cryptowoo' ),
		'desc'              => sprintf( __( 'The URL to the page that displays the information for a single address.%sPlease add %s{{ADDRESS}}%s as placeholder for the cryptocurrency address in the URL.%s', 'cryptowoo' ), '<br><strong>', '<code>', '</code>', '</strong>' ),
		'placeholder'       => 'explorer.vertcoin.info/ext/getaddress/{$address}',
		'required'          => array( 'preferred_block_explorer_vtc', '=', 'custom' ),
		'validate_callback' => 'redux_validate_custom_blockexplorer',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid custom block explorer URL', 'cryptowoo' ),
		'default'           => '',
	) );

	/*
	 * Currency Switcher plugin decimals
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting-switcher',
		'id'         => 'decimals_VTC',
		'type'       => 'select',
		'title'      => sprintf( __( '%s amount decimals', 'cryptowoo' ), 'Vertcoin' ),
		'subtitle'   => '',
		'desc'       => __( 'This option overrides the decimals option of the WooCommerce Currency Switcher plugin.', 'cryptowoo' ),
		'required'   => array( 'add_currencies_to_woocs', '=', true ),
		'options'    => array(
			2 => '2',
			4 => '4',
			6 => '6',
			8 => '8'
		),
		'default'    => 4,
		'select2'    => array( 'allowClear' => false )
	) );


	// Remove Bitcoin testnet
	Redux::removeSection( 'cryptowoo_payments', 'wallets-hdwallet-testnet', false );

	/*
	 * HD wallet section start
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-vtc',
		'type'       => 'section',
		'title'      => __( 'Vertcoin', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'cc-VTC',
		//'subtitle' => __('Use the field with the correct prefix of your Litecoin MPK. The prefix depends on the wallet client you used to generate the key.', 'cryptowoo-hd-wallet-addon'),
		'indent'     => true,
	) );

	/*
	 * Extended public key
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_vtc_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'title'             => sprintf( __( '%sprefix%s', 'cryptowoo-hd-wallet-addon' ), '<b>VTC "xpub..." ', '</b>' ),
		'desc'              => __( 'Vertcoin HD Wallet Extended Public Key (xpub...)', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		//'required' => array('cryptowoo_vtc_mpk', 'equals', ''),
		'placeholder'       => 'xpub...',
		// xpub format
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_vtc',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'Vertcoin' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * HD wallet section end
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

	// Re-add Bitcoin testnet section
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-testnet',
		'type'       => 'section',
		'title'      => __( 'TESTNET', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'fa fa-flask',
		'desc'       => __( 'Accept BTC testnet coins to addresses created via a "tpub..." extended public key. (testing purposes only!)<br><b>Depending on the position of the first unused address, it could take a while until your changes are saved.</b>', 'cryptowoo-hd-wallet-addon' ),
		'indent'     => true,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_btc_test_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'desc'              => __( 'Bitcoin TESTNET extended public key (tpub...)', 'cryptowoo-hd-wallet-addon' ),
		'title'             => __( 'Bitcoin TESTNET HD Wallet Extended Public Key', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		'placeholder'       => 'tpub...',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_btctest',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'BTCTEST' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

}

