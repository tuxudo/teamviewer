<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * TeamViewer Client IDs are decimal strings. ClientID_64 does not fit in a signed INT.
 */
class TeamviewerClientidString extends Migration
{
    private $tableName = 'teamviewer';

    public function up()
    {
        $capsule = new Capsule();

        if (!$capsule::schema()->hasTable($this->tableName)) {
            return;
        }

        if (!$capsule::schema()->hasColumn($this->tableName, 'clientid')) {
            return;
        }

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropIndex(['clientid']);
        });

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->string('clientid', 32)->nullable()->change();
        });

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('clientid');
        });
    }

    public function down()
    {
        $capsule = new Capsule();

        if (!$capsule::schema()->hasTable($this->tableName) ||
            !$capsule::schema()->hasColumn($this->tableName, 'clientid')) {
            return;
        }

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropIndex(['clientid']);
        });

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->integer('clientid')->nullable()->change();
        });

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('clientid');
        });
    }
}
