<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Treat unreported Management as unmanaged (1), not unknown.
 * Only updates existing TeamViewer rows that have a Client ID — never inserts.
 */
class TeamviewerUnmanagedDefaultOn extends Migration
{
    private $tableName = 'teamviewer';

    public function up()
    {
        $capsule = new Capsule();

        if (! $capsule::schema()->hasTable($this->tableName)) {
            return;
        }

        if (! $capsule::schema()->hasColumn($this->tableName, 'unmanaged')) {
            return;
        }

        Capsule::table($this->tableName)
            ->whereNull('unmanaged')
            ->whereNotNull('clientid')
            ->where('clientid', '!=', '')
            ->update(array('unmanaged' => 1));
    }

    public function down()
    {
        // Intentional no-op: coalescing NULL → 1 is not reversible.
    }
}
