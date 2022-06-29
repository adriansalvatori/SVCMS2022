<?php

namespace AmeliaBooking\Domain\Factory\User;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Factory\Bookable\Service\ServiceFactory;

/**
 * Class ProviderFactory
 *
 * @package AmeliaBooking\Domain\Factory\User
 */
class ProviderFactory extends UserFactory
{
    /**
     * @param array $providers
     * @param array $services
     * @param array $providersServices
     *
     * @return Collection
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public static function createCollection($providers, $services = [], $providersServices = [])
    {
        $providersCollection = new Collection();

        foreach ($providers as $providerKey => $providerArray) {
            $providersCollection->addItem(
                self::create($providerArray),
                $providerKey
            );

            if ($providersServices && array_key_exists($providerKey, $providersServices)) {
                foreach ((array)$providersServices[$providerKey] as $serviceKey => $providerService) {
                    if (array_key_exists($serviceKey, $services)) {
                        $providersCollection->getItem($providerKey)->getServiceList()->addItem(
                            ServiceFactory::create(
                                array_merge(
                                    $services[$serviceKey],
                                    $providerService
                                )
                            ),
                            $serviceKey
                        );
                    }
                }
            }
        }

        return $providersCollection;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public static function reformat($rows)
    {
        $data = [];

        foreach ($rows as $row) {
            $id = $row['provider_id'];

            $googleCalendarId = $row['google_calendar_id'];

            if ($id && empty($data[$id])) {
                $data[$id] = [
                    'id'           => $id,
                    'type'         => $row['provider_type'],
                    'firstName'    => $row['provider_firstName'],
                    'lastName'     => $row['provider_lastName'],
                    'email'        => $row['provider_email'],
                    'note'         => $row['provider_note'],
                    'phone'        => $row['provider_phone'],
                    'gender'       => $row['provider_gender'],
                    'translations' => $row['provider_translations'],
                ];
            }

            if ($data[$id] && $googleCalendarId && empty($data[$id]['googleCalendar'])) {
                $data[$id]['googleCalendar'] = [
                    'id'         =>  $row['google_calendar_id'],
                    'token'      =>  $row['google_calendar_token'],
                    'calendarId' =>  isset($row['google_calendar_calendar_id']) ?
                        $row['google_calendar_calendar_id'] : null
                ];
            }
        }

        return $data;
    }
}
