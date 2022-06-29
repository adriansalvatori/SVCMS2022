<?php


namespace AmeliaBooking\Infrastructure\Services\LessonSpace;

use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Event\EventPeriod;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventPeriodsRepository;
use AmeliaBooking\Infrastructure\Routes\Booking\Event\Event;

class LessonSpaceService
{
    /**
     * @var SettingsService $settingsService
     */
    private $settingsService;

    /** @var Container $container */
    private $container;

    /**
     * LessonSpaceService constructor.
     *
     * @param Container $container
     * @param SettingsService $settingsService
     */
    public function __construct(Container $container, SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->container       = $container;
    }

    /**
     * @param mixed $appointment
     * @param int $entity
     * @param Collection $periods
     *
     * @throws QueryExecutionException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle($appointment, $entity, $periods = null)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get("domain.booking.appointment.repository");

        /** @var EventPeriodsRepository $eventPeriodsRepository */
        $eventPeriodsRepository = $this->container->get('domain.booking.event.period.repository');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get('application.placeholder.' . $entity . '.service');

        $lessonSpaceApiKey  = $this->settingsService->getSetting('lessonSpace', 'apiKey');
        $lessonSpaceEnabled = $this->settingsService->getSetting('lessonSpace', 'enabled');

        $enabledForEntity = $this->settingsService
            ->getEntitySettings($entity === Entities::APPOINTMENT ? $appointment->getService()->getSettings() : $appointment->getSettings())
            ->getLessonSpaceSettings()
            ->getEnabled();

        if (!$lessonSpaceEnabled || empty($lessonSpaceApiKey) || !$enabledForEntity) {
            return;
        }
        $placeholderData = $placeholderService->getPlaceholdersData($appointment->toArray());

        if ($entity === Entities::APPOINTMENT) {
            $lessonSpaceName = $this->settingsService->getSetting('lessonSpace', 'spaceNameAppointments');
            $lessonSpaceName = $placeholderService->applyPlaceholders($lessonSpaceName, $placeholderData);

            $createForPending  = $this->settingsService->getSetting('lessonSpace', 'pendingAppointments');
            $shouldCreateSpace = $appointment->getStatus()->getValue() === BookingStatus::APPROVED ||
                (
                    $appointment->getStatus()->getValue() === BookingStatus::PENDING &&
                    $createForPending
                );
            if ($shouldCreateSpace && !$appointment->getLessonSpace()) {
                $resultArray = $this->execute($appointment->getId()->getValue(), $lessonSpaceName, $lessonSpaceApiKey);
                if (isset($resultArray['client_url'])) {
                    $clientUrl = $this->getInviteUrl($resultArray);
                    $appointment->setLessonSpace($clientUrl);

                    $appointmentRepository->updateFieldById(
                        $appointment->getId()->getValue(),
                        $clientUrl,
                        'lessonSpace'
                    );
                }
            }
        } else if ($entity === Entities::EVENT) {
            $lessonSpaceName = $this->settingsService->getSetting('lessonSpace', 'spaceNameEvents');
            $lessonSpaceName = $placeholderService->applyPlaceholders($lessonSpaceName, $placeholderData);

            $eventPeriodsRepository->beginTransaction();

            /** @var EventPeriod $period */
            foreach ($periods->getItems() as $period) {
                if ($period->getLessonSpace()) {
                    continue;
                }
                $resultArray = $this->execute($period->getId()->getValue(), $lessonSpaceName, $lessonSpaceApiKey);
                if (isset($resultArray['client_url'])) {
                    $clientUrl = $this->getInviteUrl($resultArray);
                    $period->setLessonSpace($clientUrl);

                    $eventPeriodsRepository->updateFieldById(
                        $period->getId()->getValue(),
                        $clientUrl,
                        'lessonSpace'
                    );
                }
            }
            $eventPeriodsRepository->commit();
            $appointment->setPeriods($periods);
        }
    }


    /**
     * @param int $id
     * @param string $name
     * @param string $lessonSpaceApiKey
     *
     * @return mixed
     *
     */
    public function execute($id, $name, $lessonSpaceApiKey)
    {
        $requestUrl = 'https://api.thelessonspace.com/v2/spaces/launch/';

        $ch = curl_init($requestUrl);

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Authorization: Organisation ' . $lessonSpaceApiKey,
                'Content-Type: application/json'
            ]
        );
        $data = [
            'id'    =>  $id,
        ];
        if ($name) {
            $data['name'] = $name;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_FORCE_OBJECT));

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($result === false) {
            return ['message' => curl_error($ch)];
        }

        curl_close($ch);

        return json_decode($result, true);
    }

    private function getInviteUrl($resultArray)
    {
        $inviteUrl = '';
        if (preg_match("/inviteUrl=([^&]*)/", $resultArray['client_url'], $match)) {
            $inviteUrl = $match[1];
        }

        return $inviteUrl ?: $resultArray['client_url'];
    }
}
