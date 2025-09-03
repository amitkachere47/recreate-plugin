<?php

namespace Uncanny_Automator_Pro\Loops\Token\Posts;

use Uncanny_Automator_Pro\Loops\Token\Text_Parseable;
use WP_Post;

/**
 * Posts tokens
 *
 * @since 5.3
 *
 * @package Uncanny_Automator_Pro\Loops\Token
 */
final class Parser extends Text_Parseable {

	/**
	 * The regexp pattern.
	 *
	 * @var string $pattern
	 */
	protected $pattern = '/{{TOKEN_EXTENDED:LOOP_TOKEN:\d+:POSTS:[^}]+}}/';

	public function parse( $entity_id, $extracted_token ) {

		$wp_post = $this->get_post( $entity_id );

		// Make sure $wp_user is a valid user entity before proceeding.
		if ( ! $wp_post instanceof WP_Post ) {
			return '';
		}

		return $this->get_post_property( $wp_post, $extracted_token );
	}

	/**
	 * Retrieves the post from the run time cache or from the database.
	 *
	 * @param int $post_id
	 *
	 * @return WP_Post|mixed[]|null
	 */
	protected function get_post( $post_id ) {

		$tag   = self::$parser_filter_tag . '_' . $post_id;
		$group = self::$parser_filter_tag . '_group';

		$post_entity_cached = wp_cache_get( $tag, $group, true );

		if ( false !== $post_entity_cached && $post_entity_cached instanceof WP_Post ) {
			return $post_entity_cached;
		}

		$post_entity = get_post( $post_id, OBJECT );

		wp_cache_set( $tag, $post_entity, $group );

		return $post_entity;

	}

	/**
	 * Retrieves the actual token value from the token identifier.
	 *
	 * @param WP_Post $post
	 * @param string $token_id
	 *
	 * @return int|string|void
	 */
	protected function get_post_property( WP_Post $post, $token_id ) {

		$token_id_lowered = strtolower( $token_id );

		switch ( $token_id_lowered ) {
			case 'post_id':
				return $post->ID;
			case 'post_author_id':
				return $post->post_author;
			default:
				return $post->$token_id_lowered;
		}
	}

}
