<?php

/**
 * PH\Controlers\Mail\Mailer
 *
 * A simple class for creating and sending emails
 * with template tags
 *
 * Based off:
 * https://github.com/anthonybudd/WP_Mail
 * @author     AnthonyBudd <anthonybudd94@gmail.com>
 */

namespace PH\Controllers\Mail\Mailers;

use Exception;
use PH\Support\Mail\Mail;

use PH\Models\User;


if (!defined('ABSPATH')) exit;

class Mailer extends Mail
{

    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $subject = '';
    protected $from = '';
    protected $headers = [];
    protected $attachments = [];

    protected $variables = [];
    protected $template = false;
    protected $sendAsHTML = true;
    protected $name = 'generic'; // email name
    protected $user = null;
    protected $project_id = 0;
    protected $send = true;

    public function __construct($name = '', $post_id = 0)
    {
        // make sure we add access tokens no matter what
        global $access_token_override;
        $access_token_override = true;

        $this->name = $name ? $name : $this->name;;
        $this->project_id  = $post_id ? $post_id : $this->project_id;
    }

    public function user(User $user)
    {
        $this->user = $user;
        $this->to = [sanitize_email($user->user_email)];
        return $this;
    }

    /**
     * Set recipients
     *
     * @param  Array|String $to
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function to($to)
    {
        if (is_object($to) && (isset($to->user_email))) {
            $this->user = $to;
            $this->to = [sanitize_email($to->user_email)];
        } else if (is_array($to)) {
            $this->to = $to;
        } else if (is_string($to)) {
            $this->to = [$to];
        }

        if (!$this->to) {
            ph_log("Error when trying to set 'to' for {$this->name} email: " . print_r($to, 1));
        }

        $this->to = apply_filters("ph_{$this->name}_email_to", $this->to);
        return $this;
    }


    /**
     * Get recipients
     * @return Array $to
     */
    public function getTo()
    {
        return $this->to;
    }


    /**
     * Set Cc recipients
     *
     * @param  String|Array $cc
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function cc($cc)
    {
        if (is_array($cc)) {
            $this->cc = $cc;
        } else {
            $this->cc = [$cc];
        }

        $this->cc = apply_filters("ph_{$this->name}_email_cc", $this->cc);

        return $this;
    }


    /**
     * Get Cc recipients
     * @return Array $cc
     */
    public function getCc()
    {
        return $this->cc;
    }


    /**
     * Set Email Bcc recipients
     *
     * @param  String|Array $bcc
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function bcc($bcc)
    {
        if (is_array($bcc)) {
            $this->bcc = $bcc;
        } else {
            $this->bcc = [$bcc];
        }

        $this->bcc = apply_filters("ph_{$this->name}_email_bcc", $this->bcc);

        return $this;
    }


    /**
     * Set email Bcc recipients
     * @return Array $bcc
     */
    public function getBcc()
    {
        return $this->bcc;
    }


    /**
     * Set email Subject
     *
     * @param  String $subject
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function subject($subject, $variables = [])
    {
        preg_match_all('/\{\{\s*.+?\s*\}\}/', $subject, $matches);
        foreach ($matches[0] as $match) {
            $var = str_replace('{', '', str_replace('}', '', preg_replace('/\s+/', '', $match)));

            if (isset($this->variables[$var])) {
                $subject = str_replace($match, $this->variables[$var], $subject);
            }
        }

        $this->subject = apply_filters("ph_{$this->name}_email_subject", $subject);

        return $this;
    }


    /**
     * Retruns email subject
     * @return Array
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the from address for outgoing emails.
     * @return string
     */
    public function get_from_address()
    {
        $from_address = apply_filters('ph_email_from_address', get_option('ph_email_from_address', get_option('admin_email')));
        $from_address = apply_filters("ph_" . $this->name . "_from_address", $from_address);
        return sanitize_email($from_address);
    }

    /**
     * Get the from name for outgoing emails.
     * @return string
     */
    public function get_from_name()
    {
        $from_name = apply_filters('ph_email_from_name', get_option('ph_email_from_name', get_bloginfo('name')));
        $from_name = apply_filters("ph_{$this->name}_from_name", $from_name);
        return wp_specialchars_decode(esc_html($from_name), ENT_QUOTES);
    }

    /**
     * Set From header
     *
     * @param  String
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function from($from)
    {
        $this->from = "{$this->get_from_name()} <{$this->get_from_address()}>";

        return $this;
    }

    /**
     * Set the email's headers
     *
     * @param  String|Array $headers [description]
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function headers($headers)
    {
        if (is_array($headers)) {
            $this->headers = $headers;
        } else {
            $this->headers = [$headers];
        }

        $this->headers = apply_filters("ph_{$this->name}_headers", $headers);

        return $this;
    }


    /**
     * Retruns headers
     * @return Array
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * Returns email content type
     * @return String
     */
    public function HTMLFilter()
    {
        return 'text/html';
    }


