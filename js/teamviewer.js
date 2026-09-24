var teamviewerConnectBase = 'teamviewer10://control?device=';

var teamviewerParseEdition = function(version) {
    var text = version == null ? '' : String(version).trim();
    if (!text) {
        return null;
    }
    var match = text.match(/^(\d+(?:\.\d+)*)\s*(.*)$/);
    var number = match ? match[1] : text;
    var code = match ? String(match[2] || '').trim().toUpperCase() : '';
    var key = '';
    if (code === 'HC' || code === 'CH') {
        key = 'edition_custom_host';
    } else if (code === 'H') {
        key = 'edition_host';
    } else if (code === 'CQS') {
        key = 'edition_custom_quicksupport';
    } else if (code === 'QS') {
        key = 'edition_quicksupport';
    }
    return {
        number: number,
        label: key ? i18n.t('teamviewer.' + key) : ''
    };
};

var formatTeamviewerClientId = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var clientId = col.text().trim();
    if (!clientId || clientId === '0') {
        return;
    }
    var connectLink = $('<a>', {
        'class': 'btn btn-info btn-xs',
        style: 'min-width: 80px; margin-right: 8px;',
        href: teamviewerConnectBase + encodeURIComponent(clientId),
        target: '_blank',
        rel: 'noopener noreferrer',
        text: i18n.t('teamviewer.connect'),
        'data-teamviewer-clientid': clientId
    });
    col.empty().append(connectLink).append(document.createTextNode(clientId));
};

var formatTeamviewerVersion = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var edition = teamviewerParseEdition(col.text());
    if (edition) {
        col.text(edition.number);
    }
};

var formatTeamviewerEdition = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var edition = teamviewerParseEdition(col.text());
    col.text(edition && edition.label ? edition.label : '');
};

var formatTeamviewerUpdateAvailable = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text().trim();
    if (value === '1') {
        col.html('<span class="label label-danger">' + i18n.t('yes') + '</span>');
    } else if (value === '0') {
        col.html('<span class="label label-success">' + i18n.t('no') + '</span>');
    }
};

var formatTeamviewerUpdateVersion = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text();
    if (!value) {
        return;
    }
    col.html($('<div>').text(value).html().replace(/\r\n|\r|\n/g, '<br>'));
};

var formatTeamviewerManagement = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text().trim();
    // Missing / unreported treated as Unmanaged (only explicit 0 is Managed)
    if (value === '0') {
        col.html('<span class="label label-success">' + i18n.t('teamviewer.managed_state') + '</span>');
    } else {
        col.html('<span class="label label-warning">' + i18n.t('teamviewer.unmanaged_state') + '</span>');
    }
};

var formatTeamviewerInterface = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var uiVersionNum = col.text().trim();
    uiVersionNum = uiVersionNum === '' ? null : parseInt(uiVersionNum, 10);
    var interfaceText = '';
    var interfaceClass = '';
    // TeamViewer UIVersion: 4 = New, 2 = Classic
    if (uiVersionNum >= 4) {
        interfaceText = i18n.t('teamviewer.interface_new');
        interfaceClass = 'label-info';
    } else if (uiVersionNum === 2 || uiVersionNum === 1) {
        interfaceText = i18n.t('teamviewer.interface_classic');
        interfaceClass = 'label-warning';
    }
    if (interfaceText) {
        col.html('<span class="label ' + interfaceClass + '">' + $('<div>').text(interfaceText).html() + '</span>');
    } else {
        col.text('');
    }
};

var formatTeamviewerAlwaysOnline = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text().trim();
    // Missing / unreported treated as No (only explicit 1 is Yes)
    if (value === '1') {
        col.html('<span class="label label-warning">' + i18n.t('yes') + '</span>');
    } else {
        col.html('<span class="label label-success">' + i18n.t('no') + '</span>');
    }
};

var formatTeamviewerAdminRights = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text().trim();
    if (value === '1') {
        col.html('<span class="label label-success">' + i18n.t('yes') + '</span>');
    } else if (value === '0') {
        col.html('<span class="label label-danger">' + i18n.t('no') + '</span>');
    }
};

var formatTeamviewerPasswordStrength = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text().trim();
    if (value === '') {
        return;
    }
    var n = parseInt(value, 10);
    var key = '';
    var cls = '';
    if (n === 0) {
        key = 'passwordstrength_disabled';
        cls = 'label-danger';
    } else if (n === 1) {
        key = 'passwordstrength_low';
        cls = 'label-danger';
    } else if (n === 2) {
        key = 'passwordstrength_medium';
        cls = 'label-warning';
    } else if (n === 3) {
        key = 'passwordstrength_high';
        cls = 'label-success';
    } else {
        col.empty();
        return;
    }
    col.html('<span class="label ' + cls + '">' + i18n.t('teamviewer.' + key) + '</span>');
};

var formatTeamviewerCommercial = function(colNumber, row) {
    var col = $('td:eq(' + colNumber + ')', row);
    var value = col.text().trim();
    if (value === '1') {
        col.html('<span class="label label-success">' + i18n.t('yes') + '</span>');
    } else if (value === '0') {
        col.html('<span class="label label-info">' + i18n.t('no') + '</span>');
    }
};

var teamviewer_update_available_filter = function(colNumber, d) {
    if (d.search.value === 'teamviewer_update_yes') {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    if (d.search.value === 'teamviewer_update_no') {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
};

var teamviewer_management_filter = function(colNumber, d) {
    if (d.search.value === 'teamviewer_unmanaged') {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    if (d.search.value === 'teamviewer_managed') {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
};

var teamviewer_always_online_filter = function(colNumber, d) {
    if (d.search.value === 'teamviewer_always_online_yes') {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    if (d.search.value === 'teamviewer_always_online_no') {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
};

var teamviewer_password_strength_filter = function(colNumber, d) {
    if (d.search.value === 'teamviewer_pwd_disabled') {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
    if (d.search.value === 'teamviewer_pwd_low') {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    if (d.search.value === 'teamviewer_pwd_medium') {
        d.columns[colNumber].search.value = '= 2';
        d.search.value = '';
    }
    if (d.search.value === 'teamviewer_pwd_high') {
        d.columns[colNumber].search.value = '= 3';
        d.search.value = '';
    }
};

$(document).on('appReady', function() {
    var tipMap = {
        'teamviewer.interface': 'teamviewer.interface_tip',
        'teamviewer.edition': 'teamviewer.edition_tip',
        'teamviewer.security_passwordstrength': 'teamviewer.security_passwordstrength_tip'
    };
    $.each(tipMap, function(headerKey, tipKey) {
        $('.table th[data-i18n="' + headerKey + '"]')
            .attr('title', i18n.t(tipKey))
            .attr('data-toggle', 'tooltip')
            .attr('data-placement', 'top')
            .tooltip({ placement: 'top', container: 'body' });
    });

    $.getJSON(appUrl + '/module/teamviewer/get_connect_base', function(data) {
        if (data && data.link) {
            teamviewerConnectBase = data.link;
            $('a[data-teamviewer-clientid]').each(function() {
                var clientId = $(this).attr('data-teamviewer-clientid');
                $(this).attr('href', teamviewerConnectBase + encodeURIComponent(clientId));
            });
        }
    });

    setTimeout(function() {
        var oTable = $('.table').DataTable();
        if (oTable && window.location.hash.substring(1)) {
            var hash = decodeURIComponent(window.location.hash.substring(1));
            oTable.fnFilter(hash);
        }
    }, 200);
});
