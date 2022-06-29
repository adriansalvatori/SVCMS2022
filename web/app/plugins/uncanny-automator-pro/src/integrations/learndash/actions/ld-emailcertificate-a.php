<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_EMAILCERTIFICATE_A
 * @package Uncanny_Automator_Pro
 */
class LD_EMAILCERTIFICATE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'EMAILACERTIFICATE';
		$this->action_meta = 'SENDCERTIFICATE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'knowledge-base/generate-an-email-a-certificate-to-the-user/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( __( 'Send a {{certificate:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => __( 'Send a {{certificate}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'send_certificate' ),
			// very last call in WP, we need to make sure they viewed the page and didn't skip before is was fully viewable
			'options_group'      => array(
				$this->action_meta => array(
					$uncanny_automator->helpers->recipe->learndash->options->pro->all_ld_certificates( null, $this->action_meta ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILFROM', __( 'From', 'uncanny-automator' ), true, 'email', '{{admin_email}}', true, '' ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILTO', __( 'To:', 'uncanny-automator' ), true, 'email', '', true, esc_html__( 'Separate multiple email addresses with a comma', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILCC', __( 'CC', 'uncanny-automator' ), true, 'email', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILBCC', __( 'BCC', 'uncanny-automator' ), true, 'email', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILSUBJECT', __( 'Subject', 'uncanny-automator' ), true ),

					// Email Content Field.
					$uncanny_automator->helpers->recipe->field->text(
						array(
							'option_code'               => 'EMAILBODY',
							/* translators: Email field */
							'label'                     => esc_attr__( 'Email body', 'uncanny-automator' ),
							'input_type'                => 'textarea',
							'supports_fullpage_editing' => true,
						)
					),

					$uncanny_automator->helpers->recipe->field->text_field( 'CERTBODY', __( 'Certificate body', 'uncanny-automator-pro' ), true, 'textarea', '', false, esc_html__( 'Use field above to override content of selected certificate. Leave blank to use original content.' ) ),
				),
			),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function send_certificate( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$certificate_id = $uncanny_automator->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$to             = $uncanny_automator->parse->text( $action_data['meta']['EMAILTO'], $recipe_id, $user_id, $args );
		$from           = $uncanny_automator->parse->text( $action_data['meta']['EMAILFROM'], $recipe_id, $user_id, $args );
		$cc             = $uncanny_automator->parse->text( $action_data['meta']['EMAILCC'], $recipe_id, $user_id, $args );
		$bcc            = $uncanny_automator->parse->text( $action_data['meta']['EMAILBCC'], $recipe_id, $user_id, $args );
		$subject        = $uncanny_automator->parse->text( $action_data['meta']['EMAILSUBJECT'], $recipe_id, $user_id, $args );
		$subject        = do_shortcode( $subject );
		$email_body     = $uncanny_automator->parse->text( $action_data['meta']['EMAILBODY'], $recipe_id, $user_id, $args );
		$email_body     = do_shortcode( $email_body );
		$cert_body      = $uncanny_automator->parse->text( $action_data['meta']['CERTBODY'], $recipe_id, $user_id, $args );

		if ( empty( wp_strip_all_tags( $cert_body ) ) ) {
			$cert_post = get_post( $certificate_id );
			if ( $cert_post instanceof \WP_Post ) {
				$cert_body = $cert_post->post_content;
			}
		}
		$pattern = get_shortcode_regex();
		preg_match_all( '/' . $pattern . '/s', $cert_body, $matches );
		if (
			preg_match_all( '/' . $pattern . '/s', $cert_body, $matches ) &&
			array_key_exists( 2, $matches ) &&
			(
				in_array( 'quizinfo', $matches[2] ) || //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				in_array( 'courseinfo', $matches[2] ) //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			)
		) {
			if ( isset( $matches[0] ) ) {
				foreach ( $matches[0] as $__mataches ) {
					$cert_body = str_replace( $__mataches, 'N/A', $cert_body );
				}
			}
		}

		$error_message = '';
		$headers[]     = 'From: <' . $from . '>';

		if ( ! empty( $cc ) ) {
			$headers[] = 'Cc: ' . $cc;
		}

		if ( ! empty( $bcc ) ) {
			$headers[] = 'Bcc: ' . $bcc;
		}

		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		/* Save Path on Server under Upload & allow overwrite */
		$save_path = apply_filters( 'automator_certificate_save_path', WP_CONTENT_DIR . '/uploads/automator-certificates/' );

		if ( ! file_exists( $save_path ) ) {
			mkdir( $save_path, 0755 );
		}

		$filename = 'certificate-' . $certificate_id . '-' . time();

		$certificate_args = array(
			'certificate_post' => $certificate_id,
			'save_path'        => $save_path, // Add save path.
			'file_name'        => $filename, // Add filename.
			'user'             => get_user_by( 'ID', $user_id ),
		);

		$attachments = $uncanny_automator->helpers->recipe->learndash->pro->generate_pdf( $certificate_args, $cert_body, 'automator' );

		// Something went wrong with return format, complete with errors.
		if ( ! is_array( $attachments ) ) {

			$error_message = esc_html__( 'Attachments return an invalid array format.', 'uncanny-automator-pro' );

			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;

			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		// Something went wrong with pdf, complete with errors.
		if ( false === $attachments['return'] ) {
			$error_message                       = $attachments['message'];
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;

			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$attachments = $attachments['message'];

		$mailed = wp_mail( $to, $subject, $email_body, $headers, array( $attachments ) );

		if ( ! $mailed ) {

			$error_message = $uncanny_automator->error_message->get( 'email-failed' );

			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;

			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			if ( $attachments ) {
				unlink( $attachments );
			}

			return;
		}

		if ( $attachments ) {
			unlink( $attachments );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );
	}

}
