jQuery(document).ready(function($) {
    // --- Bulk Print Buttons Logic ---
    var $bulkActions = $('.bulkactions');
    
    if ($bulkActions.length > 0 && typeof wc4bd_admin_params !== 'undefined') {
        var invoiceBtnHtml = '<a href="#" id="wc4bd-bulk-print-invoices" class="button" style="margin-left: 5px; display: none;">' + wc4bd_admin_params.i18n_bulk_invoice + '</a>';
        var stickerBtnHtml = '<a href="#" id="wc4bd-bulk-print-stickers" class="button" style="margin-left: 5px; display: none;">' + wc4bd_admin_params.i18n_bulk_sticker + '</a>';
        
        $bulkActions.append(invoiceBtnHtml).append(stickerBtnHtml);
        
        var $invoiceBtn = $('#wc4bd-bulk-print-invoices');
        var $stickerBtn = $('#wc4bd-bulk-print-stickers');
        
        function toggleButtonsVisibility() {
            var checkedCount = $('input[name="post[]"]:checked, input[name="id[]"]:checked').length;
            $invoiceBtn.toggle(checkedCount > 0);
            $stickerBtn.toggle(checkedCount > 0);
        }
        
        toggleButtonsVisibility();
        $(document).on('change', 'th.check-column input[type="checkbox"], td.check-column input[type="checkbox"]', toggleButtonsVisibility);
        
        function handleBulkPrint(e, type) {
            e.preventDefault();
            var order_ids = [];
            $('input[name="post[]"]:checked, input[name="id[]"]:checked').each(function () {
                order_ids.push($(this).val());
            });
            if (order_ids.length > 0) {
                var url = wc4bd_admin_params.home_url + "?print_wc4bd_" + type + "=true&_wpnonce=" + wc4bd_admin_params.nonce + "&order_ids=" + order_ids.join(',');
                window.open(url, '_blank');
            }
        }
        
        $(document).on('click', '#wc4bd-bulk-print-invoices', function (e) { handleBulkPrint(e, 'invoices'); });
        $(document).on('click', '#wc4bd-bulk-print-stickers', function (e) { handleBulkPrint(e, 'stickers'); });
    }

    // --- Media Uploader Logic ---
    $("#wc4bd-upload-btn").click(function(e) {
        e.preventDefault();
        var image_frame;
        if(image_frame){
            image_frame.open();
        }
        // Define image_frame as wp.media object
        image_frame = wp.media({
            title: 'Upload Logo',
            multiple: false,
            library: {
                type: 'image',
            }
        });

        image_frame.on('close',function() {
            // On close, get selections and save to the hidden input
            // ! has selection
            var selection =  image_frame.state().get('selection');
            var gallery_length = selection.length;
            var gallery_ids = '';
            var gallery_images = '';
            if( gallery_length > 0 ) {
                var attachment = selection.first().toJSON();
                $("#wc4bd_business_logo").val(attachment.url);
            }
        });

        image_frame.open();
    });
});