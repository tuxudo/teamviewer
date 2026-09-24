<?php

use CFPropertyList\CFPropertyList;

class Teamviewer_model extends \Model 
{
    public function __construct($serial='')
    {
        parent::__construct('id', 'teamviewer'); //primary key, tablename
        $this->rs['id'] = 0;
        $this->rs['serial_number'] = $serial;
        $this->rs['always_online'] = 0; // True/False
        $this->rs['autoupdatemode'] = 0;
        $this->rs['clientid'] = 0;
        $this->rs['clientic'] = 0;
        $this->rs['had_a_commercial_connection'] = 0; // True/False
        $this->rs['ipc_port_service'] = 0;
        $this->rs['lastmacused'] = '';
        $this->rs['licensetype'] = 0;
        $this->rs['midversion'] = 0;
        $this->rs['moverestriction'] = 0; // True/False
        $this->rs['security_adminrights'] = 0; // True/False
        $this->rs['security_passwordstrength'] = 0;
        $this->rs['version'] = '';
        $this->rs['update_available'] = 0; // True/False
        $this->rs['is_not_first_run_without_connection'] = 0; // True/False
        $this->rs['is_not_running_test_connection'] = 0; // True/False
        $this->rs['meeting_username'] = '';
        $this->rs['prefpath'] = '';
        $this->rs['updateversion'] = '';
        $this->rs['owning_manager_account_name'] = '';
        $this->rs['owning_manager_company_name'] = '';
        $this->rs['unmanaged'] = 0;
        $this->rs['buddy_login_name'] = '';
        $this->rs['buddy_display_name'] = '';
        $this->rs['ui_version'] = 0;
        $this->rs['use_new_ui'] = 0;

        if ($serial) {
            $this->retrieve_record($serial);
        }

        $this->serial = $serial;
    }

    // ------------------------------------------------------------------------

    /**
     * Process data sent by postflight
     *
     * @param string data
     * 
     **/
    public function process($data)
    {
        if (! $data) {
            echo "Error Processing Request: No TeamViewer data found";
            return;
        }

        $plist = $this->parsePlist($data);
        if (! is_array($plist)) {
            echo "Error Processing Request: Invalid TeamViewer data";
            return;
        }

        $rows = array();
        foreach ($plist as $preffile) {
            $normalized = $this->normalizeRow($preffile);
            if ($normalized === null) {
                continue;
            }

            $clientid = $normalized['clientid'];
            if (! isset($rows[$clientid])) {
                $rows[$clientid] = $normalized;
                continue;
            }

            foreach ($normalized as $item => $value) {
                if (($rows[$clientid][$item] === null || $rows[$clientid][$item] === '') && $value !== null && $value !== '') {
                    $rows[$clientid][$item] = $value;
                }
            }
        }

        if (! $rows) {
            return;
        }

        // Defaults only for rows that already have a TeamViewer Client ID (never invent records).
        // Always Online: Yes only when prefs explicitly report it; else No.
        // Management: Managed only when prefs explicitly report managed; else Unmanaged.
        foreach ($rows as $clientid => $row) {
            $rows[$clientid]['always_online'] = (isset($row['always_online']) && $row['always_online'] === 1) ? 1 : 0;
            $rows[$clientid]['unmanaged'] = (isset($row['unmanaged']) && $row['unmanaged'] === 0) ? 0 : 1;
        }

        $this->deleteWhere('serial_number=?', $this->serial_number);

        foreach ($rows as $row) {
            foreach ($row as $item => $value) {
                $this->$item = $value;
            }
            $this->id = '';
            $this->save();
        }
    }

    /**
     * Parse an untrusted plist. Reject XML entity declarations.
     *
     * @param string $data
     * @return array|null
     */
    private function parsePlist($data)
    {
        if (! is_string($data) || $data === '') {
            return null;
        }

        // Apple XML plists include a DOCTYPE. Reject only custom entity declarations.
        if (stripos($data, '<!ENTITY') !== false) {
            return null;
        }

        $previousLoader = null;
        $restoreLoader = false;
        $entityLoaderDisabled = null;
        if (function_exists('libxml_set_external_entity_loader')) {
            // libxml_set_external_entity_loader() returns bool, not the previous loader.
            if (function_exists('libxml_get_external_entity_loader')) {
                $previousLoader = libxml_get_external_entity_loader();
            }
            libxml_set_external_entity_loader(static function () {
                return null;
            });
            $restoreLoader = true;
        } elseif (PHP_VERSION_ID < 80000 && function_exists('libxml_disable_entity_loader')) {
            $entityLoaderDisabled = libxml_disable_entity_loader(true);
        }

        $plist = null;
        try {
            $parser = new CFPropertyList();
            $parser->parse($data);
            $plist = $parser->toArray();
        } catch (Throwable $e) {
            $plist = null;
        } finally {
            if ($restoreLoader) {
                libxml_set_external_entity_loader($previousLoader);
            } elseif ($entityLoaderDisabled !== null) {
                libxml_disable_entity_loader($entityLoaderDisabled);
            }
        }

        return is_array($plist) ? $plist : null;
    }

