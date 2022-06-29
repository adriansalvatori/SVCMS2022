<?php

/**
 * Wrapper for mail function to set activity template
 */

namespace PH\Controllers\Mail\Mailers;

if (!defined('ABSPATH')) exit;

use PH\Controllers\Mail\Builders\ActivityMailBuilder;

class ActivityMailer extends Mailer
{
    protected $template_name = 'activity-email';

    public function __construct($name = 'activity')
    {
        parent::__construct();

        // set template and variables for this email type
        $this->template = ph_locate_template("email/{$this->template_name}.php");
        $this->subject = sprintf(__('Latest updates from %s.', 'project-huddle'), get_bloginfo());
        $this->variables['message'] = $this->defaultMessage();
        $this->variables['title'] = $this->defaultTitle();
        $this->variables['avatar'] = '';
    }

    /**
     * Is it a manual email or automatic?
     *
     * @param boolean $is_manual
     * @return void
     */
    public function manual($is_manual)
    {
        $this->variables['manual'] = $is_manual;
        if ($is_manual && !empty($this->variables['message'])) {
            $this->variables['avatar'] = $this->avatarAndName(get_current_user_id());
        }
        return $this;
    }

    public function activity($activity)
    {
        $this->variables['sections'] = (new ActivityMailBuilder($activity))->html();
        if (!$this->variables['sections']) {
            $this->send = false;
        }
        return $this;
    }

    /**
     * Sets a message for the email
     *
     * @param String $message
     * @return void
     */
    public function message($message)
    {
        $this->variables['message'] = wp_kses_post(stripslashes(wpautop($message)));
        if (!$this->variables['message']) {
            $this->variables['avatar'] = false;
        }
        return $this;
    }

    /**
     * Sets a title in the email
     *
     * @param String $title
     * @return void
     */
    public function title($title)
    {
        $this->variables['title'] = wp_kses_post($title);
        return $this;
    }

    /**
     * Set the default title
     *
     * @return void
     */
    protected function defaultTitle()
    {
        return __('Latest Activity', 'project-huddle');
    }

    /**
     * A default message in case one is not set
     *
     * @return void
     */
    protected function defaultMessage()
    {
        $interval = PH()->activity_emails->get_interval();

        if ($interval > 60) {
            $hours = $interval / 60;
            $time = sprintf(_n('%d hour', '%d hours', $hours, 'project-huddle'), $hours);
        } else {
            $time = sprintf(__('%d minutes', 'project-huddle'), $interval);
        }

        return $this->message = wpautop(sprintf(__('Here are some updates from the projects you\'re following for the last %s.', 'project-huddle'), $time));
    }
}
