<?php

namespace Crazy_Automator_Pro;

/**
 * Class WC_USER_BILLINGINFO_CONTAINS_VALUE
 *
 * @package Crazy_Automator_Pro
 */
class WC_USER_BILLINGINFO_CONTAINS_VALUE extends Action_Condition {

	/**
	 * Define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration = 'WC';
		/*translators: Token */
		$this->name = __( "The user's {{billing information}} {{contains}} {{a specific value}}", 'crazy-automator' );
		$this->code = 'BILLINGINFO_CONTAINS_VALUE';
		/*translators: A token matches a value */
		$this->dynamic_name  = sprintf( esc_html__( "The user's billing {{information:%1\$s}} {{contains:%2\$s}} {{a specific value:%3\$s}}", 'crazy-automator' ), 'BILLING_FIELD', 'BILLING_CONDITION', 'BILLING_FIELD_VALUE' );
		$this->requires_user = true;
	}

	/**
	 * fields
	 *
	 * @return array
	 */
	public function fields() {

		$shipping_field_args = array(
			'option_code'           => 'BILLING_FIELD',
			'label'                 => esc_html__( 'Information', 'crazy-automator' ),
			'required'              => true,
			'options'               => $this->wc_billinginfo_fields(),
			'supports_custom_value' => true,
			'options_show_id'       => false,
		);

		return array(
			// Product field
			$this->field->select_field_args( $shipping_field_args ),
			$this->field->select_field_args(
				array(
					'option_code'           => 'BILLING_CONDITION',
					'label'                 => esc_html__( 'Condition', 'crazy-automator' ),
					'required'              => true,
					'options'               => array(
						array(
							'value' => 'contains',
							'text'  => __( 'contains', 'crazy-automator' ),
						),
						array(
							'value' => 'not_contains',
							'text'  => __( 'does not contain', 'crazy-automator' ),
						),
					),
					'supports_custom_value' => false,
					'options_show_id'       => false,
				)
			),
			$this->field->text(
				array(
					'option_code' => 'BILLING_FIELD_VALUE',
					'label'       => esc_html__( 'Value', 'crazy-automator' ),
					'required'    => true,
				)
			),
		);
	}

	/**
	 * @return array[]
	 */
	public function wc_billinginfo_fields() {

		$options = array(
			'billing_address_1' => __( 'Address line 1', 'crazy-automator' ),
			'billing_address_2' => __( 'Address line 2', 'crazy-automator' ),
			'billing_city'      => __( 'City', 'crazy-automator' ),
			'billing_state'     => __( 'State', 'crazy-automator' ),
			'billing_country'   => __( 'Country', 'crazy-automator' ),
			'billing_email'     => __( 'Email', 'crazy-automator' ),
			'billing_phone'     => __( 'Phone', 'crazy-automator' ),
			'billing_company'   => __( 'Company', 'crazy-automator' ),
		);

		foreach ( $options as $id => $text ) {
			$return[] = array(
				'value' => $id,
				'text'  => $text,
			);
		}

		return $return;
	}

	/**
	 * Evaluate_condition
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function evaluate_condition() {

		$condition     = $this->get_parsed_option( 'BILLING_CONDITION' );
		$billing_field = $this->get_parsed_option( 'BILLING_FIELD' );
		$billing_value = $this->get_parsed_option( 'BILLING_FIELD_VALUE' );
		$user_id       = $this->user_id;
		$user_meta     = get_user_meta( $user_id, $billing_field, true );
		$position      = strpos( strtolower( $user_meta ), strtolower( $billing_value ) );
		switch ( $condition ) {
			case 'contains':
				if ( false === $position ) {
					$message = __( 'Billing info does not contain value: ', 'crazy-automator' ) . $billing_value;
					$this->condition_failed( $message );
				}
				break;
			case 'not_contains':
				if ( is_numeric( $position ) ) {
					$message = __( 'Billing info contains value: ', 'crazy-automator' ) . $billing_value;
					$this->condition_failed( $message );
				}
				break;
		}
	}
}
