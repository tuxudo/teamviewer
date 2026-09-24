<?php

/**
 * Teamviewer module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Teamviewer_controller extends Module_controller
{
    public function __construct()
    {
        $this->module_path = dirname(__FILE__);
    }

    /**
     * Default method
     *
     * @author AvB
     **/
    public function index()
    {
        echo "You've loaded the teamviewer module!";
    }

    /**
     * Connect URL base for listing Connect buttons
     *
     * @return void
     **/
    public function get_connect_base()
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        configAppendFile(__DIR__ . '/config.php');
        $link = conf('teamviewer_link', 'teamviewer10://control?device=');
        if (! is_string($link) || ! preg_match('#^[A-Za-z][A-Za-z0-9+.-]*://#', $link)) {
            $link = 'teamviewer10://control?device=';
        }

        jsonView(array('link' => $link));
    }

    /**
     * Update available button widget counts
     *
     * @return void
     **/
    public function get_update_available_stats()
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        jsonView($this->buttonCounts(
            "COUNT(CASE WHEN update_available = 1 THEN 1 END) AS 'yes',
             COUNT(CASE WHEN update_available = 0 THEN 1 END) AS 'no'"
        ));
    }

    /**
     * Managed / unmanaged button widget counts
     *
     * @return void
     **/
    public function get_management_stats()
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        jsonView($this->buttonCounts(
            "COUNT(CASE WHEN unmanaged = 1 OR unmanaged IS NULL THEN 1 END) AS 'unmanaged',
             COUNT(CASE WHEN unmanaged = 0 THEN 1 END) AS 'managed'"
        ));
    }

    /**
     * Always online button widget counts
     *
     * @return void
     **/
    public function get_always_online_stats()
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        jsonView($this->buttonCounts(
            "COUNT(CASE WHEN always_online = 1 THEN 1 END) AS 'yes',
             COUNT(CASE WHEN always_online IS NULL OR always_online = 0 THEN 1 END) AS 'no'"
        ));
    }

    /**
     * Password strength button widget counts
     *
     * @return void
     **/
    public function get_password_strength_stats()
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        jsonView($this->buttonCounts(
            "COUNT(CASE WHEN security_passwordstrength = 0 THEN 1 END) AS 'disabled',
             COUNT(CASE WHEN security_passwordstrength = 1 THEN 1 END) AS 'low',
             COUNT(CASE WHEN security_passwordstrength = 2 THEN 1 END) AS 'medium',
             COUNT(CASE WHEN security_passwordstrength = 3 THEN 1 END) AS 'high'"
        ));
    }

    /**
     * Version distribution for bargraph widget
     *
     * @return void
     **/
    public function get_version_stats()
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        $sql = "SELECT version AS label, COUNT(1) AS count
                FROM teamviewer
                LEFT JOIN reportdata USING (serial_number)
                WHERE ".get_machine_group_filter('')."
                AND teamviewer.clientid IS NOT NULL
                AND teamviewer.clientid != ''
                AND teamviewer.version IS NOT NULL
                AND teamviewer.version != ''
                GROUP BY version";

        $out = array();
        $queryobj = new Teamviewer_model();
        foreach ($queryobj->query($sql) as $row) {
            $label = isset($row->label) ? trim((string) $row->label) : '';
            if ($label === '') {
                continue;
            }
            $out[] = array(
                'label' => $label,
                'count' => intval($row->count),
            );
        }

        usort($out, function ($a, $b) {
            $av = preg_replace('/^(\d+(?:\.\d+)*).*$/', '$1', $a['label']);
            $bv = preg_replace('/^(\d+(?:\.\d+)*).*$/', '$1', $b['label']);
            $cmp = version_compare($bv, $av);
            if ($cmp !== 0) {
                return $cmp;
            }
            if ($a['count'] === $b['count']) {
                return strcmp($a['label'], $b['label']);
            }
            return ($a['count'] < $b['count']) ? 1 : -1;
        });

        jsonView($out);
    }

    /**
     * Retrieve data in json format for client tab
     *
     * @return void
     * @author tuxudo
     **/
    public function get_data($serial_number = '')
    {
        if (! $this->authorized()) {
            jsonError('Not authorized', 403);
            return;
        }

        $serial_number = preg_replace("/[^A-Za-z0-9_\-]+/", '', $serial_number);

        if ($serial_number === '') {
            jsonView(array());
            return;
        }

        if (! authorized_for_serial($serial_number)) {
            jsonError('Unauthorized access to device data', 403);
            return;
        }

        $sql = "SELECT teamviewer.clientid, teamviewer.clientic, teamviewer.always_online, teamviewer.autoupdatemode, teamviewer.version, teamviewer.update_available, teamviewer.lastmacused, teamviewer.security_adminrights, teamviewer.security_passwordstrength, teamviewer.meeting_username, teamviewer.ipc_port_service, teamviewer.licensetype, teamviewer.is_not_first_run_without_connection, teamviewer.is_not_running_test_connection, teamviewer.had_a_commercial_connection, teamviewer.prefpath, teamviewer.updateversion, teamviewer.owning_manager_account_name, teamviewer.owning_manager_company_name, teamviewer.unmanaged, teamviewer.buddy_login_name, teamviewer.buddy_display_name, teamviewer.ui_version, teamviewer.use_new_ui
                FROM teamviewer
                LEFT JOIN reportdata USING (serial_number)
                WHERE teamviewer.serial_number = ?
                ".get_machine_group_filter('AND');

        $queryobj = new Teamviewer_model();
        jsonView($queryobj->query($sql, array($serial_number)));
    }

    /**
     * Run a COUNT CASE widget query and return label/count rows
     *
     * @param string $selectSql Whitelisted SELECT fragment only
     * @return array
     **/
    private function buttonCounts($selectSql)
    {
        $sql = "SELECT ".$selectSql."
                FROM teamviewer
                LEFT JOIN reportdata USING (serial_number)
                WHERE ".get_machine_group_filter('')."
                AND teamviewer.clientid IS NOT NULL
                AND teamviewer.clientid != ''";

        $out = array();
        $queryobj = new Teamviewer_model();
        $rows = $queryobj->query($sql);
        if (! $rows || ! isset($rows[0])) {
            return $out;
        }

        foreach ($rows[0] as $label => $value) {
            $out[] = array('label' => $label, 'count' => $value);
        }

        return $out;
    }
}
