<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Teamviewer_model::class, function (Faker\Generator $faker) {
    return [
        'always_online' => $faker->numberBetween(0, 1),
        'autoupdatemode' => $faker->numberBetween(0, 3),
        'clientid' => $faker->numerify('##########'),
        'clientic' => $faker->numberBetween(1, 2000000000),
        'had_a_commercial_connection' => 0,
        'ipc_port_service' => 5939,
        'lastmacused' => sprintf('%02X:%02X:%02X:%02X:%02X:%02X', $faker->numberBetween(0,255), $faker->numberBetween(0,255), $faker->numberBetween(0,255), $faker->numberBetween(0,255), $faker->numberBetween(0,255), $faker->numberBetween(0,255)),
        'licensetype' => 10000,
        'midversion' => $faker->numberBetween(1, 5),
        'moverestriction' => 0,
        'security_adminrights' => 0,
        'security_passwordstrength' => $faker->numberBetween(0, 3),
        'version' => $faker->numerify('#.#.#'),
        'update_available' => $faker->numberBetween(0, 1),
        'is_not_first_run_without_connection' => $faker->numberBetween(0, 1),
        'is_not_running_test_connection' => $faker->numberBetween(0, 1),
        'meeting_username' => 'demo',
        'prefpath' => '/Library/Preferences/com.teamviewer.teamviewer.preferences.plist',
        'updateversion' => $faker->numerify('#.#.#'),
        'owning_manager_account_name' => $faker->name(),
        'owning_manager_company_name' => $faker->company(),
        'unmanaged' => $faker->numberBetween(0, 1),
        'buddy_login_name' => $faker->safeEmail(),
        'buddy_display_name' => $faker->name(),
        'ui_version' => 4,
        'use_new_ui' => 1,
    ];
});
