// Global access to all functions and variables in name space
// @namespace sampleNamespace
if (typeof uapLogReport === 'undefined') {
    // the namespace is not defined
    var uapLogReport = {};
}

(function ($) { // Self Executing function with $ alias for jQuery
$(document).ready(function() {
    $('.uap-report-filters-filter select').select2({
        theme: 'default uap-logs-select2',
    });
    $('#recipe_id_filter').on('select2:select', function (e) {
        var data = e.params.data;
        //console.log(data.id);
        if($('#trigger_id_filter').length > 0){
            $.ajax({
                url:uapActivityLogApiSetup.ajax_url,
                data:{'action':'recipe-triggers','recipe_id':data.id,'ajax_nonce':uapActivityLogApiSetup.ajax_nonce},
                dataType:'json',
                type:'post',
                success:function(response){
                    
                    $('#trigger_id_filter').select2('destroy').empty();
                    
                    $("#trigger_id_filter").select2({
                        data: response
                    });
                }
            });
        }
        else if($('#action_id_filter').length > 0){
            $.ajax({
                url:uapActivityLogApiSetup.ajax_url,
                data:{'action':'recipe-actions','recipe_id':data.id,'ajax_nonce':uapActivityLogApiSetup.ajax_nonce},
                dataType:'json',
                type:'post',
                success:function(response){
                    
                    $('#action_id_filter').select2('destroy').empty();
                    
                    $("#action_id_filter").select2({
                        data: response
                    });
                   
                }
            });
        }
      });
        $('.daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('.daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('.daterange').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
        });

        cancelAsyncRun = ( event, action_log_id, action_id, recipe_log_id ) => {
            event.preventDefault();
            if ( ! confirm( "Are you sure you want to cancel this scheduled action? Once cancelled, it cannot be restored." ) ) {
                return;
            }

            $( event.target ).text( 'Please wait...' );
    
            var data = {
                'action': 'cancel_async_run',
                'action_id': action_id,
                'action_log_id': action_log_id,
                'recipe_log_id': recipe_log_id,
                'nonce': uapActivityLogApiSetup.ajax_nonce
            };
    
            $.post( ajaxurl, data, ( response ) => {
            
                response = JSON.parse( response );
    
                if ( response.success ) {
                    $( event.target ).parent().text( uapActivityLogApiSetup.i18n.action.asyncActions.cancelled );
                    location.reload();
                } else {
                    $( event.target ).parent().text( uapActivityLogApiSetup.i18n.action.asyncActions.error );
                }
                
            });
        }
});

    


})(jQuery);
