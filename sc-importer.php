<?php
/*
Plugin Name: SC-Importer
Plugin URI: https://github.com/Softcatala/
Description: SC Software Importer
Version: 0.0.1
Author: Pau Iranzo
Author URI: http://www.softcatala.org
*/
define('SCIMPORTER_TEXTDOMAIN', 'scimporter');

function scimporter_config_page() {
    global $wpdb;
    register_setting( 'scimporter-group', 'scimporter_collapse' );
    register_setting( 'scimporter-group', 'scimporter_single' );
    register_setting( 'scimporter-group', 'scimporter_style' );

    if ( function_exists('add_submenu_page') )
        add_submenu_page('options-general.php',
            __('SC Importer', SCIMPORTER_TEXTDOMAIN),
            __('SC Importer', SCIMPORTER_TEXTDOMAIN),
            'manage_options', __FILE__, 'scimporter_conf');
}

function scimporter_enqueue_scripts() {
    wp_enqueue_script( 'sc-js-importer', plugins_url( 'sc_importer.js', __FILE__ ), array('jquery'), '1.0.0', true );
    wp_localize_script( 'sc-js-importer', 'scajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}

function scimporter_conf() {
    $section_html_content = Timber::fetch('admin/sc-importer.twig' );
    echo $section_html_content;
}

add_action('admin_menu', 'scimporter_config_page');
add_action('admin_enqueue_scripts', 'scimporter_enqueue_scripts');

/* AJAX */
add_action( 'wp_ajax_rebost_import', 'sc_rebost_import' );
add_action( 'wp_ajax_nopriv_rebost_import', 'sc_rebost_import' );

function sc_rebost_import()
{
    $i = intval(sanitize_text_field( $_POST["i_value"] ));
    $j = intval(sanitize_text_field( $_POST["j_value"] ));
    $importer = new SC_Importer();
    $result = $importer->run( $i, $j );
    echo json_encode($result);
    die();
}