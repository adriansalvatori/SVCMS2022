<?php

namespace PH\Controllers;

use PH\Models\Image;

class VersionsController
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'current']);
        add_action('ph_new_version_added', [$this, 'clearTransient']);
    }

    public function current()
    {
        register_rest_field(
            'project_image',
            'current_version',
            [
                'get_callback' => function ($post) {
                    return Image::get($post['id'])->currentVersion();
                },
                'schema'          => [
                    'description' => esc_html__('Current version number', 'project-huddle'),
                    'type'        => 'number',
                    'default'     => 1,
                ],
            ]
        );
    }

    public function clearTransient($id)
    {
        delete_transient('ph_current_version_' . $id);
    }
}
