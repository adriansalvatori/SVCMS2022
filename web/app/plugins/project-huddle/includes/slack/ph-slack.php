<?php 

    if (!defined('ABSPATH')) {
        exit;
    }

    /**
     * Send slack notification when new comment is added on Mockup/Website
     *
     * @since 4.3.0
     *
     * @param  $comment_ID Comment id
     * @param  $comment_text "Comment Text" textbox text
     * @param  $link Project Link
     * @param  $client_name Client name
     * @param  $project_name Project Name
     * @param  $client_url Client avator URL
     * @param  $comment_content Comment user added on the site
     *
     */
	function push_incoming_webhook( $comment_ID, $comment_text, $link, $client_name, $project_name, $client_url, $comment_content ) {

        $comment_post_ID = get_comment_meta( $comment_ID, 'project_id', true );
        
        $specific_webhook_url = get_post_meta($comment_post_ID, 'webhook', true);
        
        $webhook_url = get_option( 'ph_slack_webhook_default' );

        if( isset( $specific_webhook_url ) && '' !== $specific_webhook_url ) {
            $webhook_url = $specific_webhook_url;
        } 
        
        $is_private_comment_allowed = get_option( 'ph_private_comment_check' );

        $comment_meta = get_comment_meta( $comment_ID, 'is_private', true);
        $check_private = filter_var( $comment_meta, FILTER_VALIDATE_BOOLEAN );
        $text = '';

        if( $check_private ) {
            $text = apply_filters( 'ph_slack_private_string', __( 'This is Private comment :lock:', 'project-huddle' ) );
        }

        $body = array(
            "attachments" => array(
                array(
                    "mrkdwn_in"=> ["text"],
                    "color"=> "#36a64f",
                    "pretext" => $text,
                    "author_name"=> $client_name,
                    "author_link"=> $client_url,
                    "author_icon"=> $client_url,
                    "text"=> $comment_text . ' :speech_balloon:',
                    "fields"=> array( 
                        array( 
                            "title"=> __( "Comment", 'project-huddle' ),
                            "value"=> strip_tags($comment_content),
                            "short"=> false
                        ),
                        array( 
                            "value"=> "*<$link|View Comment>*",
                            "short"=> true
                        ),
                    ),
                ),
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
?>