<?php

namespace PH\Providers\Mail;

use PH\Support\Providers\EventServiceProvider as ServiceProvider;

class MailEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // scheduled mail
        'ph_activity_summary_email' => [
            '\PH\Listeners\Mail\SendActivityMail'
        ],
        'ph_daily_summary_email' => [
            '\PH\Listeners\Mail\SendDailyMail'
        ],
        'ph_weekly_summary_email' => [
            '\PH\Listeners\Mail\SendWeeklyMail'
        ],

        // immediate mail
        'ph_mention_user' => [
            [
                'class' => '\PH\Listeners\Mail\SendMentionMail',
                'args' => 3
            ]
        ],
        'ph_project_publish_comment_after_mentions' => [
            [
                'class' => '\PH\Listeners\Mail\SendCommentMail',
                'args' => 3
            ]
        ],

        // attributes during create
        'ph_mockup_rest_create_thread_attribute' => [
            [
                'class' => '\PH\Listeners\Mail\SendAssignedMail',
                'args' => 4
            ]
        ],
        'ph_website_rest_create_thread_attribute' => [
            [
                'class' => '\PH\Listeners\Mail\SendAssignedMail',
                'args' => 4
            ]
        ],

        // attributes during update
        'ph_mockup_rest_update_thread_attribute' => [
            [
                'class' => '\PH\Listeners\Mail\SendResolveMail',
                'args' => 4
            ],
            [
                'class' => '\PH\Listeners\Mail\SendAssignedMail',
                'args' => 4
            ]
        ],
        'ph_website_rest_update_thread_attribute' => [
            [
                'class' => '\PH\Listeners\Mail\SendResolveMail',
                'args' => 4
            ],
            [
                'class' => '\PH\Listeners\Mail\SendAssignedMail',
                'args' => 4
            ],
        ],

        'ph_members_added' => [
            [
                'class' => '\PH\Listeners\Mail\SendUserAddedMail',
                'args' => 2
            ]
        ],

        'ph_email_share_post' => [
            [
                'class' => '\PH\Listeners\Mail\SendShareMail',
                'args' => 4
            ]
        ],

        'ph_sent_user_password_reset' => [
            [
                'class' => '\PH\Listeners\Mail\SendPasswordResetMail',
                'args' => 2
            ]
        ],

        'ph_item_approval' => [
            [
                'class' => '\PH\Listeners\Mail\SendItemApprovedMail',
                'args' => 2
            ]
        ],

        'ph_project_approval' => [
            [
                'class' => '\PH\Listeners\Mail\SendProjectApprovedMail',
                'args' => 2
            ]
        ],
        'ph_batch_activity_email' => [
            '\PH\Listeners\Mail\SendBatchActivityMail'
        ]
    ];
}
