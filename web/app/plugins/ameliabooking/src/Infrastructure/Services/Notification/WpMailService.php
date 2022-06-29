<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Notification;

use AmeliaBooking\Domain\Services\Notification\AbstractMailService;
use AmeliaBooking\Domain\Services\Notification\MailServiceInterface;

/**
 * Class WpMailService
 */
class WpMailService extends AbstractMailService implements MailServiceInterface
{

    /**
     * WpMailService constructor.
     *
     * @param        $from
     * @param        $fromName
     */
    public function __construct($from, $fromName)
    {
        parent::__construct($from, $fromName);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param       $to
     * @param       $subject
     * @param       $body
     * @param array $bccEmails
     * @param array $attachments
     *
     * @return mixed|void
     * @SuppressWarnings(PHPMD)
     */

    public function send($to, $subject, $body, $bccEmails = [], $attachments = [])
    {
        $content = ['Content-Type: text/html; charset=UTF-8','From: '  . $this->fromName . ' <' . $this->from . '>'];

        if ($bccEmails) {
            $content[] = 'Bcc:' . implode(', ', $bccEmails);
        }

        $attachmentsLocations = [];

        foreach ($attachments as $attachment) {
            if (!empty($attachment['content']) &&
                ($tmpFile = tempnam(sys_get_temp_dir(), 'cal_')) !== false &&
                file_put_contents($tmpFile, $attachment['content']) !== false &&
                @rename($tmpFile, $tmpFile .= '.ics') !== false
            ) {
                $attachmentsLocations[] = $tmpFile;
            }
        }

        wp_mail($to, $subject, $body, $content, $attachmentsLocations);
    }
}
