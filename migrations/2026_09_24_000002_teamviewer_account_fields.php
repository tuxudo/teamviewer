<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Manager, signed-in account, and interface fields from TeamViewer preferences.
 */
class TeamviewerAccountFields extends Migration
{
    private $tableName = 'teamviewer';

    public function up()
    {
        $capsule = new Capsule();

        if (!$capsule::schema()->hasTable($this->tableName)) {
            return;
        }

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            if (!Capsule::schema()->hasColumn('teamviewer', 'owning_manager_account_name')) {
                $table->string('owning_manager_account_name')->nullable();
            }
            if (!Capsule::schema()->hasColumn('teamviewer', 'owning_manager_company_name')) {
                $table->string('owning_manager_company_name')->nullable();
            }
            if (!Capsule::schema()->hasColumn('teamviewer', 'unmanaged')) {
                $table->boolean('unmanaged')->nullable();
                $table->index('unmanaged');
            }
            if (!Capsule::schema()->hasColumn('teamviewer', 'buddy_login_name')) {
                $table->string('buddy_login_name')->nullable();
            }
            if (!Capsule::schema()->hasColumn('teamviewer', 'buddy_display_name')) {
                $table->string('buddy_display_name')->nullable();
            }
            if (!Capsule::schema()->hasColumn('teamviewer', 'ui_version')) {
                $table->integer('ui_version')->nullable();
            }
            if (!Capsule::schema()->hasColumn('teamviewer', 'use_new_ui')) {
                $table->boolean('use_new_ui')->nullable();
            }
        });
    }

    public function down()
    {
        $capsule = new Capsule();

        if (!$capsule::schema()->hasTable($this->tableName)) {
            return;
        }

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            if (Capsule::schema()->hasColumn('teamviewer', 'unmanaged')) {
                $table->dropIndex(['unmanaged']);
            }
            $table->dropColumn(array(
                'owning_manager_account_name',
                'owning_manager_company_name',
                'unmanaged',
                'buddy_login_name',
                'buddy_display_name',
                'ui_version',
                'use_new_ui',
            ));
        });
    }
}
