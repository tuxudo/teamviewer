<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Treat unreported Always Online as off (0), not unknown.
 * Only updates existing TeamViewer rows that have a Client ID — never inserts.
 */
class TeamviewerAlwaysOnlineDefaultOff extends Migration
{
    private $tableName = 'teamviewer';

    public function up()
    {
        $capsule = new Capsule();

        if (! $capsule::schema()->hasTable($this->tableName)) {
            return;
        }

        if (! $capsule::schema()->hasColumn($this->tableName, 'always_online')) {
            return;
        }

        Capsule::table($this->tableName)
            ->whereNull('always_online')
            ->whereNotNull('clientid')
            ->where('clientid', '!=', '')
            ->update(array('always_online' => 0));
    }

    public function down()
    {
        // Intentional no-op: coalescing NULL → 0 is not reversible.
    }
}
