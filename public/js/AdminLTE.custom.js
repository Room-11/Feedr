/**
 * Setup custom radio buttons and check boxes
 */
(function($) {
    'use strict';

    $('input[type="checkbox"].flat-blue, input[type="radio"].flat-blue').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });
}(jQuery));

/**
 * Fold / expand password field when changing feed visibility
 */
(function($) {
    'use strict';

    $('.feed-form [name="visibility"]').on('ifChecked', function() {
        var $this          = $(this);
        var $visibilityRow = $this.closest('.row').next();

        if ($this.val() === 'public') {
            $visibilityRow.slideUp();
        } else {
            $visibilityRow.slideDown();
        }
    });
}(jQuery));

/**
 * Setup confirm
 */
(function($) {
    'use strict';

    $(document).on('click', '.confirm', function(e) {
        var $this = $(this);
        var $icon = $this.find('i');

        e.preventDefault();

        var $confirmButton = $this
            .clone()
            .removeClass('btn-success btn-info btn-primary btn-warning btn-danger confirm')
            .addClass('btn-success confirm-ok')
        ;

        $confirmButton.find('i').addClass('fa-check').removeClass('fa-trash-o')

        $this.before($confirmButton);
        $icon.addClass('fa-remove').removeClass('fa-trash-o');
        $this.addClass('confirm-cancel').removeClass('confirm');
    });

    $(document).on('click', '.confirm-cancel', function(e) {
        var $this = $(this);
        var $icon = $this.find('i');

        e.preventDefault();

        $this.prev().remove();

        $icon.removeClass('fa-remove').addClass('fa-trash-o');
        $this.removeClass('confirm-cancel').addClass('confirm');
    });
}(jQuery));
