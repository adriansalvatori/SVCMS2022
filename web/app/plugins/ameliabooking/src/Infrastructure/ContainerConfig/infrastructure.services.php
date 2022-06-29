<?php
/**
 * Assembling infrastructure services:
 * Instantiating infrastructure services
 */

use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\LessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Notification\MailerFactory;
use AmeliaBooking\Infrastructure\Services\Notification\MailgunService;
use AmeliaBooking\Infrastructure\Services\Notification\PHPMailService;
use AmeliaBooking\Infrastructure\Services\Notification\SMTPService;
use AmeliaBooking\Infrastructure\Services\Notification\WpMailService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;
use AmeliaBooking\Infrastructure\Services\Recaptcha\RecaptchaService;
use AmeliaBooking\Infrastructure\Services\Zoom\ZoomService;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Mailer Service
 *
 * @param Container $c
 *
 * @return MailgunService|PHPMailService|SMTPService|WpMailService
 */
$entries['infrastructure.mail.service'] = function ($c) {
    return MailerFactory::create($c->get('domain.settings.service'));
};

/**
 * Report Service
 *
 * @return AmeliaBooking\Infrastructure\Services\Report\Spout\CsvService
 */
$entries['infrastructure.report.csv.service'] = function () {
    return new AmeliaBooking\Infrastructure\Services\Report\Spout\CsvService();
};

/**
 * PayPal Payment Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Infrastructure\Services\Payment\PayPalService
 */
$entries['infrastructure.payment.payPal.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Payment\PayPalService(
        $c->get('domain.settings.service')
    );
};

/**
 * Stripe Payment Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Infrastructure\Services\Payment\StripeService
 */
$entries['infrastructure.payment.stripe.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Payment\StripeService(
        $c->get('domain.settings.service')
    );
};

/**
 * Mollie Payment Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Infrastructure\Services\Payment\MollieService
 */
$entries['infrastructure.payment.mollie.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Payment\MollieService(
        $c->get('domain.settings.service')
    );
};

/**
 * Razorpay Payment Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Infrastructure\Services\Payment\RazorpayService
 */
$entries['infrastructure.payment.razorpay.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Payment\RazorpayService(
        $c->get('domain.settings.service')
    );
};

/**
 * Currency Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Infrastructure\Services\Payment\CurrencyService
 */
$entries['infrastructure.payment.currency.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Payment\CurrencyService(
        $c->get('domain.settings.service')
    );
};

/**
 * Less Parser Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Infrastructure\Services\Frontend\LessParserService
 */
$entries['infrastructure.frontend.lessParser.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Frontend\LessParserService(
        AMELIA_PATH . '/assets/less/frontend/amelia-booking.less',
        AMELIA_UPLOADS_PATH . '/amelia/css',
        $c->get('domain.settings.service')
    );
};

/**
 * Google Calendar Service
 *
 * @param Container $c
 *
 * @return GoogleCalendarService
 */
$entries['infrastructure.google.calendar.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService($c);
};

/**
 * Zoom Service
 *
 * @param Container $c
 *
 * @return ZoomService
 */
$entries['infrastructure.zoom.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Zoom\ZoomService(
        $c->get('domain.settings.service')
    );
};

/**
 * Lesson Space Service
 *
 * @param Container $c
 *
 * @return LessonSpaceService
 */
$entries['infrastructure.lesson.space.service'] = function ($c) {
    return new LessonSpaceService(
        $c,
        $c->get('domain.settings.service')
    );
};

/**
 * Outlook Service
 *
 * @param Container $c
 *
 * @return OutlookCalendarService
 */
$entries['infrastructure.outlook.calendar.service'] = function ($c) {
    return new OutlookCalendarService($c);
};

/**
 * Recaptcha Service
 *
 * @param Container $c
 *
 * @return RecaptchaService
 */
$entries['infrastructure.recaptcha.service'] = function ($c) {
    return new AmeliaBooking\Infrastructure\Services\Recaptcha\RecaptchaService(
        $c->get('domain.settings.service')
    );
};
