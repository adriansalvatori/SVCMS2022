<?php

namespace PH\Controllers;

use PH\Models\Item;
use PH\Models\Project;
use PH\Models\Page;
use PH\Contracts\Model;

require_once PH_PLUGIN_DIR . 'includes/slack/ph-slack.php';

class ApprovalController
{
    
    public function __construct()
    {
        // set history
        add_action('ph_item_approval', [$this, 'saveHistory'], 10, 3);
        add_action('ph_project_approval', [$this, 'saveHistory'], 10, 3);

        // do triggers
        add_action('ph_set_approval', [$this, 'triggers'], 10, 3);

        // clear transients
        add_action('untrashed_post', [$this, 'clearProjectTransient']);
    }

    /**
     * Get approval status
     *
     * @param int $id
     * @return void
     */
    public function getStatus($id)
    {
        // backwards compat
        if ($approved = get_post_meta($id, 'approval', true)) {
            return (bool) $approved;
        }
        return (bool) get_post_meta($id, 'approved', true);
    }

    /**
     * Save Approval
     *
     * @param \App\Model $model
     * @param bool $approved
     * @return void
     */
    public function save($model, $approved)
    {
        
        $isNew = true;
        if (metadata_exists('post', $model->ID, 'approved') || metadata_exists('post', $model->ID, 'approval')) {
            $isNew = false;
        }

        $meta = update_post_meta($model->ID, 'approved', (bool) $approved);
        // $meta = update_post_meta($model->ID, 'approval', (bool) $approved); // legacy
        do_action('ph_set_approval', $model, (bool) $approved, $isNew);
        return $meta;
    }

