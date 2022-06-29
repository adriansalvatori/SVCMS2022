<?php

namespace PH\Controllers\Mail\Builders;

use PH\Models\Project;

if (!defined('ABSPATH')) exit;

class ActivityMailBuilder
{
    /**
     * Stores activity
     */
    protected $activity;

    /**
     * Store the sections output
     *
     * @var array
     */
    private $sections = [];

    /**
     * Requires an activity array
     *
     * @param Array $activity
     */
    public function __construct($activity)
    {
        $this->activity = $activity;
    }

    /**
     * Sections
     * @return void
     */
    public function sections()
    {
        return $this->buildSections();
    }

    /**
     * Build email sections
     *
     * @return void
     */
    protected function buildSections()
    {
        // loop through each project
        foreach ($this->activity as $project_id => $comments) {
            // create sections for each
            $nested = $this->createSections($comments);

            // add to sections output
            if (!empty($nested)) {
                $this->sections[$project_id] = array(
                    'name'     => ph_get_the_title($project_id),
                    'approval' => (bool) ph_post_is_approved($project_id),
                    'activity' => $nested,
                );
            }
        }
    }

    /**
     * Groups project comments by type
     *
     * @param array $comments
     * @return void
     */
    protected function createSections($comments)
    {
        if (empty($comments)) {
            return [];
        }

        $categories = [];

        foreach ($comments as $comment) {
            switch ($comment->comment_type) {
                case 'ph_approval':
                    $post_type = get_post_type($comment->comment_post_ID);
                    $approved  = get_comment_meta($comment->comment_ID, 'approval', true);
                    $type = $this->getSimpleTypeName($post_type);
                    if ($approved) { // TODO: Add unapprovals
                        $categories[$type][$approved ? 'approved' : 'unapproved'][] = $comment;
                    }
                    break;
                case 'ph_assign':
                    $categories['assign'][] = $comment;
                    break;
                default:
                    $categories['comment'][] = $comment;
                    break;
            }
        }
        return $categories;
    }

    /**
     * Build templates via html
     *
     * @return String
     */
    public function html()
    {
        ob_start();

        if (empty($this->activity)) {
            return;
        }

        $this->sections();

        if (empty($this->sections)) {
            return;
        }

        // loop through each project
        foreach ($this->sections as $project_id => $section) {
            $project = new Project($project_id);
            // project name section
            if ($section['name']) {
                ph_get_template(
                    'email/parts/project-section-name.php',
                    array(
                        'name'             => $section['name'],
                        'approved'         => $section['approval'],
                        'item_approval'    => ph_get_items_approval_status($project_id, $project->getPostTypeObject()->slug),
                        'project_id'       => $project_id,
                        'approval_comment' => isset($section['activity']['project']) && isset($section['activity']['project']['approved']) ? $section['activity']['project']['approved'][0] : false,
                    )
                );
            }

            // if project is approved
            if (ph_post_is_approved($project_id)) {
                $approval = isset($section['activity']['project']) && isset($section['activity']['project']['approved']) ? $section['activity']['project']['approved'][0] : false;

                if ($approval) {
                    ph_get_template(
                        'email/parts/project-approval.php',
                        array(
                            'person' => $approval->comment_author,
                            'avatar' => get_avatar_url($approval->comment_author_email, 32),
                            'date'    => sprintf(__('%s ago', 'project-huddle'), human_time_diff(strtotime($approval->comment_date), current_time('timestamp'))),
                        )
                    );
                }
            } else {
                if (isset($section['activity']['item']) && isset($section['activity']['item']['approved'])) {
                    ph_get_template(
                        'email/parts/resolved-items-list.php',
                        array(
                            'approved'      => count($section['activity']['item']['approved']),
                            'comments'   => $section['activity']['item']['approved'],
                            'type'      => get_post_type_object(get_post_type($project_id))->labels->singular_name,
                            'total'     => count((array) $section['activity']['item']['approved']) + count((array) $section['activity']['item']['unapproved']),
                            'project_id' => $project_id,
                        )
                    );
                }
            }

            // assignment activity
            if (isset($section['activity']['assign']) && !empty($section['activity']['assign'])) {
                $unique_assign = ph_unique_post_array($section['activity']['assign'], 'comment_post_ID');

                // unset resolved
                foreach ($unique_assign as $key => $comment) {
                    if (get_post_meta($comment->comment_post_ID, 'resolved', true)) {
                        unset($unique_assign[$key]);
                    }
                }
                if (count($unique_assign)) {
                    ph_get_template(
                        'email/parts/assign-list.php',
                        array(
                            'total'      => count($unique_assign),
                            'comments'   => $unique_assign,
                            'project_id' => $project_id,
                        )
                    );
                }
            }

            // comment activity
            if (isset($section['activity']['comment']) && $section['activity']['comment']) {
                ph_get_template(
                    'email/parts/comments-list.php',
                    array(
                        'total'      => count($section['activity']['comment']),
                        'comments'   => $section['activity']['comment'],
                        'project_id' => $project_id,
                    )
                );
            }

            // thread activity
            if (isset($section['activity']['thread']) && isset($section['activity']['thread']['approved'])) {
                ph_get_template(
                    'email/parts/resolved-comments-list.php',
                    array(
                        'total'      => count($section['activity']['thread']['approved']),
                        'comments'   => $section['activity']['thread']['approved'],
                        'project_id' => $project_id,
                    )
                );
            }
        }

        $output = ob_get_clean();
        return $output;
    }

    /**
     * Get the simple post type name
     *
     * @param String $type
     * @return void
     */
    private function getSimpleTypeName($type)
    {
        // TODO: Move this to post type object
        if (in_array($type, ph_get_item_post_types())) {
            return 'item';
        }
        if (in_array($type, ph_get_thread_post_types())) {
            return 'thread';
        }
        if (in_array($type, ph_get_project_post_types())) {
            return 'project';
        }
    }
}
