<?php

namespace PH\Database;

use PH\Support\Database\Migrations\Migration;

class ThreadMembersTable extends Migration
{
    const name = "ph_thread_members";

    public function up()
    {
        global $wpdb;

        return "user_id bigint(20) UNSIGNED NOT NULL,
        post_id bigint(20) UNSIGNED NOT NULL,
        PRIMARY KEY  (user_id, post_id),
        KEY IX_post_id (post_id),
        CONSTRAINT `{$wpdb->prefix}ph_thread_members_post` FOREIGN KEY (`post_id`) REFERENCES `{$wpdb->prefix}posts` (`ID`) ON DELETE CASCADE,
        CONSTRAINT `{$wpdb->prefix}ph_thread_members_user` FOREIGN KEY (`user_id`) REFERENCES `{$wpdb->prefix}users` (`ID`) ON DELETE CASCADE";
    }
}