    /**
     * Keep known fields and coerce types. 0 and false are stored, not treated as empty.
     *
     * @param mixed $preffile
     * @return array|null
     */
    private function normalizeRow($preffile)
    {
        if (! is_array($preffile)) {
            return null;
        }

        $clientid = $this->cleanClientId(isset($preffile['clientid']) ? $preffile['clientid'] : null);
        if ($clientid === null) {
            return null;
        }

        $row = array('clientid' => $clientid);

        foreach (array(
            'always_online',
            'had_a_commercial_connection',
            'moverestriction',
            'security_adminrights',
            'update_available',
            'is_not_first_run_without_connection',
            'is_not_running_test_connection',
            'unmanaged',
            'use_new_ui',
        ) as $item) {
            $row[$item] = array_key_exists($item, $preffile) ? $this->cleanBool($preffile[$item]) : null;
        }

        foreach (array(
            'autoupdatemode',
            'clientic',
            'ipc_port_service',
            'licensetype',
            'midversion',
            'security_passwordstrength',
        ) as $item) {
            $row[$item] = array_key_exists($item, $preffile) ? $this->cleanInt($preffile[$item]) : null;
        }

        $row['ui_version'] = array_key_exists('ui_version', $preffile) ? $this->cleanSmallInt($preffile['ui_version'], 99) : null;

        foreach (array(
            'version',
            'meeting_username',
            'prefpath',
            'updateversion',
        ) as $item) {
            $row[$item] = array_key_exists($item, $preffile) ? $this->cleanString($preffile[$item]) : null;
        }

        $row['lastmacused'] = array_key_exists('lastmacused', $preffile)
            ? $this->cleanMacList($preffile['lastmacused'])
            : null;

        foreach (array(
            'owning_manager_account_name',
            'owning_manager_company_name',
            'buddy_login_name',
            'buddy_display_name',
        ) as $item) {
            $row[$item] = array_key_exists($item, $preffile) ? $this->cleanLine($preffile[$item]) : null;
        }

        return $row;
    }

    private function cleanClientId($value)
    {
        if (is_int($value)) {
            if ($value < 0) {
                return null;
            }
            $value = sprintf('%d', $value);
        } elseif (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if (! preg_match('/^[0-9]{1,20}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function cleanBool($value)
    {
        if ($value === true || $value === 1 || $value === '1') {
            return 1;
        }
        if ($value === false || $value === 0 || $value === '0') {
            return 0;
        }

        return null;
    }

    private function cleanInt($value)
    {
        if (is_bool($value) || is_array($value)) {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => -2147483648, 'max_range' => 2147483647),
        ));

        return $int === false ? null : $int;
    }

    private function cleanSmallInt($value, $max)
    {
        if (is_bool($value) || is_array($value)) {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 0, 'max_range' => $max),
        ));

        return $int === false ? null : $int;
    }

    private function cleanString($value)
    {
        if (is_int($value)) {
            $value = sprintf('%d', $value);
        }
        if (! is_string($value)) {
            return null;
        }

        $value = str_replace(array("\r\n", "\r"), "\n", trim($value));
        if ($value === '') {
            return null;
        }
        if (strlen($value) > 255) {
            $value = substr($value, 0, 255);
        }

        return $value;
    }

    /**
     * Normalize MAC addresses to xx:xx:xx:xx:xx:xx (one per line).
     *
     * @param mixed $value
     * @return string|null
     */
    private function cleanMacList($value)
    {
        $value = $this->cleanString($value);
        if ($value === null) {
            return null;
        }

        $formatted = array();
        foreach (preg_split('/[\n,]+/', $value) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $mac = $this->formatMacAddress($part);
            if ($mac !== null) {
                $formatted[] = $mac;
            }
        }

        if (! $formatted) {
            return null;
        }

        $out = implode("\n", $formatted);
        if (strlen($out) > 255) {
            $out = substr($out, 0, 255);
        }

        return $out;
    }

    /**
     * @param string $value
     * @return string|null
     */
    private function formatMacAddress($value)
    {
        $hex = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $value));
        if ($hex === '' || (strlen($hex) % 2) !== 0) {
            return null;
        }

        $pairs = str_split($hex, 2);

        return implode(':', $pairs);
    }

    private function cleanLine($value)
    {
        $value = $this->cleanString($value);
        if ($value === null || strpos($value, "\n") !== false) {
            return null;
        }

        return $value;
    }
}
