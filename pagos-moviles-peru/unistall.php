<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit();
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS wp_pago_yape_virutec" );
$wpdb->query("DROP TABLE IF EXISTS wp_pago_yape" );
delete_option("yape_a1tiendas_version");