    /**
     * Get approval history
     */
    public function getHistory($id, $args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'post_id'  => $id,
                'type__in' => array(
                    'ph_approval',
                )
            ]
        );
        return ph_get_comments($args);
    }

    /**
     * Save approval in history
     *
     * @param \PH\Model $model
     * @param bool $approved
     * @return void
     */
    public function saveHistory(Model $model, $approved, $isNew = false)
    {
        if ($isNew && !$approved) {
            return;
        }

        // needs a current user
        if (!$user = wp_get_current_user()) {
            return false;
        }

        // unsert approval comment
        return wp_insert_comment(
            array(
                'comment_post_ID'      => $model->ID,
                'comment_author'       => $user->display_name,
                'comment_author_email' => $user->user_email,
                'user_id'              => $user->ID,
                'comment_content'      => $approved ? 'approved' : 'unapproved',
                'comment_type'         => 'ph_approval',
                'comment_approved'     => 1, // force approval.
                'comment_meta'         => [
                    'approval' => (bool) $approved,
                ],
            )
        );
    }

    /**
     * Send correct triggers for mail, etc.
     *
     * @param bool $approved
     * @param int $id
     * @param bool $isNew Is this a new project
     * @param PH\Contracts\Model $model
     * @return void
     */
    public function triggers(Model $model, $approved, $isNew = false)
    {
        global $is_ph_batch;
       
        // get the type of the Project (mockup/website)
        $type = ( $model->projectType() == 'website' ) ? __('website', 'project-huddle') : __('mockup', 'project-hudde');

        $current_user = wp_get_current_user();
        // get current user.
        $client_name = ( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
        
        // get client site name.
        $project_name = ph_get_the_title($model->ID);

        $slack_terms = (bool)get_option( 'ph_slack_terms' );
        
        $project_id = get_post_meta($model->ID, 'parent_id', true);

        $client_url = get_avatar_url( $current_user, 32 );
      
        $project_link = $model->getAccessLink();
        
        $approval_option = get_option( 'ph_slack_project_approvals' );
        
        $approval_enabled = ( !empty( $approval_option ) && 'on' == $approval_option ) ? $approval_option : '';
        
        // if we're approving a project, do that!
        if (is_a($model, Project::class)) {
           
            do_action('ph_project_approval', $model, $approved, $isNew);
             // trigger when entire project(whole) is approved!
            if( $slack_terms && $approval_enabled ) {
                $project_name = ph_get_the_title($model->ID);
                $comment_text_ = get_option( 'ph_project_approval_text' );
                $comment_text = $this->get_shortcode_text(  $comment_text_, $client_name, $project_name, $approved, $type );
                $this->push_incoming_webhook( $comment_text, $project_name, $client_name, $approved, $client_url, $project_link, $project_id, $type );              
            }
            
            return;
        }

        // make sure we're not running other batches
        static $batch_running;
        if ($batch_running) {
            return;
        }

        // if we're batch approving images, do a project approval action
        if ($is_ph_batch) {
            $batch_running = true;
            do_action('ph_project_approval', $model->project(), $approved, $isNew);
            // trigger when image approval.
            if( $slack_terms && $approval_enabled ) {
                $project_name = ph_get_the_title($model->ID);
                $client_name = ( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
                $comment_text_ = get_option( 'ph_project_approval_text' );
                $comment_text = $this->get_shortcode_text(  $comment_text_, $client_name, $project_name, $approved, $type );
                $this->push_incoming_webhook( $comment_text, $project_name, $client_name, $approved, $client_url, $project_link, $project_id, $type );
            }
        
        } else {
            // if all siblings are approved, trigger project approval
            if ($model->siblingsApproved()) {
                do_action('ph_project_approval', $model->project(), $approved, $isNew);
                // trigger when single page is approved! -- earlier
                if( $slack_terms && $approval_enabled ) {
                    $project_name = ph_get_the_title($model->ID);
                    $client_name = ( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
                    $comment_text_ = get_option( 'ph_project_approval_text' );
                    $comment_text = $this->get_shortcode_text(  $comment_text_, $client_name, $project_name, $approved, $type );
                    // trigger (only for)single mockup is approved. -- newly 
                    $this->push_incoming_webhook( $comment_text, $project_name, $client_name, $approved, $client_url, $project_link, $project_id, $type );
                }
                
            } else { 
                do_action('ph_item_approval', $model, $approved, $isNew);
                if( $slack_terms && $approval_enabled ) {
                    if ($isNew && !$approved) {
                        return;
                    }
                    
                    $project_name = ph_get_the_title($model->ID);
                    $client_name = ( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
                    $comment_text_ = get_option( 'ph_project_approval_text' );
                    $comment_text = $this->get_shortcode_text( $comment_text_, $client_name, $project_name, $approved, $type );
                    // trigger single page of mockup and website approved/unapproved
                    $this->push_incoming_webhook( $comment_text, $project_name, $client_name, $approved, $client_url, $project_link, $project_id,$type );
                }
            }
        }
    }

    function get_shortcode_text( $textbox_comment, $client_name, $project_title, $approved, $type ) {

        $find = array( '/\{ph_commenter_name\}/','/\{ph_project_type\}/' ,'/\{ph_project_name\}/', '/\{ph_action_status\}/');
        $status = $approved ? __( 'approved', 'project-huddle' ) : __( 'unapproved', 'project-huddle' );

        $comment_text = '';

        $replacement = array( $client_name, $type ,$project_title, $status );

        if( isset( $client_name ) || isset( $project_title ) || isset( $status ) ) {
            
            $comment_text = preg_replace( $find, $replacement, $textbox_comment );
            
        }
        
        return $comment_text;
    }

    /**
     * Send slack notification when approved/unapproved of Mockup/Website/Page
     *
     * @since 4.3.0
     *
     * @param  $comment Comment
     * @param  $comment_text "Comment Text" textbox text
     * @param  $project_name Project Name
     * @param  $client_name Client name
     * @param  $approved approved/unapproved status
     * @param  $client_url Client avator URL
     * @param  $project_link Project Link
     * @param  $project_id Id for spearate webhook URL
     *
     */
    function push_incoming_webhook( $comment_text, $project_name, $client_name, $approved, $client_url, $project_link, $project_id, $type ) {

        $status = $approved ? __( 'approved', 'project-huddle' ) : __( 'unapproved', 'project-huddle' );
        $color = $approved ? "#36a64f" : "#e44d50";
        
        $status_emoji = $approved ? " :white_check_mark: " : " :x: ";

        $specific_webhook_url = get_post_meta( $project_id, 'webhook', true);
        
        $webhook_url = get_option( 'ph_slack_webhook_default' );

        if( isset( $specific_webhook_url ) && '' !== $specific_webhook_url ) {
            $webhook_url = $specific_webhook_url;
        } 
        $project_type_check_string = sprintf( __( "View %s", 'project_huddle' ),  ucwords($type) );
        $body = array(
            "attachments"=> array(
                array(
                    "color"=> $color,
                    "author_name"=> $client_name,
                    "author_link"=> $client_url,
                    "author_icon"=> $client_url,
                    "text"=> $comment_text . $status_emoji,
                    "fields"=> array(
                        array(
                            "title"=> sprintf( __( "%s Name", 'project-huddle' ), ucwords($type) ),
                            "value"=> $project_name,
                            "short"=> false
                        ),
                        array(
                            "value"=> "*<$project_link|$project_type_check_string>*",
                            "short"=> false
                        ),
                    ),
                )
            )
        );
        
        $result = wp_remote_post( $webhook_url, array(
            'headers' => array(
                'content-type' => 'application/json',
            ),
            'body' => wp_json_encode( $body ),
        ) );

        return $result;

    }

    /**
     * Are it's siblings approved too?
     *
     * @param int $parent_id
     * @param string $project_type
     * @return void
     */
    public function siblingsApproved($parent_id, $project_type)
    {
        $all_approved = false;
        $approval_status = ph_get_items_approval_status($parent_id, $project_type);

        if (!empty($approval_status)) {
            $all_approved = $approval_status['total'] == $approval_status['approved'];
        }

        return $all_approved;
    }

    public function clearProjectTransient($model)
    {
        delete_transient("ph_approved_status_" . $model);
    }
}
