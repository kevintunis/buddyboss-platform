<?php
/**
 * BuddyBoss Performance Forums Integration.
 *
 * @package BuddyBoss\Performance
 */

namespace BuddyBoss\Performance\Integration;

use BuddyBoss\Performance\Helper;
use BuddyBoss\Performance\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Forums Integration Class.
 *
 * @package AppBoss\Performance
 */
class BB_Forums extends Integration_Abstract {


	/**
	 * Add(Start) Integration
	 *
	 * @return mixed|void
	 */
	public function set_up() {
		$this->register( 'bbp-forums' );

		$event_groups = array( 'bbpress', 'bbpress-forums' );

		$purge_events = array(
			'save_post_forum', // When forum created.
			'edit_post_forum', // When forum updated.
			'trashed_post', // When forum trashed.
			'untrashed_post', // When forum untrashed.
			'deleted_post', // When forum deleted.
			'bbp_new_topic', // When new topic created, update count and last topic id and author id.
			'bbp_edit_topic', // When topic updated, update count and last topic id and author id.
			'bbp_new_reply', // When new reply created, update count and last reply id and author id.
			'bbp_edit_reply', // When reply updated, update count and last reply id and author id.
			'bbp_merged_topic', // When topic merged, update count and last reply id and author id.
			'bbp_post_move_reply', // When reply moved, update count and last reply id and author id.
			'bbp_post_split_topic', // When split topic, update count and last reply id and author id.
			'bbp_add_user_subscription', // When user subscribe forum.
			'bbp_remove_user_subscription', // When user remove forum's subscribe.
		);

		/**
		 * Add Custom events to purge forums endpoint cache
		 */
		$purge_events = apply_filters( 'bbplatform_cache_bbp_forums', $purge_events );
		$this->purge_event( 'bbp-forums', $purge_events );

		/**
		 * Support for single items purge
		 */
		$purge_single_events = array(
			'save_post_forum'                       => 1, // When forum created.
			'edit_post_forum'                       => 1, // When forum updated.
			'trashed_post'                          => 1, // When forum trashed.
			'untrashed_post'                        => 1, // When forum untrashed.
			'deleted_post'                          => 1, // When forum deleted.
			'bbp_add_user_subscription'             => 2, // When user subscribe forum.
			'bbp_remove_user_subscription'          => 2, // When user remove forum's subscribe.
			'bbp_new_topic'                         => 2, // When new topic created, update count and last topic id and author id.
			'bbp_edit_topic'                        => 2, // When topic updated, update count and last topic id and author id.
			'bbp_new_reply'                         => 3, // When new reply created, update count and last reply id and author id.
			'bbp_edit_reply'                        => 3, // When reply updated, update count and last reply id and author id.
			'bbp_merged_topic'                      => 3, // When topic merged, update count and last reply id and author id.
			'bbp_post_move_reply'                   => 3, // When reply moved, update count and last reply id and author id.
			'bbp_post_split_topic'                  => 3, // When split topic, update count and last reply id and author id.

			// Group Embed data.
			'bp_group_admin_edit_after'             => 1, // When forum Group change form admin.
			'groups_group_details_edited'           => 1, // When forum Group Details updated form Manage.
			'groups_group_settings_edited'          => 1, // When forum Group setting updated form Manage.
			'bp_group_admin_after_edit_screen_save' => 1, // When Group forums setting Manage.
			'groups_avatar_uploaded'                => 1, // When forum Group avarar updated form Manage.
			'groups_cover_image_uploaded'           => 1, // When forum Group cover photo uploaded form Manage.
			'groups_cover_image_deleted'            => 1, // When forum Group cover photo deleted form Manage.

			// Add Author Embed Support.
			'profile_update'                        => 1, // User updated on site.
			'deleted_user'                          => 1, // User deleted on site.
			'xprofile_avatar_uploaded'              => 1, // User avatar photo updated.
			'bp_core_delete_existing_avatar'        => 1, // User avatar photo deleted.
		);

		/**
		 * Add Custom events to purge single activity endpoint cache
		 */
		$purge_single_events = apply_filters( 'bbplatform_cache_bbp_forums_single', $purge_single_events );
		$this->purge_single_events( 'bbplatform_cache_purge_bbp-forums_single', $purge_single_events );

		$is_component_active = Helper::instance()->get_app_settings( 'cache_component', 'buddyboss' );
		$settings            = Helper::instance()->get_app_settings( 'cache_bb_forum_discussions', 'buddyboss' );
		$cache_bb_forums     = isset( $is_component_active ) && isset( $settings ) ? ( $is_component_active && $settings ) : false;

		if ( $cache_bb_forums ) {

			$this->cache_endpoint(
				'buddyboss/v1/forums',
				Cache::instance()->MONTH_IN_SECONDS * 60,
				$purge_events,
				$event_groups,
				array(
					'unique_id'         => 'id',
					'purge_deep_events' => array_keys( $purge_single_events ),
				),
				true
			);

			$this->cache_endpoint(
				'buddyboss/v1/forums/<id>',
				Cache::instance()->MONTH_IN_SECONDS * 60,
				array_keys( $purge_single_events ),
				$event_groups,
				array(),
				false
			);
		}
	}

