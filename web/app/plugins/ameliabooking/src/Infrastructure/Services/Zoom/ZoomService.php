<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Zoom;

use AmeliaBooking\Domain\Services\Settings\SettingsService;
use Firebase\JWT\JWT;

/**
 * Class ZoomService
 *
 * @package AmeliaBooking\Infrastructure\Services\Zoom
 */
class ZoomService
{
    /**
     * @var SettingsService $settingsService
     */
    private $settingsService;

    /**
     * ZoomService constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param string     $requestUrl
     * @param array|null $data
     * @param string     $method
     *
     * @return mixed
     */
    public function execute($requestUrl, $data, $method)
    {
        $zoomSettings = $this->settingsService->getCategorySettings('zoom');

        $token = [
            'iss' => $zoomSettings['apiKey'],
            'exp' => time() + 3600
        ];

        $ch = curl_init($requestUrl);

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Authorization: Bearer ' . JWT::encode($token, $zoomSettings['apiSecret']),
                'Content-Type: application/json'
            ]
        );

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_FORCE_OBJECT));
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($result === false) {
            return ['message' => curl_error($ch), 'users' => null];
        }

        curl_close($ch);

        $resultArray = json_decode($result, true);

        if (isset($resultArray['join_url']) &&
            strpos($resultArray['join_url'], 'pwd=') === false &&
            isset($resultArray['encrypted_password'])
        ) {
            $limitParam = strpos($resultArray['join_url'], '?') === false ? '?' : '&';

            $resultArray['join_url'] = $resultArray['join_url'] .=
                $limitParam . 'pwd=' . $resultArray['encrypted_password'];
        }

        return $resultArray;
    }

    /**
     *
     * @return mixed
     */
    public function getUsers()
    {
        $zoomSettings = $this->settingsService->getCategorySettings('zoom');

        $pagesCount = $zoomSettings['maxUsersCount'] > 300 ? (int)ceil($zoomSettings['maxUsersCount'] / 300) : 1;

        $response = [];

        $users = [];

        for ($i = 1; $i <= $pagesCount; $i++) {
            $urlParams = 'page_size=300' . ($pagesCount > 1 ? '&page_number=' . $i : '');

            $response = $this->execute("https://api.zoom.us/v2/users?$urlParams", null, 'GET');

            $users = array_merge($users, $response['users']);

            if (sizeof($response['users']) < 300) {
                break;
            }
        }

        $response['users'] = $users;

        return $response;
    }

    /**
     * @param int   $userId
     * @param array $data
     *
     * @return mixed
     */
    public function createMeeting($userId, $data)
    {
        return $this->execute("https://api.zoom.us/v2/users/{$userId}/meetings", $data, 'POST');
    }

    /**
     * @param int   $meetingId
     * @param array $data
     *
     * @return mixed
     */
    public function updateMeeting($meetingId, $data)
    {
        return $this->execute("https://api.zoom.us/v2/meetings/{$meetingId}", $data, 'PATCH');
    }

    /**
     * @param int   $meetingId
     *
     * @return mixed
     */
    public function deleteMeeting($meetingId)
    {
        return $this->execute("https://api.zoom.us/v2/meetings/{$meetingId}", null, 'DELETE');
    }

    /**
     * @param int $meetingId
     *
     * @return mixed
     */
    public function getMeeting($meetingId)
    {
        return $this->execute("https://api.zoom.us/v2/meetings/{$meetingId}", null, 'GET');
    }
}
