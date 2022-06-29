<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class Autonami_Helpers
 *
 * @package Uncanny_Automator
 */
class Autonami_Pro_Helpers extends \Uncanny_Automator\Autonami_Helpers {

	/**
	 * Method add_tag_to_contact
	 *
	 * @param  mixed $email
	 * @param  mixed $tag_id
	 * @param  mixed $tag_readable
	 * @return void
	 */
	public function remove_tag_from_contact( $email, $tag_id, $tag_readable ) {

		$tags_to_remove = array(
			$tag_id,
		);

		$autonami_contact = new \BWFCRM_Contact( $email );

		$result = $autonami_contact->remove_tags( $tags_to_remove );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		}

		if ( empty( $result ) ) {
			/* translators: %s - the tag name. */
			throw new \Exception( sprintf( __( 'User did not have the %s tag', 'uncanny-automator' ), $tag_readable ) );
		}

		$autonami_contact->save();

	}

	/**
	 * add_contact_to_list
	 *
	 * @param  mixed $email
	 * @param  mixed $list_id
	 * @param  mixed $list_readable
	 * @return void
	 */
	public function add_contact_to_list( $email, $list_id, $list_readable ) {

		$lists_to_add = array(
			array(
				'id' => $list_id,
			),
		);

		$autonami_contact = new \BWFCRM_Contact( $email );

		$result = $autonami_contact->add_lists( $lists_to_add );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		}

		if ( empty( $result ) ) {
			/* translators: %s - the tag name. */
			throw new \Exception( sprintf( __( 'User was already a member of %s', 'uncanny-automator' ), $list_readable ) );
		}

		$autonami_contact->save();
	}

	/**
	 * add_contact_to_list
	 *
	 * @param  mixed $email
	 * @param  mixed $list_id
	 * @param  mixed $list_readable
	 * @return void
	 */
	public function remove_contact_from_list( $email, $list_id, $list_readable ) {

		$lists_to_rempve = array(
			$list_id,
		);

		$autonami_contact = new \BWFCRM_Contact( $email );

		$result = $autonami_contact->remove_lists( $lists_to_rempve );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		}

		if ( empty( $result ) ) {
			/* translators: %s - the tag name. */
			throw new \Exception( sprintf( __( 'The user was not a member of %s ', 'uncanny-automator' ), $list_readable ) );
		}

		$autonami_contact->save();
	}
}
