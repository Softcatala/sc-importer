/** JS functions related to pages from the post_type 'Programa' **/

jQuery( document ).ready(function() {

    //Importació de programes del rebost
    var $import_rebost_form = jQuery('#import_rebost_form');

    $import_rebost_form.on('submit', function (ev) {
        ev.preventDefault();

        var nom_programa = jQuery("#nom_programa").val();
        jQuery("#submit").html('S\'està important... <i class="fa fa-spinner fa-pulse"></i>');

        var step = jQuery("#step").val();
        jQuery("#result_import").html('');
        execute_import_step(1, step, step);
    });

    //Importació de les ids del rebost
    var $import_rebost_form = jQuery('#import_wordpress_ids_form');

    $import_rebost_form.on('submit', function (ev) {
        ev.preventDefault();

        jQuery("#submit_ids").html('S\'està important... <i class="fa fa-spinner fa-pulse"></i>');

        jQuery("#result_import").html('');
        execute_wordpress_ids_import();

    });

    //Importació de taxonomies
    var $import_rebost_form = jQuery('#import_taxonomies_form');

    $import_rebost_form.on('submit', function (ev) {
        ev.preventDefault();

        jQuery("#submit_cat").html('S\'està important... <i class="fa fa-spinner fa-pulse"></i>');

        jQuery("#result_import").html('');
        execute_import('taxonomies_import');
    });

    //Importació de projectes
    var $import_form = jQuery('#import_projectes_form');

    $import_form.on('submit', function (ev) {
        ev.preventDefault();

        jQuery("#submit_pr").html('S\'està important... <i class="fa fa-spinner fa-pulse"></i>');

        jQuery("#result_import").html('');
        execute_import('projectes_import');

    });
});

function form_import_ok(dt) {
    jQuery("#result_import").show();
    var div_import = jQuery("#result_import").html();
    var final_import = div_import + dt.text + '<br/>Processats: ' + dt.j_value + '<br/>';
    jQuery("#result_import").html(final_import);

    if ( ! dt.end_of_file ) {
        execute_import_step( dt.i_value, dt.j_value, dt.step );
    } else {
        var div_import = jQuery("#result_import").html();
        var final_import = 'S\'ha completat la importació.<br/><br/>' + div_import + dt.text + '<br/>Processats: ' + dt.j_value + '<br/>';
        jQuery("#result_import").html(final_import);
        restart_buttons_text();
    }
}

function form_import_ko() {
    text = '<div style="border: 1px solid #dddddd; padding: 10px;">S\'ha produït un error en la importació</div>';
    jQuery("#result_import").html();
    restart_buttons_text();
    alert('Alguna cosa no ha funcionat bé');
}

function execute_import_step ( i_value, j_value, step ) {
    //Data
    var post_data = new FormData();
    post_data.append('i_value', i_value);
    post_data.append('j_value', j_value);
    post_data.append('step', step);
    post_data.append('action', 'rebost_import');

    jQuery.ajax({
        type: 'POST',
        url: scajax.ajax_url,
        data: post_data,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: form_import_ok,
        error: form_import_ko
    });
}

function execute_wordpress_ids_import() {
    var post_data = new FormData();
    post_data.append('action', 'wordpressids_import');

    jQuery.ajax({
        type: 'POST',
        url: scajax.ajax_url,
        data: post_data,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: form_import_ok,
        error: form_import_ko
    });
}

function execute_import(action) {
    var post_data = new FormData();
    post_data.append('action', action);

    jQuery.ajax({
        type: 'POST',
        url: scajax.ajax_url,
        data: post_data,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: import_ok,
        error: import_ko
    });
}

function import_ok(dt) {
    jQuery("#result_import").show();
    jQuery("#result_import").html(dt.text);
    restart_buttons_text();
}

function import_ko(dt) {
    jQuery("#result_import").show();
    jQuery("#result_import").html('Alguna cosa no ha funcionat bé');
    restart_buttons_text();
}

function restart_buttons_text() {
    jQuery("#submit_cat").html('Inicia la importació de taxonomies');
    jQuery("#submit_pr").html('Inicia la importació de projectes');
    jQuery("#submit").html('Inicia la importació');
}