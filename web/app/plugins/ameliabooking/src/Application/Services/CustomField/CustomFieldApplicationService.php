<?php

namespace AmeliaBooking\Application\Services\CustomField;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\ForbiddenFileUploadException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\CustomField\CustomField;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\Token;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldEventRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldOptionRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldServiceRepository;

/**
 * Class CustomFieldApplicationService
 *
 * @package AmeliaBooking\Application\Services\CustomField
 */
class CustomFieldApplicationService
{
    private $container;

    public static $allowedUploadedFileExtensions = [
        '.jpg'  => 'image/jpeg',
        '.jpeg' => 'image/jpeg',
        '.png'  => 'image/png',

        '.mp3'  => 'audio/mpeg',
        '.mpeg' => 'video/mpeg',
        '.mp4'  => 'video/mp4',

        '.txt'  => 'text/plain',
        '.csv'  => 'text/plain',
        '.xls'  => 'application/vnd.ms-excel',
        '.pdf'  => 'application/pdf',
        '.doc'  => 'application/msword',
    ];

    /**
     * CustomFieldApplicationService constructor.
     *
     * @param Container $container
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param CustomField $customField
     *
     * @return boolean
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function delete($customField)
    {
        /** @var CouponRepository $couponRepository */
        $customFieldRepository = $this->container->get('domain.customField.repository');

        /** @var CustomFieldServiceRepository $customFieldServiceRepository */
        $customFieldServiceRepository = $this->container->get('domain.customFieldService.repository');

        /** @var CustomFieldEventRepository $customFieldEventRepository */
        $customFieldEventRepository = $this->container->get('domain.customFieldEvent.repository');

        /** @var CustomFieldOptionRepository $customFieldOptionRepository */
        $customFieldOptionRepository = $this->container->get('domain.customFieldOption.repository');

        return
            $customFieldServiceRepository->deleteByEntityId($customField->getId()->getValue(), 'customFieldId') &&
            $customFieldEventRepository->deleteByEntityId($customField->getId()->getValue(), 'customFieldId') &&
            $customFieldOptionRepository->deleteByEntityId($customField->getId()->getValue(), 'customFieldId') &&
            $customFieldRepository->delete($customField->getId()->getValue());
    }

    /**
     * @param array $customFields
     *
     * @return array
     */
    public function processCustomFields(&$customFields)
    {
        $uploadedFilesInfo = [];

        foreach ($customFields as $customFieldId => $customField) {
            if ($customField['type'] === 'file' && isset($customFields[$customFieldId]['value'])) {
                foreach ((array)$customFields[$customFieldId]['value'] as $index => $data) {
                    if (isset($_FILES['files']['tmp_name'][$customFieldId][$index])) {
                        $fileExtension = pathinfo(
                            $_FILES['files']['name'][$customFieldId][$index],
                            PATHINFO_EXTENSION
                        );

                        if (!array_key_exists('.' . strtolower($fileExtension), self::$allowedUploadedFileExtensions)) {
                            continue;
                        }

                        $token = new Token();

                        $fileName = $token->getValue() . '.' . $fileExtension;

                        $customFields[$customFieldId]['value'][$index]['fileName'] = $fileName;

                        $uploadedFilesInfo[$customFieldId]['value'][$index] = [
                            'tmpName'  => $_FILES['files']['tmp_name'][$customFieldId][$index],
                            'fileName' => $fileName
                        ];
                    }
                }
            }

            if (!array_key_exists('value', $customFields[$customFieldId]) &&
                $customFields[$customFieldId]['type'] === 'checkbox'
            ) {
                $customFields[$customFieldId]['value'] = [];
            }
        }

        return $uploadedFilesInfo;
    }

    /**
     * @param int    $bookingId
     * @param array  $uploadedCustomFieldFilesNames
     * @param string $folder
     * @param string $copy
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws ForbiddenFileUploadException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function saveUploadedFiles($bookingId, $uploadedCustomFieldFilesNames, $folder, $copy)
    {
        $uploadPath = $this->getUploadsPath() . $folder;

        if ($uploadedCustomFieldFilesNames) {
            !is_dir($uploadPath) && !mkdir($uploadPath, 0755, true) && !is_dir($uploadPath);

            if (!is_writable($uploadPath) || !is_dir($uploadPath)) {
                throw new ForbiddenFileUploadException('Error While Uploading File');
            }

            if (!file_exists("$uploadPath/index.html")) {
                file_put_contents("$uploadPath/index.html", '');
            }
        }

        foreach ($uploadedCustomFieldFilesNames as $customFieldId => $customField) {
            foreach ((array)$uploadedCustomFieldFilesNames[$customFieldId]['value'] as $index => $data) {
                $fileExtension = pathinfo($data['fileName'], PATHINFO_EXTENSION);

                if (!array_key_exists('.' . strtolower($fileExtension), self::$allowedUploadedFileExtensions)) {
                    continue;
                }

                if (is_dir($uploadPath) && is_writable($uploadPath)) {
                    if ($copy) {
                        copy($data['tmpName'], "{$uploadPath}/{$bookingId}_{$data['fileName']}");
                    } else {
                        rename($data['tmpName'], "{$uploadPath}/{$bookingId}_{$data['fileName']}");
                    }

                    $uploadedCustomFieldFilesNames[$customFieldId]['value'][$index]['tmpName'] =
                        "{$uploadPath}/{$bookingId}_{$data['fileName']}";
                }
            }
        }

        return $uploadedCustomFieldFilesNames;
    }

    /**
     * @param Collection $bookings
     * @param Collection $oldBookings
     *
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function deleteUploadedFilesForDeletedBookings($bookings, $oldBookings)
    {
        $newBookingIds = [];

        /** @var CustomerBooking $booking */
        foreach ($bookings->getItems() as $booking) {
            $newBookingIds[] = $booking->getId()->getValue();
        }

        $deletedBookingIds = array_diff($oldBookings->keys(), $newBookingIds);

        /** @var CustomerBooking $oldBooking */
        foreach ($oldBookings->getItems() as $bookingId => $oldBooking) {
            if (in_array($bookingId, $deletedBookingIds, true) && $oldBooking->getCustomFields()) {
                $oldBookingCustomFields = json_decode($oldBooking->getCustomFields()->getValue(), true);

                foreach ((array)$oldBookingCustomFields as $customField) {
                    if ($customField && array_key_exists('value', $customField) &&
                        array_key_exists('type', $customField) && $customField['type'] === 'file'
                    ) {
                        foreach ((array)$customField['value'] as $file) {
                            if (is_array($file) && array_key_exists('fileName', $file)) {
                                if (file_exists($this->getUploadsPath() . $bookingId . '_' . $file['fileName'])) {
                                    unlink($this->getUploadsPath() . $bookingId . '_' . $file['fileName']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return string
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getUploadsPath()
    {
        /** @var SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');

        $path = $settingsDS->getSetting('general', 'customFieldsUploadsPath');

        if (trim($path) && substr($path, -1) !== '/') {
            return $path . '/';
        }

        return trim($path) ?: AMELIA_UPLOADS_FILES_PATH;
    }
}
