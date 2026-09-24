<?php
configAppendFile(__DIR__ . '/../config.php');
$teamviewer_link = conf('teamviewer_link', 'teamviewer10://control?device=');
if (!is_string($teamviewer_link) || !preg_match('#^[A-Za-z][A-Za-z0-9+.-]*://#', $teamviewer_link)) {
    $teamviewer_link = 'teamviewer10://control?device=';
}
?>
<div id="lister" style="font-size: large; float: right;">
    <a href="/show/listing/teamviewer/teamviewer" title="List">
        <i class="btn btn-default tab-btn fa fa-list-alt"></i>
    </a>
</div>
<div id="report_btn" style="font-size: large; float: right;">
    <a href="/show/report/teamviewer/teamviewer" title="Report">
        <i class="btn btn-default tab-btn fa fa-bar-chart-o"></i>
    </a>
</div>
<h2><i class="fa fa-laptop-code"></i> <span data-i18n="teamviewer.teamviewer"></span></h2>

<div id="teamviewer-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>
<div id="teamviewer-content"></div>

<script>
$(document).on('appReady', function(e, lang) {
	
	var connectBase = <?= json_encode($teamviewer_link, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

	// Get teamviewer data
	$.getJSON( appUrl + '/module/teamviewer/get_data/' + serialNumber, function( data ) {

        // Check if we have data
        if( data == "" || ! data){
            $('#teamviewer-msg').text(i18n.t('no_data'));
        } else {

            // Hide
            $('#teamviewer-msg').text('');
            $('#teamviewer-count-view').removeClass('hide');
            var esc = function(value) {
                return $('<div>').text(value == null ? '' : String(value)).html();
            };

            var skipThese = ['id','serial_number','midversion','moverestriction','prefpath','use_new_ui'];
            var tabHtml = [];
            var row = function(labelKey, value, tooltipKey) {
                if (value == null || value === '') {
                    return '';
                }
                var tip = tooltipKey ? ' data-toggle="tooltip" data-placement="top" title="'+esc(i18n.t('teamviewer.'+tooltipKey))+'"' : '';
                return '<tr><th'+tip+'>'+esc(i18n.t('teamviewer.'+labelKey))+'</th><td>'+esc(value)+'</td></tr>';
            };
            var yesNo = function(value) {
                if (value === 1 || value === '1') {
                    return i18n.t('yes');
                }
                if (value === 0 || value === '0') {
                    return i18n.t('no');
                }
                return '';
            };
            var labeledRow = function(labelKey, valueHtml, tooltipKey) {
                if (valueHtml == null || valueHtml === '') {
                    return '';
                }
                var tip = tooltipKey ? ' data-toggle="tooltip" data-placement="top" title="'+esc(i18n.t('teamviewer.'+tooltipKey))+'"' : '';
                return '<tr><th'+tip+'>'+esc(i18n.t('teamviewer.'+labelKey))+'</th><td>'+valueHtml+'</td></tr>';
            };
            var multilineText = function(value) {
                if (value == null || value === '') {
                    return '';
                }
                return esc(value).replace(/\r\n|\r|\n/g, '<br>');
            };
            var yesNoBadge = function(value, yesClass, noClass) {
                var label = yesNo(value);
                if (!label) {
                    return '';
                }
                var cls = (value == 1 || value === '1') ? yesClass : noClass;
                return '<span class="label '+cls+'">'+esc(label)+'</span>';
            };
            var passwordStrengthBadge = function(value) {
                if (value == null || value === '') {
                    return '';
                }
                var n = parseInt(value, 10);
                if (isNaN(n)) {
                    return '';
                }
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
                    return '';
                }
                return '<span class="label '+cls+'">'+esc(i18n.t('teamviewer.'+key))+'</span>';
            };
            // Version preference: "15.81.6 H", "15.81.6 HC" — only label known suffixes
            var parseEdition = function(version) {
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
            var formatMacAddress = function(mac) {
                if (mac == null || mac === '') {
                    return '';
                }
                var hex = String(mac).replace(/[:\s.\-]/g, '').toUpperCase();
                if (!hex || hex.length % 2 !== 0 || !/^[0-9A-F]+$/.test(hex)) {
                    return String(mac).trim();
                }
                return hex.match(/.{1,2}/g).join(':');
            };
            var formatMacList = function(value) {
                if (value == null || value === '') {
                    return '';
                }
                return String(value).split(/[\n,]+/).map(function(part) {
                    return formatMacAddress(part.trim());
                }).filter(function(part) {
                    return part !== '';
                }).join(', ');
            };
            $.each(data, function(i,d){

                var rows = ''
                var interfaceText = ''
                var interfaceClass = ''
                var uiVersionNum = (d.ui_version == null || d.ui_version === '') ? null : parseInt(d.ui_version, 10)
                // TeamViewer UIVersion: 4 = New, 2 = Classic
                if (uiVersionNum >= 4) {
                    interfaceText = i18n.t('teamviewer.interface_new')
                    interfaceClass = 'label-info'
                } else if (uiVersionNum === 2 || uiVersionNum === 1) {
                    interfaceText = i18n.t('teamviewer.interface_classic')
                    interfaceClass = 'label-warning'
                }
                var management = ''
                // Missing Management treated as Unmanaged — only explicit 0 is Managed
                var unmanagedVal = (d.unmanaged == 0 || d.unmanaged === '0') ? 0 : 1
                if (unmanagedVal == 1) {
                    management = i18n.t('teamviewer.unmanaged_state')
                } else {
                    management = i18n.t('teamviewer.managed_state')
                }

                var clientId = d.clientid == null ? '' : String(d.clientid).trim();
                if (clientId !== '') {
                    var connectbutton = '';
                    if (clientId !== '0') {
                        connectbutton = '<a class="btn btn-info btn-xs" style="margin-right: 8px;" href="'+esc(connectBase)+encodeURIComponent(clientId)+'" target="_blank" rel="noopener noreferrer">'+esc(i18n.t('teamviewer.connect'))+'</a>';
                    }
                    rows = rows + '<tr><th>'+esc(i18n.t('teamviewer.clientid'))+'</th><td>'+connectbutton+esc(clientId)+'</td></tr>';
                }
                var edition = parseEdition(d.version)
                if (edition) {
                    rows = rows + row('version', edition.number)
                    if (edition.label) {
                        rows = rows + row('edition', edition.label, 'edition_tip')
                    }
                }
                rows = rows + labeledRow('update_available', yesNoBadge(d.update_available, 'label-danger', 'label-success'))
                rows = rows + labeledRow('updateversion', multilineText(d.updateversion))
                if (interfaceText) {
                    rows = rows + labeledRow('interface', '<span class="label '+interfaceClass+'">'+esc(interfaceText)+'</span>', 'interface_tip')
                }
                // Missing Always Online treated as No — only explicit 1 is Yes
                var alwaysOnline = (d.always_online == 1 || d.always_online === '1') ? 1 : 0
                rows = rows + labeledRow('always_online', yesNoBadge(alwaysOnline, 'label-warning', 'label-success'))
                rows = rows + row('owning_manager_account_name', d.owning_manager_account_name, 'owning_manager_account_name_tip')
                rows = rows + row('owning_manager_company_name', d.owning_manager_company_name, 'owning_manager_company_name_tip')
                if (management) {
                    var managementClass = unmanagedVal == 1 ? 'label-warning' : 'label-success'
                    rows = rows + labeledRow('unmanaged', '<span class="label '+managementClass+'">'+esc(management)+'</span>', 'unmanaged_tip')
                }
                rows = rows + row('buddy_login_name', d.buddy_login_name, 'buddy_login_name_tip')
                rows = rows + row('buddy_display_name', d.buddy_display_name, 'buddy_display_name_tip')
                rows = rows + row('meeting_username', d.meeting_username)
                rows = rows + row('licensetype', d.licensetype)
                rows = rows + row('lastmacused', formatMacList(d.lastmacused))
                rows = rows + row('ipc_port_service', d.ipc_port_service)
                rows = rows + row('autoupdatemode', d.autoupdatemode)
                rows = rows + labeledRow('security_adminrights', yesNoBadge(d.security_adminrights, 'label-success', 'label-danger'))
                var pwdStrength = passwordStrengthBadge(d.security_passwordstrength)
                if (pwdStrength) {
                    rows = rows + labeledRow('security_passwordstrength', pwdStrength, 'security_passwordstrength_tip')
                }
                rows = rows + labeledRow('had_a_commercial_connection', yesNoBadge(d.had_a_commercial_connection, 'label-success', 'label-info'))
                rows = rows + row('is_not_first_run_without_connection', yesNo(d.is_not_first_run_without_connection))
                rows = rows + row('is_not_running_test_connection', yesNo(d.is_not_running_test_connection))
                rows = rows + row('clientic', d.clientic)

                var shown = ['clientid','version','update_available','updateversion','ui_version','always_online','owning_manager_account_name','owning_manager_company_name','unmanaged','buddy_login_name','buddy_display_name','meeting_username','licensetype','lastmacused','ipc_port_service','autoupdatemode','security_adminrights','security_passwordstrength','had_a_commercial_connection','is_not_first_run_without_connection','is_not_running_test_connection','clientic'];
                for (var prop in d){
                    if (skipThese.indexOf(prop) !== -1 || shown.indexOf(prop) !== -1) {
                        continue;
                    }
                    if (d[prop] == null || d[prop] === '') {
                        continue;
                    }
                    rows = rows + row(prop, d[prop]);
                }

                var prefpath = (d.prefpath || '').replace('Library/Preferences/com.teamviewer.teamviewer.preferences.Machine.plist','').replace('Preferences/com.teamviewer.teamviewer.preferences.plist','')

                tabHtml.push(
                    '<h4>' + esc(prefpath) + '</h4>' +
                    '<div style="max-width:640px;">' +
                        '<table class="table table-striped table-condensed">' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                    '</div>'
                );
            })

            $('#teamviewer-content').html(tabHtml.join(''));
            $('#teamviewer-content [data-toggle="tooltip"]').tooltip({
                placement: 'top',
                container: 'body'
            });
        }
	});
});
</script>
