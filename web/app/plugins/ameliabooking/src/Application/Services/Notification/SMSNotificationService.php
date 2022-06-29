<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Notification;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Notification\Notification;
use AmeliaBooking\Domain\Entity\Notification\NotificationLog;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\NotificationStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationLogRepository;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationSMSHistoryRepository;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class SMSNotificationService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
class SMSNotificationService extends AbstractNotificationService
{
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array        $appointmentArray
     * @param Notification $notification
     * @param bool         $logNotification
     * @param int|null     $bookingKey
     *
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function sendNotification(
        $appointmentArray,
        $notification,
        $logNotification,
        $bookingKey = null
    ) {
        /** @var \AmeliaBooking\Application\Services\Settings\SettingsService $settingsAS */
        $settingsAS = $this->container->get('application.settings.service');
        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.{$appointmentArray['type']}.service");
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $data = $placeholderService->getPlaceholdersData(
            $appointmentArray,
            $bookingKey,
            'sms'
        );

        $isCustomerPackage = isset($appointmentArray['isForCustomer']) && $appointmentArray['isForCustomer'];

        if ($appointmentArray['type'] === Entities::PACKAGE) {
            if (!empty($appointmentArray['recurring'][0]['booking']['info']) && $isCustomerPackage) {
                $info = $appointmentArray['recurring'][0]['booking']['info'];

                $infoArray = json_decode($info, true);

                if (!empty($infoArray['phone'])) {
                    $appointmentArray['customer']['phone'] = $infoArray['phone'];
                }
            } else {
                $info = $isCustomerPackage ? json_encode($appointmentArray['customer']) : null;
            }
        } else {
            $info = $bookingKey !== null ? $appointmentArray['bookings'][$bookingKey]['info'] : null;
        }

        $notificationContent = $helperService->getBookingTranslation(
            $info,
            $notification->getTranslations() ? $notification->getTranslations()->getValue() : null,
            'content'
        ) ?: $notification->getContent()->getValue();

        $text = $placeholderService->applyPlaceholders($notificationContent, $data);

        $users = $this->getUsersInfo(
            $notification->getSendTo()->getValue(),
            $appointmentArray,
            $bookingKey,
            $data
        );

        foreach ($users as $user) {
            if ($user['phone']) {
                $reParsedData = $appointmentArray['type'] === Entities::PACKAGE &&
                !(isset($appointmentArray['isForCustomer']) && $appointmentArray['isForCustomer']) ?
                    $placeholderService->reParseContentForProvider(
                        $appointmentArray,
                        '',
                        $text,
                        $user['id']
                    ) : [
                        'body' => $text,
                    ];

                try {
                    $this->saveAndSend(
                        $notification,
                        $user,
                        $appointmentArray,
                        $reParsedData,
                        $logNotification,
                        $user['phone']
                    );

                    $additionalPhoneNumbers = $settingsAS->getBccSms();

                    foreach ($additionalPhoneNumbers as $phoneNumber) {
                        $this->saveAndSend(
                            $notification,
                            null,
                            $appointmentArray,
                            $reParsedData,
                            $logNotification,
                            $phoneNumber
                        );
                    }
                } catch (QueryExecutionException $e) {
                } catch (ContainerException $e) {
                }
            }
        }
    }

    /**
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function sendUndeliveredNotifications()
    {
        /** @var NotificationLogRepository $notificationLogRepository */
        $notificationLogRepository = $this->container->get('domain.notificationLog.repository');

        /** @var NotificationSMSHistoryRepository $notificationsSMSHistoryRepo */
        $notificationsSMSHistoryRepo = $this->container->get('domain.notificationSMSHistory.repository');

        /** @var Collection $undeliveredNotifications */
        $undeliveredNotifications = $notificationLogRepository->getUndeliveredNotifications('sms');

        /** @var SMSAPIService $smsApiService */
        $smsApiService = $this->container->get('application.smsApi.service');