	/**
	 * When forum created
	 *
	 * @param int $forum_id Forum post ID.
	 */
	public function event_save_post_forum( $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When forum updated
	 *
	 * @param int $forum_id Forum post ID.
	 */
	public function event_edit_post_forum( $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When forum trashed
	 *
	 * @param int $forum_id Forum post ID.
	 */
	public function event_trashed_post( $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When forum untrashed
	 *
	 * @param int $forum_id Forum post ID.
	 */
	public function event_untrashed_post( $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When forum deleted
	 *
	 * @param int $forum_id Forum post ID.
	 */
	public function event_deleted_post( $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When user subscribe forum
	 *
	 * @param int $user_id User ID.
	 * @param int $forum_id Forum post ID.
	 */
	public function event_bbp_add_user_subscription( $user_id, $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When user remove forums subscribe
	 *
	 * @param int $user_id User ID.
	 * @param int $forum_id Forum post ID.
	 */
	public function event_bbp_remove_user_subscription( $user_id, $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When new topic created, update count and last topic id and author id
	 *
	 * @param int $topic_id Topic post ID.
	 * @param int $forum_id Forum post ID.
	 */
	public function event_bbp_new_topic( $topic_id, $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When topic updated, update count and last topic id and author id
	 *
	 * @param int $topic_id Topic post ID.
	 * @param int $forum_id Forum post ID.
	 */
	public function event_bbp_edit_topic( $topic_id, $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When new reply created, update count and last reply id and author id
	 *
	 * @param int $reply_id Reply post ID.
	 * @param int $topic_id Topic post ID.
	 * @param int $forum_id Forum post ID.
	 */
	public function event_bbp_new_reply( $reply_id, $topic_id, $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When reply updated, update count and last reply id and author id
	 *
	 * @param int $reply_id Reply post ID.
	 * @param int $topic_id Topic post ID.
	 * @param int $forum_id Forum post ID.
	 */
	public function event_bbp_edit_reply( $reply_id, $topic_id, $forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
	}

	/**
	 * When topic merged, update count and last reply id and author id
	 *
	 * @param int $destination_topic_id Destination Topic ID.
	 * @param int $source_topic_id Source Topic ID.
	 * @param int $source_topic_forum_id Source Topic Forum ID.
	 */
	public function event_bbp_merged_topic( $destination_topic_id, $source_topic_id, $source_topic_forum_id ) {
		Cache::instance()->purge_by_group( 'bbp-forums_' . $source_topic_forum_id );
	}

	/**
	 * When reply moved, update count and last reply id and author id
	 *
	 * @param int $move_reply_id Move Reply ID.
	 * @param int $source_topic_id Source Topic ID.
	 * @param int $destination_topic_id Destination Topic ID.
	 */
	public function event_bbp_post_move_reply( $move_reply_id, $source_topic_id, $destination_topic_id ) {
		$destination_forum_id = bbp_get_topic_forum_id( $destination_topic_id );
		Cache::instance()->purge_by_group( 'bbp-forums_' . $destination_forum_id );
	}

	/**
	 * When split topic update count and last reply id and author id
	 *
	 * @param int $from_reply_id From Reply ID.
	 * @param int $source_topic_id Source Topic ID.
	 * @param int $destination_topic_id Destination Topic ID.
	 */
	public function event_bbp_post_split_topic( $from_reply_id, $source_topic_id, $destination_topic_id ) {
		$destination_forum_id = bbp_get_topic_forum_id( $destination_topic_id );
		Cache::instance()->purge_by_group( 'bbp-forums_' . $destination_forum_id );
	}

	/**
	 * When forum Group change form admin
	 *
	 * @param int $group_id Group id.
	 */
	public function event_bp_group_admin_edit_after( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}

	}

	/**
	 * When forum Group Details updated form Manage
	 *
	 * @param int $group_id Group id.
	 */
	public function event_groups_group_details_edited( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * When forum Group setting updated form Manage
	 *
	 * @param int $group_id Group id.
	 */
	public function event_groups_group_settings_edited( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * When Group forums setting Manage
	 *
	 * @param int $group_id Group id.
	 */
	public function event_bp_group_admin_after_edit_screen_save( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * When forum Group avarar updated form Manage
	 *
	 * @param int $group_id Group id.
	 */
	public function event_groups_avatar_uploaded( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * When forum Group cover photo uploaded form Manage
	 *
	 * @param int $group_id Group id.
	 */
	public function event_groups_cover_image_uploaded( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * When forum Group cover photo deleted form Manage
	 *
	 * @param int $group_id Group id.
	 */
	public function event_groups_cover_image_deleted( $group_id ) {
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * User updated on site
	 *
	 * @param int $user_id User ID.
	 */
	public function event_profile_update( $user_id ) {
		$forum_ids = $this->get_forum_ids_by_userid( $user_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * User deleted on site
	 *
	 * @param int $user_id User ID.
	 */
	public function event_deleted_user( $user_id ) {
		$forum_ids = $this->get_forum_ids_by_userid( $user_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * User avatar photo updated
	 *
	 * @param int $user_id User ID.
	 */
	public function event_xprofile_avatar_uploaded( $user_id ) {
		$forum_ids = $this->get_forum_ids_by_userid( $user_id );
		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
			}
		}
	}

	/**
	 * User avatar photo deleted
	 *
	 * @param array $args Array of arguments used for avatar deletion.
	 */
	public function event_bp_core_delete_existing_avatar( $args ) {
		$user_id = ! empty( $args['item_id'] ) ? absint( $args['item_id'] ) : 0;
		if ( ! empty( $user_id ) ) {
			if ( isset( $args['object'] ) && 'user' === $args['object'] ) {
				$forum_ids = $this->get_forum_ids_by_userid( $user_id );
				if ( ! empty( $forum_ids ) ) {
					foreach ( $forum_ids as $forum_id ) {
						Cache::instance()->purge_by_group( 'bbp-forums_' . $forum_id );
					}
				}
			}
		}
	}


	/**
	 * Get Activities ids from user name.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array
	 */
	private function get_forum_ids_by_userid( $user_id ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='forum' AND post_author = %d", $user_id );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_col( $sql );
	}
}
