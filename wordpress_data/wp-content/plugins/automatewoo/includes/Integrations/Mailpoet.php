<?php

namespace AutomateWoo;

use MailPoet\API\MP\v1\APIException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class integrating MailPoet API
 *
 * @see https://github.com/mailpoet/mailpoet/tree/trunk/doc
 * @class Integration_Mailpoet
 * @since 5.6.10
 */
class Integration_Mailpoet extends Integration {

	/** @var string */
	public $integration_id = 'mailpoet';

	/**
	 * @var \MailPoet\API\MP\v1\API
	 */
	private $api;

	private const STATUS_SUBSCRIBED = 'subscribed';

	/**
	 * Constructor
	 *
	 * @param \MailPoet\API\MP\v1\API $api The API Object
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * This functions allows adding hooks for MailPoet
	 *
	 * @since 5.7.0
	 */
	public function init_hooks() {
		add_action( 'automatewoo/customer/opted_out', [ $this, 'sync_opt_out' ] );
		add_action( 'automatewoo/customer/before_is_unsubscribed', [ $this, 'sync_subscribed_status' ] );
	}

	/**
	 * Get Mailpoet lists.
	 * It filters the list discarding those in trash.
	 *
	 * @return array An Associative array with the list ID as a key and the list name as a value.
	 */
	public function get_lists() {

		$list_names = [];

		$lists = $this->api->getLists();

		foreach ( $lists as $list ) {
			// getLists method includes trashed list. We exclude those from the returned list in AW.
			if ( is_null( $list['deleted_at'] ) ) {
				$list_names[ $list['id'] ] = $list['name'];
			}
		}

		return $list_names;
	}

	/**
	 * Gets a subscriber by email.
	 * When the subscriber doesn't exist it returns null.
	 *
	 * @param string $subscriber_email The subscriber email.
	 * @return array|null The subscriber or null if it doesn't exist.
	 */
	private function get_subscriber( $subscriber_email ) {
		try {
			return $this->api->getSubscriber( $subscriber_email );
		} catch ( \Exception $e ) {
			return null;
		}
	}


	/**
	 * Add a subscriber to a list. If the subscriber doesn't exist it will be created.
	 *
	 * @param array  $subscriber_data Subscriber data.
	 * @param string $list_id List ID
	 * @param array  $options API Call options. Like email confirmation.
	 * @throws APIException When the subscribe action fails.
	 */
	public function subscribe( $subscriber_data, $list_id, $options = [] ) {
		$subscriber = $this->get_subscriber( $subscriber_data['email'] );

		if ( is_null( $subscriber ) ) {
			$this->api->addSubscriber( $subscriber_data, [ $list_id ], $options );
		} else {
			$this->api->subscribeToList( $subscriber['id'], $list_id, $options );
		}

	}


	/**
	 * Removes a subscriber from a list. If the subscriber doesn't exist it fails.
	 *
	 * @param string $subscriber_email Subscriber data.
	 * @param string $list_id List ID
	 * @throws \Exception When the subscriber doesn't exist.
	 */
	public function unsubscribe( $subscriber_email, $list_id ) {
		$subscriber = $this->get_subscriber( $subscriber_email );

		if ( is_null( $subscriber ) ) {
			throw new \Exception( "Subscriber doesn't exist" );
		}

		$this->api->unsubscribeFromList( $subscriber['id'], $list_id );
	}

	/**
	 * Get the list IDs where the user is subscribed.
	 *
	 * MailPoet throws exception if you try to unsubscribe a user from the WC / WP lists (segments 1 and 2)
	 * So this function returns an array with list IDs discarding the lists outside the default type.
	 *
	 * @since 5.7.0
	 * @param array $subscriber The subscriber data
	 * @return array An array of default list IDs where the user is subscribed.
	 */
	private function get_subscribed_lists_ids( $subscriber ) {

		if ( is_null( $subscriber ) ) {
			return [];
		}

		// discard not subscribed
		$subscribed_lists = array_filter(
			$subscriber['subscriptions'],
			function ( $subscription ) {
				return $subscription['status'] === self::STATUS_SUBSCRIBED;
			}
		);

		return array_intersect(
			array_column( $this->api->getLists(), 'id' ),
			array_column( $subscribed_lists, 'segment_id' )
		);
	}

	/**
	 * Opt-out a customer when that customer opt-out from AutomateWoo
	 *
	 * @see https://github.com/woocommerce/automatewoo/issues/1313
	 * @since 5.7.0
	 * @param Customer $customer The customer object.
	 */
	public function sync_opt_out( $customer ) {
		$subscriber = $this->get_subscriber( $customer->get_email() );
		$list_ids   = $this->get_subscribed_lists_ids( $subscriber );

		if ( ! empty( $list_ids ) ) {
			try {
				$this->api->unsubscribeFromLists( $subscriber['id'], $list_ids );
			} catch ( APIException $e ) {
				return;
			}
		}
	}

	/**
	 * Subscribes an AW user in case the user is subscribed in MailPoet
	 *
	 * @see Customer::is_unsubscribed()
	 * @since 5.7.0
	 * @param Customer $customer The customer object
	 */
	public function sync_subscribed_status( $customer ) {

		// If customer is subscribed in AW, no need to verify MailPoet Status.
		if ( true === $customer->get_is_subscribed() ) {
			return;
		}

		$subscriber = $this->get_subscriber( $customer->get_email() );

		// If customer is subscribed in MailPoet then we opt in the customer in AW.
		if ( $this->is_subscribed( $subscriber ) ) {
			$customer->opt_in();
		}

	}

	/**
	 * Check if a user is subscribed by checking the subscription status on each default list.
	 * This is a workaround since MailPoet API doesn't allow yet to check/change the global subscription status.
	 *
	 * @param array $subscriber The subscriber data
	 * @return bool True if the user is subscribed.
	 */
	private function is_subscribed( $subscriber ) {
		return ! empty( $this->get_subscribed_lists_ids( $subscriber ) );
	}
}
