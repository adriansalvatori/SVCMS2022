<?php

namespace PH\Support\Database\Migrations;

abstract class Migration
{
    protected $table_name;
    protected $version = '1.0';
    private $sql;

    abstract public function up();

    public function __construct()
    {
        $this->table_name = static::name;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle()
    {
        $this->createTable();
    }

    protected function createTable()
    {
        // if this version is less than or equal to the database version, don't create
        if (version_compare($this->version, get_option("{$this->table_name}_db_version", '0.0'), '<=')) {
            return;
        }

        // create table
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $this->sql = $this->up();
        $this->sql = "CREATE TABLE IF NOT EXISTS`{$wpdb->prefix}{$this->table_name}` ({$this->sql})$charset_collate;";

        // Don't use DB Delta since we cannot create foreign keys
        $wpdb->query($this->sql);

        $success = empty($wpdb->last_error);

        if (!$success) {
            ph_log($wpdb->last_error);
            return;
        }

        ph_log("Successfully created {$this->table_name} database");

        // update site option
        update_option("{$this->table_name}_db_version", $this->version);

        // success
        return true;
    }
}
