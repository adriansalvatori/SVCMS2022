<?php

use PH\Controllers\Mail\Mailers\ActivityMailer;

/**
 * Ajax actions for ProjectHuddle
 * These actions don't fall under REST API requests
 */

/**
 * Quickly get a list of all project names and IDs
 *
 * @return void
 */
function ph_ajax_list_projects()
{
    check_ajax_referer('wp_rest');

    $args =  array(
        'post_type' => array('ph-project', 'ph-website'),
        'posts_per_page' => -1,
    );

    if (!current_user_can('edit_others_ph-projects')) {
        $project_ids      = ph_get_users_project_ids();
        if (!empty($project_ids)) {
            $ids = (array) $project_ids;
        } else {
            $ids = array(-1);
        }
        $args['post__in'] =  $ids;
    }

    if (isset($_POST['search'])) {
        $args['s'] = $_POST['search'];
    }

    $projects_query = new WP_Query($args);

    foreach ($projects_query->posts as $project) {
        $status = array(
            'total'    => 0,
            'resolved' => 0,
        );
        if ($project->post_type === 'ph-website') {
            $status = ph_get_website_resolve_status($project->ID);
        }
        if ($project->post_type === 'ph-project') {
            $status = ph_get_mockup_resolve_status($project->ID);
        }

        $project->total_comments = $status['total'];
        $project->resolved_comments = $status['resolved'];
        $project->members = ph_get_project_member_ids($project->ID);
    }

    wp_send_json_success($projects_query->posts);
}
add_action('wp_ajax_ph_list_projects', 'ph_ajax_list_projects');

function ph_email_truncate_remove()
{
    return 99999;
}

function ph_ajax_manual_activity_email()
{
    check_ajax_referer('wp_rest');

    $args =  array(
        'type' => 'daily',
        'id'    => 0,
        'members' =>  array(),
        'subject' => '',
        'message' => ''
    );

    foreach ($args as $key => $arg) {
        if (isset($_POST[$key])) {
            $args[$key] = $_POST[$key];
        }
    }

    // make sure only people who can edit projects
    if (!current_user_can('edit_ph-project', $args['id']) || !current_user_can('edit_ph-website', $args['id'])) {
        wp_die('You are not allowed to do this.');
    }

    // validate members
    $args['members'] = array_map('intval', $args['members']);
    $has_activity = false;

    if (empty($args['members'])) {
        wp_send_json_success([
            'message' => __('You need to choose at least one recipient.', 'project-huddle'),
            'type' => 'warning'
        ]);
    }

    foreach ($args['members'] as $user_id) {
        // get users activity comments
        $user = new \PH\Models\User($user_id);

        // hook to disable for a specific user
        if (!$user || apply_filters('ph_disable_manual_activity_emails', false, $user->ID)) {
            continue;
        }

        $activity = $user->projectsActivity()
            ->type($args['type'])
            ->projects([(int) $args['id']])
            ->subscribed(apply_filters("ph_manual_{$args['type']}_emails_subscribed_only", $args['type'] === 'activity'))
            ->groupBy('project_id')
            ->get();

        // send activity email
        if ($activity) {
            $has_activity = true;
            add_filter('ph_comment_truncate', 'ph_email_truncate_remove');
            add_filter('ph_approvals_truncate', 'ph_email_truncate_remove');
            add_filter('ph_assign_truncate', 'ph_email_truncate_remove');
            add_filter('ph_resolved_comment_truncate', 'ph_email_truncate_remove');

            $send = (new ActivityMailer($args['type']))
                ->activity($activity)
                ->manual(true)
                ->to($user->user_email)
                ->subject($args['subject'] ?: __('Latest Project Activity', 'project-huddlle'))
                ->title('')
                ->message($args['message'])
                ->send();

            remove_filter('ph_comment_truncate', 'ph_email_truncate_remove');
            remove_filter('ph_approvals_truncate', 'ph_email_truncate_remove');
            remove_filter('ph_assign_truncate', 'ph_email_truncate_remove');
            remove_filter('ph_resolved_comment_truncate', 'ph_email_truncate_remove');
        }
    }

    if (!$has_activity) {
        wp_send_json_success([
            'message' => __('There is no activity within this time period to send. (' . $args['type'] . ').', 'project-huddle'),
            'type' => 'warning'
        ]);
    }

    if ($send) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_ph_send_activity_email', 'ph_ajax_manual_activity_email');

/**
 * Ajax handler to renew the REST API nonce.
 *
 * @since 3.8.10
 */
function ph_ajax_rest_nonce()
{
    exit(wp_create_nonce('wp_rest'));
}
add_action('wp_ajax_nopriv_ph_rest_nonce', 'ph_ajax_rest_nonce');
add_action('wp_ajax_ph_rest_nonce', 'ph_ajax_rest_nonce');
