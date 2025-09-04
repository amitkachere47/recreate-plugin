<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
namespace Crazy_Automator_Pro;

use Crazy_Automator\Recipe;

/**
 * Class CRAZY_AMELIA_APPOINTMENT_BOOKED_SERVICE_CANCELLED
 *
 * @package Crazy_Automator
 */
class CRAZY_AMELIA_APPOINTMENT_BOOKED_SERVICE_CANCELLED {

	use Recipe\Triggers;

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	const TRIGGER_CODE = 'AMELIA_APPOINTMENT_BOOKED_SERVICE_CANCELLED';

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	const TRIGGER_META = 'AMELIA_APPOINTMENT_BOOKED_SERVICE_CANCELLED_META';

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {

		// Only enable in Amelia Pro.
		if ( ! defined( 'AMELIA_LITE_VERSION' ) ) {

			$this->setup_trigger();

		}

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function setup_trigger() {

		// Bailout if helpers from base Automator is not found.
		if ( is_null( Crazy_Automator()->helpers->recipe->ameliabooking ) ) {
			return;
		}

		$this->set_integration( 'AMELIABOOKING' );
		$this->set_trigger_code( self::TRIGGER_CODE );
		$this->set_trigger_meta( self::TRIGGER_META );
		$this->set_is_pro( true );
		$this->set_is_login_required( false );
		$this->set_trigger_type( 'anonymous' );

		// The action hook to attach this trigger into.
		$this->add_action( 'AmeliaBookingCanceled' );

		// The number of arguments that the action hook accepts.
		$this->set_action_args_count( 3 );

		$this->set_sentence(
			sprintf(
			/* Translators: Trigger sentence */
				esc_html__( 'A booking of an appointment for {{a specific service:%1$s}} is canceled', 'crazy-automator' ),
				$this->get_trigger_meta()
			)
		);

		$this->set_readable_sentence(
		/* Translators: Trigger sentence */
			esc_html__( 'A booking of an appointment for {{a specific service}} is canceled', 'crazy-automator' )
		);

		// Set the options field group.
		$this->set_options_callback( array( $this, 'load_options' ) );

		// Register the trigger.
		$this->register_trigger();

	}

	/**
	 * @return array
	 */
	public function load_options() {
		return Crazy_Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => $this->get_trigger_option_fields(),
			)
		);
	}

	/**
	 * Validate the trigger.
	 *
	 * Return false if returned booking data is empty.
	 */
	public function validate_trigger( ...$args ) {

		$reservation = array_shift( $args );

		$reservation = isset( $reservation[0] ) ? $reservation[0] : null;

		return 'appointment' === (string) $reservation['type'] && 'canceled' === (string) $reservation['status'];

	}

	/**
	 * Prepare to run.
	 *
	 * Sets the conditional trigger to true.
	 *
	 * @return void.
	 */
	public function prepare_to_run( $data ) {

		$this->set_conditional_trigger( true );

	}

	/**
	 * Trigger conditions.
	 *
	 * Only run the trigger if service is set to 'Any' or if service id is equals to the one set in the recipe.
	 *
	 * @return void.
	 */
	protected function trigger_conditions( $args ) {

		// Grab the returned booking data.
		$booking_data = $args[0];

		// Match 'Any services' condition.
		$this->do_find_any( true );

		// Match specific condition.
		$this->do_find_this( $this->get_trigger_meta() );

		$this->do_find_in( array( $booking_data['serviceId'] ) );

	}

	/**
	 * The trigger options fields.
	 *
	 * @return array The field options.
	 */
	public function get_trigger_option_fields() {

		return Crazy_Automator()->helpers->recipe->ameliabooking->options->get_option_fields(
			$this->get_trigger_code(),
			$this->get_trigger_meta()
		);

	}

	/**
	 * Continue trigger process even for logged-in user.
	 *
	 * @return boolean True.
	 */
	public function do_continue_anon_trigger( ...$args ) {

		return true;

	}

}
