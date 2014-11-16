(function($) {
    $('[data-action="repoSearch"]').on('click', function(e) {
        var button    = $(this);
        var repoValue = button.closest('.input-group').children('input').val();
        var result    = button.closest('.modal-body').children('table');
        var icon      = button.find('i').clone();

        if (!repoValue) {
            return;
        }

        button.find('i').remove();
        button.append('<img src="/images/ajax-loader.gif" alt="">');
        button.blur();

        result.hide();
        result.find('tbody tr').remove();

        var rows = ['odd', 'even'];

        $.get('/search-repository', {
            repo: repoValue
        }, function(repos) {
            for (var i = 0, l = repos.length; i < l; i++) {
                result.append('<tr class="' + result[i%2] + '"><td><input type="checkbox" data-id="' + repos[i]['id'] + '" data-username="' + repos[i]['owner']['login'] + '" data-name="' + repos[i]['name'] + '" data-url="' + repos[i]['html_url'] + '" data-fullname="' + repos[i]['full_name'] + '"></td><td><a href="' + repos[i]['html_url'] + '" target="_blank">' + repos[i]['full_name'] + '</a></td></tr>');
            }

            button.find('img').remove();
            button.append(icon);

            result.show();
        }, 'json');
    });

    $('[data-action="modalAddRepo"]').on('click', function(e) {
        var selectedItems = $(this).closest('.modal-dialog').find('input[type="checkbox"]:checked');
        var tbody = $(this).closest('.panel-heading').next().find('table tbody');

        var rows = ['odd', 'even'];

        selectedItems.each(function() {
            var row = '';

            var count = tbody.find('tr').length;

            row += '<tr class="' + rows[count%2] + '" data-id="' + $(this).data('id') + '" data-fullname="' + $(this).data('fullname') + '">';
            row += '    <td>' + $(this).data('username') + '</td>';
            row += '    <td>' + $(this).data('name') + '</td>';
            row += '    <td><a href="' + $(this).data('url') + '" target="_blank">' + $(this).data('url') + '</a></td>';
            row += '    <td>';
            row += '        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#deleteRepo' + $(this).data('id') + '">Delete</button>';
            row += '        <div class="modal fade" id="deleteRepo' + $(this).data('id') + '" tabindex="-1" role="dialog" aria-labelledby="deleteRepo' + $(this).data('id') + 'Label" aria-hidden="true" style="display: none;">';
            row += '            <div class="modal-dialog">';
            row += '                <div class="modal-content">';
            row += '                    <div class="modal-header">';
            row += '                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
            row += '                        <h4 class="modal-title">Delete repository</h4>';
            row += '                    </div>';
            row += '                    <div class="modal-body">';
            row += '                        Are you sure you want to delete the repository (' + $(this).data('username') + '/' + $(this).data('name') + ') from the feed?';
            row += '                    </div>';
            row += '                    <div class="modal-footer">';
            row += '                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
            row += '                        <button type="button" class="btn btn-primary" data-action="deleteRepo" data-id="' + $(this).data('id') + '">Delete</button>';
            row += '                    </div>';
            row += '                </div>';
            row += '            </div>';
            row += '        </div>';
            row += '    </td>';
            row += '</tr>';

            tbody.append(row);
        });

        $('#addRepo').modal('hide');

        updatePreview();
    });

    $('#addRepo').on('hidden.bs.modal', function () {
        var modal = $(this);

        modal.find('table tbody tr').remove();
        modal.find('table').hide();
        modal.find('[name="repo"]').val('');
    });

    $('body').on('click', '[data-action="deleteRepo"]', function(e) {
        $(this).closest('.panel-body').find('table tbody tr[data-id="' + $(this).data('id') + '"]').remove();

        $('#deleteRepo' + $(this).data('id')).modal('hide');

        updatePreview();
    });

    function updatePreview() {
        $('.panel-body .chat.preview .empty, .panel-body .chat.preview .new-item').fadeOut('fast');
        $('.panel-body .chat.preview .empty, .panel-body .chat.preview .new-item').promise().done(function(){
            $('.panel-body .chat.preview .loader').fadeIn('fast', function() {
                $('.panel-body .chat.preview .new-item').remove();

                var ids = [];

                $('#repos-table tbody tr').each(function() {
                    ids.push($(this).data('fullname'));
                });

                $('input[name="repos"]').val(JSON.stringify(ids));

                if (!ids.length) {
                    $('.panel-body .chat.preview .loader').fadeOut('fast', function() {
                        $('.panel-body .chat.preview .empty').fadeIn('fast');
                    });
                } else {
                    $.get('/get-releases', {
                        ids: ids
                    }, function(releases) {
                        var previewPanel = $('.panel-body .chat.preview');

                        var alignment = [
                            {
                                li: 'left',
                                avatar: 'pull-left',
                                timestamp: 'pull-right'
                            },
                            {
                                li: 'right',
                                avatar: 'pull-right',
                                timestamp: 'pull-left'
                            }
                        ];

                        var i = 1;

                        for (var timestamp in releases) {
                            if (!releases.hasOwnProperty(timestamp)) continue;

                            var html = '';

                            html += '<li class="' + alignment[i%2]['li'] + ' clearfix new-item">';
                            html += '    <span class="chat-img ' + alignment[i%2]['avatar'] + '">';
                            html += '        <img src="' + releases[timestamp]['avatar'] + '" alt="GitHub Rlease" class="img-circle">';
                            html += '    </span>';
                            html += '    <div class="chat-body clearfix">';
                            html += '        <div class="header">';
                            html += '            <a href="' + releases[timestamp]['url'] + '" target="_blank"><strong class="primary-font">' + releases[timestamp]['name'] + ' ' + releases[timestamp]['version'] + '</strong></a>';
                            html += '            <small class="' + alignment[i%2]['timestamp'] + ' text-muted">';
                            html += '                <i class="fa fa-clock-o fa-fw"></i> ' + releases[timestamp]['timestamp'] + '</small>';
                            html += '        </div>';
                            html += '        <p>';
                            html += '            ' + releases[timestamp]['description'];
                            html += '        </p>';
                            html += '    </div>';
                            html += '</li>';

                            previewPanel.prepend(html);

                            i++;
                        }

                        $('.panel-body .chat.preview .loader').fadeOut('fast', function() {
                            $('.panel-body .chat.preview .new-item').fadeIn('fast');
                        });
                    }, 'json');
                }
            });
        });
    }

    $('[data-action="userSearch"]').on('click', function(e) {
        var button    = $(this);
        var userValue = button.closest('.input-group').children('input').val();
        var result    = button.closest('.modal-body').children('table');
        var icon      = button.find('i').clone();

        if (!userValue) {
            return;
        }

        button.find('i').remove();
        button.append('<img src="/images/ajax-loader.gif" alt="">');
        button.blur();

        result.hide();
        result.find('tbody tr').remove();

        var rows = ['odd', 'even'];

        $.get('/search-user', {
            user: userValue
        }, function(users) {
            for (var i = 0, l = users['items'].length; i < l; i++) {
                result.append('<tr class="' + result[i%2] + '"><td><input type="checkbox" data-id="' + users['items'][i]['id'] + '" data-username="' + users['items'][i]['login'] + '" data-url="' + users['items'][i]['html_url'] + '" data-avatar="' + users['items'][i]['avatar_url'] + '"></td><td><img src="' + users['items'][i]['avatar_url'] + '" class="img-circle"></td><td><a href="' + users['items'][i]['html_url'] + '" target="_blank">' + users['items'][i]['login'] + '</a></td></tr>');
            }

            button.find('img').remove();
            button.append(icon);

            result.show();
        }, 'json');
    });

    $('[data-action="modalAddAdmin"]').on('click', function(e) {
        var selectedItems = $(this).closest('.modal-dialog').find('input[type="checkbox"]:checked');
        var tbody = $(this).closest('.panel-heading').next().find('table tbody');

        var rows = ['odd', 'even'];

        selectedItems.each(function() {
            var row = '';

            var count = tbody.find('tr').length;

            row += '<tr class="' + rows[count%2] + '" data-id="' + $(this).data('id') + '" data-username="' + $(this).data('username') + '">';
            row += '    <td><img src="' + $(this).data('avatar') + '" class="img-circle"></td>';
            row += '    <td>' + $(this).data('username') + '</td>';
            row += '    <td><a href="' + $(this).data('url') + '" target="_blank">' + $(this).data('url') + '</a></td>';
            row += '    <td>';
            row += '        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#deleteAdmin' + $(this).data('id') + '">Delete</button>';
            row += '        <div class="modal fade" id="deleteAdmin' + $(this).data('id') + '" tabindex="-1" role="dialog" aria-labelledby="deleteAdmin' + $(this).data('id') + 'Label" aria-hidden="true" style="display: none;">';
            row += '            <div class="modal-dialog">';
            row += '                <div class="modal-content">';
            row += '                    <div class="modal-header">';
            row += '                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
            row += '                        <h4 class="modal-title">Delete administrator</h4>';
            row += '                    </div>';
            row += '                    <div class="modal-body">';
            row += '                        Are you sure you want to remove the administrator (' + $(this).data('username') + ') from the feed?';
            row += '                    </div>';
            row += '                    <div class="modal-footer">';
            row += '                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
            row += '                        <button type="button" class="btn btn-primary" data-action="deleteAdmin" data-id="' + $(this).data('id') + '">Delete</button>';
            row += '                    </div>';
            row += '                </div>';
            row += '            </div>';
            row += '        </div>';
            row += '    </td>';
            row += '</tr>';

            tbody.append(row);
        });

        $('#addAdmin').modal('hide');
    });

    $('#addAdmin').on('hidden.bs.modal', function () {
        var modal = $(this);

        modal.find('table tbody tr').remove();
        modal.find('table').hide();
        modal.find('[name="user"]').val('');

        updateAdmins();
    });

    $('body').on('click', '[data-action="deleteAdmin"]', function(e) {
        $(this).closest('.panel-body').find('table tbody tr[data-id="' + $(this).data('id') + '"]').remove();

        $('#deleteAdmin' + $(this).data('id')).modal('hide');

        updateAdmins();
    });

    function updateAdmins() {
        var ids = [];

        $('#admins-table tbody tr').each(function() {
            ids.push({
                id: $(this).data('id'),
                username: $(this).data('username')
            });
        });

        $('input[name="admins"]').val(JSON.stringify(ids));
    }
}(jQuery));
