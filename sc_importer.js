/** JS functions related to pages from the post_type 'Programa' **/

jQuery( document ).ready(function() {

    var $import_rebost_form = jQuery('#import_rebost_form');

    $import_rebost_form.on('submit', function (ev) {
        ev.preventDefault();

        jQuery("#loading").fadeIn();
        var nom_programa = jQuery("#nom_programa").val();

        execute_import_step( 1, 2 );

    });
});

function form_import_ok(dt) {
    var div_import = jQuery("#result_import").html();
    var final_import = div_import + '<br/>' + dt.text;
    jQuery("#result_import").html(final_import);

    if ( dt.i_value < 350 ) {
        execute_import_step( dt.i_value + 1, dt.j_value + 1 );
    }
}

function form_import_ko() {
    alert('Alguna cosa no ha funcionat bé');
}

function execute_import_step ( i_value, j_value ) {
    //Data
    var post_data = new FormData();
    post_data.append('i_value', i_value);
    post_data.append('j_value', j_value);
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