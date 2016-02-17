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

class SC_Importer_Plugin {

    public function __construct()
    {
        add_action('admin_menu', array( $this, 'scimporter_config_page' ));
        add_action('admin_enqueue_scripts', array( $this, 'scimporter_enqueue_scripts' ));

        /* AJAX */
        add_action( 'wp_ajax_rebost_import', array( $this, 'sc_rebost_import' ));
        add_action( 'wp_ajax_nopriv_rebost_import', array( $this, 'sc_rebost_import' ));

        if ( !class_exists( 'SC_Importer' ) ) {
            require_once dirname(__FILE__) . '/lib/importer.php';
        }
    }

    function scimporter_config_page() {
        global $wpdb;
        register_setting( 'scimporter-group', 'scimporter_collapse' );
        register_setting( 'scimporter-group', 'scimporter_single' );
        register_setting( 'scimporter-group', 'scimporter_style' );

        if ( function_exists('add_submenu_page') )
            add_submenu_page('options-general.php',
                __('SC Importer', SCIMPORTER_TEXTDOMAIN),
                __('SC Importer', SCIMPORTER_TEXTDOMAIN),
                'manage_options', __FILE__, array ( $this, 'scimporter_dash_page' ));
    }

    /**
     * Adds the corresponding JS and CSS file for the extension
     */
    function scimporter_enqueue_scripts() {
        wp_enqueue_script( 'sc-js-importer', plugins_url( 'resources/js/sc_importer.js', __FILE__ ), array('jquery'), '1.0.0', true );
        wp_enqueue_style( 'sc-css-importer', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', array(), '1.0' );

        wp_localize_script( 'sc-js-importer', 'scajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
    }

    /**
     * Renders the dashboard page
     */
    function scimporter_dash_page() {
        $admin_template = dirname(__FILE__) . '/templates/admin/sc-importer.twig';
        $section_html_content = Timber::fetch( $admin_template );
        echo $section_html_content;
    }

    /**
     * Starts the import from line $i to line $j
     */
    function sc_rebost_import()
    {
        $i = intval(sanitize_text_field( $_POST["i_value"] ));
        $j = intval(sanitize_text_field( $_POST["j_value"] ));
        $step = intval(sanitize_text_field( $_POST["step"] ));

        $importer = new SC_Importer();
        $result = $importer->run( $i, $j, $step );
        echo json_encode($result);
        die();
    }
}

new SC_Importer_Plugin();