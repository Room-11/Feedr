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

/**
 * Setup repo search
 */
(function($) {
    'use strict';

    var repositories       = [];
    var $repositoriesField = $('[name="repositories"]');
    var $counter           = $('.repo-amount');

    if ($repositoriesField.length && $repositoriesField.val().length) {
        repositories = JSON.parse($repositoriesField.val());
    }

    var addRepository = function(id, name) {
        for (var i = 0; i < repositories.length; i++) {
            if (repositories[i].id === id) return;
        }

        repositories.push({
            id: id,
            name: name
        });

        updateField();
    };

    var deleteRepository = function(id) {
        for (var i = 0; i < repositories.length; i++) {
            if (repositories[i].id === id) {
                repositories.splice(i, 1);

                break;
            }
        }

        updateField();
    };

    var updateField = function() {
        $('[name="repositories"]').val(JSON.stringify(repositories));

        $counter.text(repositories.length);
        $counter[0].setAttribute('data-original-title', $counter.data('translation').replace('%d', repositories.length));
    };

    $(document).on('keypress', '[name="repo-search"]', function(e) {
        if (e.which !== 13) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        $('[data-action="repo-search"]').click();
    });

    $(document).on('click', '[data-action="repo-search"]', function() {
        var $this = $(this);
        var $icon = $this.find('i');

        $icon.addClass('fa-circle-o-notch fa-spin').removeClass('fa-plus');

        $.get('/repositories/search', {
            repo: $('[name="repo-search"]').val()
        }, function(modal) {
            $icon.removeClass('fa-circle-o-notch fa-spin').addClass('fa-plus');

            $('body').append(modal);

            $('#search-results').modal('show');
        });
    });

    $(document).on('hidden.bs.modal', '#search-results', function () {
        $('#search-results').remove();
    });

    $(document).on('click', '.add-repo', function() {
        var $this = $(this);
        var $icon = $this.find('i');

        $.get('/repositories/add', {
            id: $this.data('id'),
            repo: $this.data('repo')
        }, function(row) {
            $icon.addClass('fa-check').removeClass('fa-plus');

            $('.repo-table tbody').append(row);

            addRepository($this.data('id'), $this.data('repo'));
        });
    });

    $(document).on('click', 'tr.repository .confirm-ok', function() {
        var $this = $(this);
        var $row  = $this.closest('tr');

        deleteRepository($row.data('id'));

        $row.remove();
    });
}(jQuery));

/**
 * Setup admin search
 */
(function($) {
    'use strict';

    var administrators       = [];
    var $administratorsField = $('[name="administrators"]');
    var $counter             = $('.admin-amount');

    if ($administratorsField.length && $administratorsField.val().length) {
        administrators = JSON.parse($administratorsField.val());
    }

    var addAdministrator = function(id, name) {
        for (var i = 0; i < administrators.length; i++) {
            if (administrators[i].id === id) return;
        }

        administrators.push({
            id: id,
            name: name
        });

        updateField();
    };

    var deleteAdministrator = function(id) {
        for (var i = 0; i < administrators.length; i++) {
            if (administrators[i].id === id) {
                administrators.splice(i, 1);

                break;
            }
        }

        updateField();
    };

    var updateField = function() {
        $('[name="administrators"]').val(JSON.stringify(administrators));

        $counter.text(administrators.length);
        $counter[0].setAttribute('data-original-title', $counter.data('translation').replace('%d', administrators.length));
    };

    $(document).on('keypress', '[name="admin-search"]', function(e) {
        if (e.which !== 13) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        $('[data-action="admin-search"]').click();
    });

    $(document).on('click', '[data-action="admin-search"]', function() {
        var $this = $(this);
        var $icon = $this.find('i');

        $icon.addClass('fa-circle-o-notch fa-spin').removeClass('fa-plus');

        $.get('/administrators/search', {
            user: $('[name="admin-search"]').val()
        }, function(modal) {
            $icon.removeClass('fa-circle-o-notch fa-spin').addClass('fa-plus');

            $('body').append(modal);

            $('#search-results').modal('show');
        });
    });

    $(document).on('hidden.bs.modal', '#search-results', function () {
        $('#search-results').remove();
    });

    $(document).on('click', '.add-admin', function() {
        var $this = $(this);
        var $icon = $this.find('i');

        $.get('/administrators/add', {
            id: $this.data('id'),
            avatar: $this.data('avatar'),
            username: $this.data('username')
        }, function(row) {
            $icon.addClass('fa-check').removeClass('fa-plus');

            $('.admin-table tbody').append(row);

            addAdministrator($this.data('id'), $this.data('name'));
        });
    });

    $(document).on('click', 'tr.administrator .confirm-ok', function() {
        var $this = $(this);
        var $row  = $this.closest('tr');

        deleteAdministrator($row.data('id'));

        $row.remove();
    });
}(jQuery));
