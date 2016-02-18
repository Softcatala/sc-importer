/** JS functions related to pages from the post_type 'Programa' **/

jQuery( document ).ready(function() {

    var $import_rebost_form = jQuery('#import_rebost_form');

    $import_rebost_form.on('submit', function (ev) {
        ev.preventDefault();

        jQuery("#loading").fadeIn();
        var nom_programa = jQuery("#nom_programa").val();
        jQuery("#submit").html('S\'està important... <i class="fa fa-spinner fa-pulse"></i>');

        var step = jQuery("#step").val();
        jQuery("#result_import").html('');
        execute_import_step( 1, step, step );

    });
});

jQuery( document ).ready(function() {

    var $import_rebost_form = jQuery('#import_wordpress_ids_form');

    $import_rebost_form.on('submit', function (ev) {
        ev.preventDefault();

        jQuery("#loading").fadeIn();
        jQuery("#submit_ids").html('S\'està important... <i class="fa fa-spinner fa-pulse"></i>');

        jQuery("#result_import").html('');
        execute_wordpress_ids_import();

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
        jQuery("#submit").html('Inicia la importació');
    }
}

function form_import_ko() {
    text = '<div style="border: 1px solid #dddddd; padding: 10px;">S\'ha produït un error en la importació</div>';
    jQuery("#result_import").html();
    jQuery("#submit").val('Inicia la importació');
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