        /** @var NotificationLog $undeliveredNotification */
        foreach ($undeliveredNotifications->getItems() as $undeliveredNotification) {
            try {
                $data = json_decode($undeliveredNotification->getData()->getValue(), true);

                if ($history = $notificationsSMSHistoryRepo->getById($data['historyId'])) {
                    $apiResponse = $smsApiService->send(
                        $history['phone'],
                        $data['body'],
                        AMELIA_ACTION_URL . '/notifications/sms/history/' . $data['historyId']
                    );

                    if ($apiResponse->status === 'OK') {
                        $this->updateSmsHistory($data['historyId'], $apiResponse);

                        $notificationLogRepository->updateFieldById(
                            $undeliveredNotification->getId()->getValue(),
                            1,
                            'sent'
                        );
                    }
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function sendBirthdayGreetingNotifications()
    {
        /** @var Collection $notifications */
        $notifications = $this->getByNameAndType('customer_birthday_greeting', $this->type);

        /** @var Notification $notification */
        $notification = $notifications->getItem($notifications->keys()[0]);

        // Check if notification is enabled and it is time to send notification
        if ($notification->getStatus()->getValue() === NotificationStatus::ENABLED &&
            $notification->getTime() &&
            DateTimeService::getNowDateTimeObject() >=
            DateTimeService::getCustomDateTimeObject($notification->getTime()->getValue())
        ) {
            /** @var NotificationLogRepository $notificationLogRepo */
            $notificationLogRepo = $this->container->get('domain.notificationLog.repository');
            /** @var NotificationSMSHistoryRepository $notificationsSMSHistoryRepo */
            $notificationsSMSHistoryRepo = $this->container->get('domain.notificationSMSHistory.repository');
            /** @var SMSAPIService $smsApiService */
            $smsApiService = $this->container->get('application.smsApi.service');
            /** @var PlaceholderService $placeholderService */
            $placeholderService = $this->container->get('application.placeholder.appointment.service');
            /** @var SettingsService $settingsService */
            $settingsService = $this->container->get('domain.settings.service');

            $customers = $notificationLogRepo->getBirthdayCustomers($this->type);

            $companyData = $placeholderService->getCompanyData();

            $customersArray = $customers->toArray();

            foreach ($customersArray as $bookingKey => $customerArray) {
                $data = [
                    'customer_email'      => $customerArray['email'],
                    'customer_first_name' => $customerArray['firstName'],
                    'customer_last_name'  => $customerArray['lastName'],
                    'customer_full_name'  => $customerArray['firstName'] . ' ' . $customerArray['lastName'],
                    'customer_phone'      => $customerArray['phone'],
                    'customer_id'         => $customerArray['id'],
                ];

                /** @noinspection AdditionOperationOnArraysInspection */
                $data += $companyData;

                $text = $placeholderService->applyPlaceholders(
                    $notification->getContent()->getValue(),
                    $data
                );

                if ($data['customer_phone']) {
                    try {
                        $historyId = $notificationsSMSHistoryRepo->add(
                            [
                                'notificationId' => $notification->getId()->getValue(),
                                'userId'         => $data['customer_id'],
                                'text'           => $text,
                                'phone'          => $data['customer_phone'],
                                'alphaSenderId'  => $settingsService->getSetting('notifications', 'smsAlphaSenderId'),
                            ]
                        );

                        $apiResponse = $smsApiService->send(
                            $data['customer_phone'],
                            $text,
                            AMELIA_ACTION_URL . '/notifications/sms/history/' . $historyId
                        );

                        if ($apiResponse->status === 'OK') {
                            $this->updateSmsHistory($historyId, $apiResponse);

                            $notificationLogRepo->add(
                                $notification,
                                $data['customer_id']
                            );
                        }
                    } catch (QueryExecutionException $e) {
                    }
                }
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param Notification $notification
     * @param array $user
     * @param array $appointmentArray
     * @param array $reParsedData
     * @param bool $logNotification
     * @param string $sendTo
     *
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    private function saveAndSend($notification, $user, $appointmentArray, $reParsedData, $logNotification, $sendTo)
    {

        /** @var NotificationLogRepository $notificationsLogRepository */
        $notificationsLogRepository = $this->container->get('domain.notificationLog.repository');
        /** @var NotificationSMSHistoryRepository $notificationsSMSHistoryRepo */
        $notificationsSMSHistoryRepo = $this->container->get('domain.notificationSMSHistory.repository');
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');
        /** @var SMSAPIService $smsApiService */
        $smsApiService = $this->container->get('application.smsApi.service');

        if ($user && !empty($appointmentArray['isRetry'])) {
            /** @var Collection $sentNotifications */
            $sentNotifications = $notificationsLogRepository->getSentNotificationsByUserAndEntity(
                $user['id'],
                'sms',
                $appointmentArray['type'],
                $appointmentArray['type'] === Entities::PACKAGE ?
                    $appointmentArray['packageCustomerId'] : $appointmentArray['id']
            );

            if ($sentNotifications->length()) {
                return;
            }
        }

        $historyId = $notificationsSMSHistoryRepo->add(
            [
                'notificationId'    => $notification->getId()->getValue(),
                'userId'            => $user ? $user['id'] : null,
                'appointmentId'     =>
                    $appointmentArray['type'] === Entities::APPOINTMENT ? $appointmentArray['id'] : null,
                'eventId'           =>
                    $appointmentArray['type'] === Entities::EVENT ? $appointmentArray['id'] : null,
                'packageCustomerId' => $appointmentArray['type'] === Entities::PACKAGE ?
                    $appointmentArray['packageCustomerId'] : null,
                'text'              => $reParsedData['body'],
                'phone'             => $user ? $user['phone'] : $sendTo,
                'alphaSenderId'     => $settingsService->getSetting('notifications', 'smsAlphaSenderId')
            ]
        );

        $logNotificationId = null;

        if ($logNotification) {
            $logNotificationId = $notificationsLogRepository->add(
                $notification,
                $user ? $user['id'] : null,
                $appointmentArray['type'] === Entities::APPOINTMENT ? $appointmentArray['id'] : null,
                $appointmentArray['type'] === Entities::EVENT ? $appointmentArray['id'] : null,
                $appointmentArray['type'] === Entities::PACKAGE ? $appointmentArray['packageCustomerId'] : null,
                json_encode(
                    [
                        'subject'   => '',
                        'body'      => $reParsedData['body'],
                        'icsFiles'  => [],
                        'historyId' => $historyId,
                    ]
                )
            );
        }

        $apiResponse = $smsApiService->send(
            $sendTo,
            $reParsedData['body'],
            AMELIA_ACTION_URL . '/notifications/sms/history/' . $historyId
        );

        if ($apiResponse->status === 'OK') {
            $this->updateSmsHistory($historyId, $apiResponse);

            if ($logNotificationId) {
                $notificationsLogRepository->updateFieldById((int)$logNotificationId, 1, 'sent');
            }
        }
    }

    /**
     * @param int   $historyId
     * @param mixed $apiResponse
     * @throws QueryExecutionException
     */
    public function updateSmsHistory($historyId, $apiResponse)
    {
        /** @var NotificationSMSHistoryRepository $notificationsSMSHistoryRepo */
        $notificationsSMSHistoryRepo = $this->container->get('domain.notificationSMSHistory.repository');

        $notificationsSMSHistoryRepo->update(
            $historyId,
            [
                'logId'    => $apiResponse->message->logId,
                'status'   => $apiResponse->message->status,
                'price'    => $apiResponse->message->price,
                'dateTime' => DateTimeService::getNowDateTimeInUtc(),
                'segments' => $apiResponse->message->segments
            ]
        );
    }
}
