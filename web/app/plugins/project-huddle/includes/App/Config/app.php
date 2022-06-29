<?php

return [
    // register service providers here
    'providers' => [
        '\PH\Providers\Mail\MailEventServiceProvider',
        '\PH\Providers\Database\MigrationServiceProvider',
        '\PH\Controllers\VersionsController',
        'approvals' => '\PH\Controllers\ApprovalController',
        'auth' => '\PH\Support\Authentication'
    ]
];
