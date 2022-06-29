<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Settings;

/**
 * Class Settings
 *
 * @package AmeliaBooking\Domain\Entity\Settings
 */
class Settings
{
    /** @var GeneralSettings */
    private $generalSettings;

    /** @var PaymentSettings */
    private $paymentSettings;

    /** @var ZoomSettings */
    private $zoomSettings;

    /** @var LessonSpaceSettings */
    private $lessonSpaceSettings;

    /**
     * @return GeneralSettings
     */
    public function getGeneralSettings()
    {
        return $this->generalSettings;
    }

    /**
     * @param GeneralSettings $generalSettings
     */
    public function setGeneralSettings($generalSettings)
    {
        $this->generalSettings = $generalSettings;
    }

    /**
     * @return PaymentSettings
     */
    public function getPaymentSettings()
    {
        return $this->paymentSettings;
    }

    /**
     * @param PaymentSettings $paymentSettings
     */
    public function setPaymentSettings($paymentSettings)
    {
        $this->paymentSettings = $paymentSettings;
    }

    /**
     * @return ZoomSettings
     */
    public function getZoomSettings()
    {
        return $this->zoomSettings;
    }

    /**
     * @param ZoomSettings $zoomSettings
     */
    public function setZoomSettings($zoomSettings)
    {
        $this->zoomSettings = $zoomSettings;
    }

    /**
     * @return LessonSpaceSettings
     */
    public function getLessonSpaceSettings()
    {
        return $this->lessonSpaceSettings;
    }

    /**
     * @param LessonSpaceSettings $lessonSpaceSettings
     */
    public function setLessonSpaceSettings($lessonSpaceSettings)
    {
        $this->lessonSpaceSettings = $lessonSpaceSettings;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'general'  => $this->getGeneralSettings() ? $this->getGeneralSettings()->toArray() : null,
            'payments' => $this->getPaymentSettings() ? $this->getPaymentSettings()->toArray() : null,
        ];
    }
}
