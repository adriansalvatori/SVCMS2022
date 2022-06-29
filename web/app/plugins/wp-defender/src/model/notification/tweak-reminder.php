<?php

namespace WP_Defender\Model\Notification;

use Calotes\Helper\Array_Cache;

/**
 * Class Tweak_Reminder.
 *
 * @package WP_Defender\Model\Notification
 */
class Tweak_Reminder extends \WP_Defender\Model\Notification {
	/**
	 * Option name.
	 *
	 * @var string
	 */
	protected $table = 'wd_security_tweaks_reminder';

	/**
	 * Load the default first before actual value imported from db.
	 *
	 * @return string|void
	 */
	protected function before_load() {
		$params = array(
			'slug'                 => 'tweak-reminder',
			'title'                => __( 'Security Recommendations - Notification', 'wpdef' ),
			'status'               => self::STATUS_DISABLED,
			'description'          => __( 'Get email notifications if/when a security recommendation needs fixing.', 'wpdef' ),
			// @since 3.0.0 Fix 'Guest'-line.
			'in_house_recipients'  => is_user_logged_in() ? array( $this->get_default_user() ) : array(),
			'out_house_recipients' => array(),
			'type'                 => 'notification',
			'dry_run'              => false,
			'configs'              => array(
				'reminder' => 'weekly',
			),
		);
		$this->import( $params );
	}

	/**
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function maybe_send() {
		if ( self::STATUS_ACTIVE !== $this->status ) {
			return false;
		}

		$est = new \DateTime( 'now', wp_timezone() );
		$est->setTimestamp( $this->last_sent );
		$now      = new \DateTime( 'now', wp_timezone() );
		$interval = \DateInterval::createFromDateString( (string) $est->getOffset() . 'seconds' );
		$now->add( $interval );

		switch ( $this->configs['reminder'] ) {
			case 'daily':
				$est->add( new \DateInterval( 'P1D' ) );
				break;
			case 'weekly':
				$est->add( new \DateInterval( 'P1W' ) );
				break;
			case 'monthly':
			default:
				$est->add( new \DateInterval( 'P1M' ) );
				break;
		}
		$est->add( $interval );
		// Testing.
		if ( defined( 'WP_DEFENDER_TESTING' ) && true === constant( 'WP_DEFENDER_TESTING' ) ) {
			return true;
		}
		if ( $est->getTimestamp() < $now->getTimestamp() ) {
			return true;
		}

		return false;
	}

	public function send() {
		$tweaks = wd_di()->get( \WP_Defender\Model\Setting\Security_Tweaks::class );
		if ( 0 === (is_array($tweaks->issues) || $tweaks->issues instanceof \Countable ? count( $tweaks->issues ) : 0) ) {
			return;
		}
		$arr        = Array_Cache::get( 'tweaks', 'tweaks' );
		$issues     = '';
		$template   = wd_di()->get( \WP_Defender\Controller\Security_Tweaks::class );
		$status_img = defender_asset_url( '/assets/email-assets/img/Warning@2x.png' );
		foreach ( $tweaks->issues as $slug ) {
			if ( isset( $arr[ $slug ] ) ) {
				$issue   = $arr[ $slug ];
				$data    = $issue->to_array();
				$issues .= $template->render_partial(
					'email/tweak-issue',
					array(
						'data'       => $data,
						'status_img' => $status_img,
					),
					false
				);
			}
		}

		foreach ( $this->in_house_recipients as $recipient ) {
			if ( self::USER_SUBSCRIBED !== $recipient['status'] ) {
				continue;
			}
			$this->send_to_user( $recipient['email'], $recipient['name'], $issues );
		}

		foreach ( $this->out_house_recipients as $recipient ) {
			if ( self::USER_SUBSCRIBED !== $recipient['status'] ) {
				continue;
			}
			$this->send_to_user( $recipient['email'], $recipient['name'], $issues );
		}
		$this->last_sent = time();
		$this->save();
	}

	/**
	 * @param $email
	 * @param $name
	 * @param $issues
	 *
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function send_to_user( $email, $name, $issues ) {
		$tweaks   = wd_di()->get( \WP_Defender\Model\Setting\Security_Tweaks::class );
		$logs_url = network_admin_url( 'admin.php?page=wdf-hardener' );
		$logs_url = apply_filters( 'report_email_logs_link', $logs_url, $email );

		$security_tweak = wd_di()->get( \WP_Defender\Controller\Security_Tweaks::class );
		$content_body   = $security_tweak->render_partial(
			'email/tweaks-reminder',
			array(
				'count'    => is_array($tweaks->issues) || $tweaks->issues instanceof \Countable ? count( $tweaks->issues ) : 0,
				'view_url' => $logs_url,
				'name'     => $name,
				'issues'   => $issues,
				'site_url' => network_site_url(),
			),
			false
		);
		$content        = $security_tweak->render_partial(
			'email/index',
			array(
				'title'         => __( 'Security Report', 'wpdef' ),
				'content_body'  => $content_body,
			),
			false
		);

		/* translators: */
		$subject = _n(
			'Security Recommendation Report for %1$s. %2$s recommendation to action.',
			'Security Recommendation Report for %1$s. %2$s recommendations to action.',
			is_array($tweaks->issues) || $tweaks->issues instanceof \Countable ? count( $tweaks->issues ) : 0,
			'wpdef'
		);
		$subject = sprintf( $subject, network_site_url(), is_array($tweaks->issues) || $tweaks->issues instanceof \Countable ? count( $tweaks->issues ) : 0 );

		$headers = defender_noreply_html_header(
			defender_noreply_email( 'wd_recommendation_noreply_email' )
		);

		$ret = wp_mail( $email, $subject, $content, $headers );
		if ( $ret ) {
			$this->save_log( $email );
		}
	}

	/**
	 * Define labels for settings key.
	 *
	 * @param  string|null $key
	 *
	 * @return string|array|null
	 */
	public function labels( $key = null ) {
		$labels = array(
			'notification'        => __( 'Security Recommendations - Notification', 'wpdef' ),
			'notification_repeat' => __( 'Frequency', 'wpdef' ),
			'subscribers'         => __( 'Recipients', 'wpdef' ),
		);

		if ( ! is_null( $key ) ) {
			return $labels[ $key ] ?? null;
		}

		return $labels;
	}
}