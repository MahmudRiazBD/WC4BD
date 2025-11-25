(function($) {
    'use strict';

    $(document).ready(function() {
        // Add the 'Print Invoices' option to the bulk actions dropdowns
        var optionHtml = '<option value="print_wc4bd_invoices">Print Invoices (WC4BD)</option>';
        $('select[name="action"], select[name="action2"]').append(optionHtml);
    });

})(jQuery);