    /**
     * Set email content type
     *
     * @param  Bool $html
     *
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function sendAsHTML($html)
    {
        $this->sendAsHTML = $html;

        return $this;
    }


    /**
     * Attach a file or array of files.
     * Filepaths must be absolute.
     *
     * @param  String|Array $path
     *
     * @throws Exception
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function attach($path)
    {
        if (is_array($path)) {
            $this->attachments = [];
            foreach ($path as $path_) {
                if (!file_exists($path_)) {
                    throw new Exception("Attachment not found at $path");
                } else {
                    $this->attachments[] = $path_;
                }
            }
        } else {
            if (!file_exists($path)) {
                throw new Exception("Attachment not found at $path");
            }
            $this->attachments = [$path];
        }

        return $this;
    }


    /**
     * Set the template file
     *
     * @param  string $template Path to HTML template
     * @param  array  $variables
     *
     * @throws Exception
     * @return PH\Controlers\Mail\Mailer $this
     */
    public function template($template, $variables = [])
    {
        if (!file_exists($template)) {
            throw new Exception('File not found');
        }

        if (is_array($variables)) {
            $this->variables = $variables;
        }

        $this->template = apply_filters("ph_{$this->name}_template", $template);

        return $this;
    }

    /**
     * Add variables
     *
     * @param array $variables
     * @return void
     */
    public function with($variables)
    {
        if (is_array($variables)) {
            $this->variables = apply_filters("ph_{$this->name}_template", $variables);
        }
    }

    /**
     * Renders the template
     * @return string
     */
    private function render()
    {
        // load template
        ob_start();
        include $this->template;
        $template = ob_get_clean();

        preg_match_all('/\{\{\s*.+?\s*\}\}/', $template, $matches);
        foreach ($matches[0] as $match) {
            $var = str_replace('{', '', str_replace('}', '', preg_replace('/\s+/', '', $match)));

            if (isset($this->variables[$var])) {
                $template = str_replace($match, $this->variables[$var], $template);
            }
        }

        return $template;
    }


    /**
     * Builds Email Headers
     * @return string email headers
     */
    private function buildHeaders()
    {
        $headers = '';

        $headers .= implode("\r\n", $this->headers) . "\r\n";

        foreach ($this->bcc as $bcc) {
            $headers .= sprintf("Bcc: %s \r\n", $bcc);
        }

        foreach ($this->cc as $cc) {
            $headers .= sprintf("Cc: %s \r\n", $cc);
        }

        if (!empty($this->from)) {
            $headers .= sprintf("From: %s \r\n", $this->from);
        }

        // add html header
        $headers .= "Content-Type: {$this->HTMLFilter()}; charset=utf-8\r\n";

        return $headers;
    }


    /**
     * Set the wp_mail_content_type filter, if necessary
     */
    private function beforeSend()
    {

        if (
            count($this->to) === 0
            && count($this->bcc) === 0
            && count($this->cc) === 0
        ) {
            throw new Exception('You must set at least 1 recipient');
        }

        if (empty($this->template)) {
            throw new Exception('You must set a template');
        }

        if ($this->sendAsHTML) {
            add_filter('wp_mail_content_type', array($this, 'HTMLFilter'));
        }
    }


    /**
     * Sends a rendered email using
     * WordPress's wp_mail() function
     * @return bool
     */
    public function send()
    {
        try {
            $this->beforeSend();
        } catch (Exception $e) {
            ph_log("Error when trying to send {$this->name} email: " . $e->getMessage());
            return;
        }

        // allow send cancellation
        if (!$this->send) {
            return;
        }

        // bail on suppressions
        if ($this->user && $this->name) {
            if ($this->project_id && $this->user->hasProjectSuppression($this->project_id)) {
                return;
            }

            // does the user have a suppression
            if (apply_filters("ph_disable_{$this->name}_emails", $this->user->isSuppressed($this->name), $this->user->ID)) {
                return;
            }
        }

        // filter to disable all email (optionally by post)
        if (apply_filters('ph_disable_email', false, $this->project_id, $this->to, $this->subject, $this->render())) {
            return false;
        }

        add_filter('wp_mail_from', array($this, 'get_from_address'));
        add_filter('wp_mail_from_name', array($this, 'get_from_name'));

        try {
            $send = wp_mail(
                $this->to,
                $this->subject,
                $this->render(),
                $this->buildHeaders(),
                $this->attachments
            );
        } catch (Exception $e) {
            ph_log("Error when trying to send {$this->name} email: " . $e->getMessage());
            return;
        }

        remove_filter('wp_mail_from', array($this, 'get_from_address'));
        remove_filter('wp_mail_from_name', array($this, 'get_from_name'));

        return $send;
    }
}
