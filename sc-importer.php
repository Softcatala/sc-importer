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

    protected $link;
    const DB_User = 'rrebost';
    const DB_Pass = 'mypasswd';
    const DB_Name = 'rebost';

    public function __construct()
    {
        add_action('admin_menu', array( $this, 'scimporter_config_page' ));
        add_action('admin_enqueue_scripts', array( $this, 'scimporter_enqueue_scripts' ));

        /* AJAX */
        add_action( 'wp_ajax_rebost_import', array( $this, 'sc_rebost_import' ));
        add_action( 'wp_ajax_nopriv_rebost_import', array( $this, 'sc_rebost_import' ));
        add_action( 'wp_ajax_wordpressids_import', array( $this, 'sc_wordpressids_import' ));
        add_action( 'wp_ajax_nopriv_wordpressids_import', array( $this, 'sc_wordpressids_import' ));
        add_action( 'wp_ajax_taxonomies_import', array( $this, 'sc_taxonomies_import' ));
        add_action( 'wp_ajax_nopriv_taxonomies_import', array( $this, 'sc_taxonomies_import' ));

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

    /**
     * This function imports the wordpress ids into the rebost DB
     */
    function sc_wordpressids_import() {
        //Connect to the DB
        $this->link = mysqli_connect('localhost', self::DB_User, self::DB_Pass, self::DB_Name);

        //Create column if it doesn't exit
        $query = "SHOW COLUMNS FROM `baixades` LIKE 'wordpress_id';";
        $result = $this->do_the_query( $query );

        //Create table baixades_titles if it doesn't exist
        $this->create_table('baixades_titles');

        if ( ! $result ) {
            $query_create_column = "ALTER TABLE `baixades` ADD wordpress_id INT(8) after idrebost";
            $result = $this->do_the_query( $query_create_column, 'alter' );
            if ( ! $result ) {
                //error
            }
        }

        $query_idrebost = "SELECT idrebost FROM baixades GROUP BY idrebost";
        $result = $this->do_the_query( $query_idrebost );
        foreach ( $result as $idrebost ) {
            $rebost_id = $idrebost->idrebost;
            $programa = $this->get_the_wordpress_programa( $rebost_id );
            if ( $programa ) {
                $wordpress_id = $programa->ID;
                $programa_title = $programa->post_title;

                //Create the entry for program name if it doesn't exist
                $query_check_program_title = "SELECT wordpress_id FROM baixades_titles WHERE title = \"$programa_title\"";
                $result = $this->do_the_query( $query_check_program_title );
                if ( ! $result ) {
                    $query_insert_program_title = "INSERT INTO baixades_titles ( `idrebost`, `wordpress_id`, `title`)
                                              VALUES ( \"$rebost_id\", \"$wordpress_id\",
                                              CONVERT(CONVERT(\"$programa_title\" USING binary) USING utf8) )"
                    ;
                    $this->do_the_query( $query_insert_program_title, 'insert' );
                }
            } else {
                $wordpress_id = $programa;
            }

            $query_update_wordpress_id = "UPDATE baixades SET wordpress_id = \"$wordpress_id\" WHERE idrebost = \"$rebost_id\"";
            $this->do_the_query( $query_update_wordpress_id, 'update' );
        }
    }

    /**
     * This function imports the wordpress ids into the rebost DB
     */
    function sc_taxonomies_import() {
        $importer = new SC_Importer();
        $result = $importer->import_taxonomies();

        echo json_encode($result);
        die();
    }

    /**
     * This function executes the passed query
     *
     * @param string $query
     * @return object $result
     */
    public function do_the_query( $query, $type = 'select' )
    {
        $result = array();

        $query_result = $this->link->query($query);

        if ( $type != 'select') {
            $result = $query_result;
        } else {
            while ($row = $query_result->fetch_object()){
                $result[] = $row;
            }
        }


        return $result;
    }

    /**
     * This function retrieves the wordpress_id given a rebost_id value
     */
    function get_the_wordpress_programa( $rebost_id ) {
        $args = array(
            'post_type' => 'programa',
            'meta_query' => array(
                array(
                    'key' => 'wpcf-idrebost',
                    'value' => $rebost_id,
                    'compare' => '='
                )
            )
        );

        $programes = query_posts($args);
        if ( count ( $programes) > 0 ) {
            $programa = $programes[0];
        } else {
            $programa = 0;
        }

        return $programa;
    }

    /**
     * Creates the table baixades_titles if it doesn't exist
     */
    function create_table ($table_name) {
        //Table names creation
        $query_table = "SHOW TABLES LIKE '$table_name'";
        $result = $this->do_the_query( $query_table, 'show' );

        if ( ! $result->num_rows ) {
            $query_create_table = "CREATE TABLE $table_name (
                                  title_id INT NOT NULL AUTO_INCREMENT,
                                  idrebost INT NOT NULL,
                                  wordpress_id INT NOT NULL,
                                  title VARCHAR(255) NOT NULL,
                                  PRIMARY KEY ( title_id )
            )";

            $this->do_the_query( $query_create_table, 'create_table' );
        }
    }
}

new SC_Importer_Plugin();