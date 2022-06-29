<?php

namespace PH\Database;

use PH\Support\Database\Migrations\Migration;

class ProjectMembersTable extends Migration
{
    const name = "ph_members";

    public function up()
    {
        global $wpdb;
        return "user_id bigint(20) UNSIGNED NOT NULL,
        project_id bigint(20) UNSIGNED NOT NULL,
        PRIMARY KEY  (user_id, project_id),
        KEY IX_project_id (project_id),
        CONSTRAINT `{$wpdb->prefix}ph_members_post` FOREIGN KEY (`project_id`) REFERENCES `{$wpdb->prefix}posts` (`ID`) ON DELETE CASCADE,
        CONSTRAINT `{$wpdb->prefix}ph_members_user` FOREIGN KEY (`user_id`) REFERENCES `{$wpdb->prefix}users` (`ID`) ON DELETE CASCADE";
    }
}
