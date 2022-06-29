<?php

namespace PH\Providers\Database;

use PH\Support\Providers\EventServiceProvider as ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // scheduled mail
        'admin_init' => [
            '\PH\Database\ProjectMembersTable',
            '\PH\Database\ThreadMembersTable'
        ]
    ];
}
