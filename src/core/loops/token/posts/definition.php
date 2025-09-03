<?php
namespace Uncanny_Automator_Pro\Loops\Token\Posts;

/**
 * Posts tokens definitions.
 *
 * @since 5.3
 *
 * @package Uncanny_Automator_Pro\Loops\Token
 */
final class Definition {

	/**
	 * Registers the tokens.
	 *
	 * @return void
	 */
	public static function register_hooks() {
		add_filter( 'automator_recipe_main_object_loop_tokens_items', array( new self(), 'list' ), 10, 2 );
	}

	/**
	 * List all tokens.
	 *
	 * @param string[] $tokens
	 * @param \Uncanny_Automator\Services\Recipe\Structure\Actions\Item\Loop $loop
	 *
	 * @return mixed[]
	 */
	public function list( $tokens, $loop ) {

		$id = $loop->get( 'id' );

		$iterable_expr = $loop->get( 'iterable_expression' );

		if ( ! isset( $iterable_expr['type'] ) ) {
			return $tokens;
		}

		if ( 'posts' !== $iterable_expr['type'] ) {
			return $tokens;
		}

		$tokens[] = array(
			'data_type'  => 'int',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_ID',
			'name'       => _x( 'Post ID', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'text',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_TITLE',
			'name'       => _x( 'Post title', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'text',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_DATE',
			'name'       => _x( 'Post date', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'text',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_CONTENT',
			'name'       => _x( 'Post content', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'text',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_EXCERPT',
			'name'       => _x( 'Post excerpt', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'text',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_STATUS',
			'name'       => _x( 'Post status', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'text',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_TYPE',
			'name'       => _x( 'Post type', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		$tokens[] = array(
			'data_type'  => 'int',
			'id'         => 'TOKEN_EXTENDED:LOOP_TOKEN:' . $id . ':POSTS:POST_AUTHOR_ID',
			'name'       => _x( 'Post author ID', 'Post loop token', 'uncanny-automator-pro' ),
			'token_type' => 'custom',
		);

		return $tokens;
	}
}
