<?php
namespace Woocommerce\Pagarme\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

//WooCommerce
use WC_Payment_Gateway;
use WC_Order;
use WC_Payment_Gateway_CC;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Gateway;

class Gateways extends WC_Payment_Gateway
{
	/**
	 * @var Object
	 */
	public $model;

	public function __construct()
	{
		$this->model = new Gateway();

		$this->id                 = 'woo-mundipagg-payments';
		$this->method_title       = __( 'MundiPagg Payments', 'woo-mundipagg-payments' );
		$this->method_description = __( 'Payment Gateway MundiPagg', 'woo-mundipagg-payments' );
		$this->has_fields         = false;
		$this->icon               = Core::plugins_url( 'assets/images/logo.png' );

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled     = $this->get_option( 'enabled', 'no' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you_page' ) );
	}

	/**
	 * Output the admin options table.
	 *
	 * @since 1.0
	 * @param null
	 * @return Void
	 */
	public function admin_options()
	{
		printf(
			'<h3>%s</h3>
			 <div %s>
			 	<table class="form-table">%s</table>
			 </div>',
			__( 'General', 'woo-mundipagg-payments' ),
			Utils::get_component( 'settings' ),
			$this->generate_settings_html( $this->get_form_fields(), false )
		);
	}

	/**
	 * Return the name of the option in the WP DB.
	 * @since 1.0
	 * @return string
	 */
	public function get_option_key()
	{
		return $this->model->settings->get_option_key();
	}

	public function is_available()
	{
		return ( $this->model->settings->is_enabled() && ! $this->get_errors() && $this->model->supported_currency() );
	}

	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled'                           => $this->field_enabled(),
			'title'                             => $this->field_title(),
			'description'                       => $this->field_description(),
			'environment'                       => $this->field_environment(),
			'sandbox_secret_key'                => $this->field_sandbox_secret_key(),
			'sandbox_public_key'                => $this->field_sandbox_public_key(),
			'production_secret_key'             => $this->field_production_secret_key(),
			'production_public_key'             => $this->field_production_public_key(),
			'section_payment_settings'          => $this->section_payment_settings(),
			'enable_billet'                     => $this->field_enable_billet(),
			'enable_credit_card'                => $this->field_enable_credit_card(),
			'multimethods_billet_card'          => $this->field_multimethods_billet_card(),
			'multimethods_2_cards'              => $this->field_multimethods_2_cards(),
			'multicustomers'                    => $this->field_multicustomers(),
			'section_antifraud'                 => $this->section_antifraud(),
			'antifraud_enabled'                 => $this->antifraud_enabled(),
			'antifraud_min_value'               => $this->antifraud_min_value(),
			'section_billet'                    => $this->section_billet(),
			'billet_bank'                       => $this->field_billet_bank(),
			'billet_deadline_days'              => $this->field_billet_deadline_days(),
			'billet_instructions'               => $this->field_billet_instructions(),
			'section_credit_card'               => $this->section_credit_card(),
			'cc_soft_descriptor'                => $this->field_cc_soft_descriptor(),
			'cc_operation_type'                 => $this->field_cc_operation_type(),
			'cc_allow_save'                     => $this->field_cc_allow_save(),
			'cc_flags'                          => $this->field_cc_flags(),
			'cc_installment_type'               => $this->field_cc_installment_type(),
			'cc_installments_maximum'           => $this->field_cc_installment_fields( 'maximum' ),
			'cc_installments_without_interest'  => $this->field_cc_installment_fields( 'without_interest' ),
			'cc_installments_interest'          => $this->field_cc_installment_fields( 'interest' ),
			'cc_installments_interest_increase' => $this->field_cc_installment_fields( 'interest_increase' ),
			'cc_installments_by_flag'           => $this->field_cc_installment_fields( 'flags' ),
			'section_tools'                     => $this->section_tools(),
			'enable_logs'                       => $this->field_enabled_logs(),
		);
	}

	public function process_payment( $order_id )
	{
		$wc_order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $wc_order->get_checkout_payment_url( true ),
		);
	}

	public function receipt_page( $order_id )
	{
		$this->checkout_transparent( $order_id );
	}

	public function thank_you_page( $order_id )
	{
		$order = new WC_Order( $order_id );

		require_once Core::get_file_path( 'thank-you-page.php', 'templates/' );
	}

	public function checkout_transparent( $order_id )
	{
		$wc_order = new WC_Order( $order_id );

		require_once Core::get_file_path( 'main.php', 'templates/checkout/' );
	}

	public function section_payment_settings()
	{
		return array(
			'title' => __( 'Payment settings', 'woo-mundipagg-payments' ),
			'type'  => 'title',
		);
	}

	public function field_enabled()
	{
		return array(
			'title'   => __( 'Enable', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable payment', 'woo-mundipagg-payments' ),
			'default' => 'no',
		);
	}

	public function field_title()
	{
		return array(
			'title'       => __( 'Title', 'woo-mundipagg-payments' ),
			'description' => __( 'This the title which the user sees during checkout.', 'woo-mundipagg-payments' ),
			'desc_tip'    => true,
			'default'     => __( 'MundiPagg', 'woo-mundipagg-payments' ),
		);
	}

	public function field_description()
	{
		return array(
			'title'   => __( 'Description', 'woo-mundipagg-payments' ),
			'default' => __( 'Pay with Mundipagg', 'woo-mundipagg-payments' ),
		);
	}

	public function field_environment()
	{
		return array(
			'type'    => 'select',
			'title'   => __( 'Environment', 'woo-mundipagg-payments' ),
			'class'   => 'wc-enhanced-select',
			'default' => 'sandbox',
			'options' => array(
				'sandbox'    => 'Sandbox',
				'production' => __( 'Production', 'woo-mundipagg-payments' ),
			),
			'custom_attributes' => array(
				'data-action'  => 'environment',
				'data-element' => 'environment-select',
			),
		);
	}

	public function field_sandbox_secret_key()
	{
		return array(
			'title'             => __( 'Sandbox Secret Key', 'woo-mundipagg-payments' ),
			'custom_attributes' => array(
				'data-field' => 'sandbox-secret-key',
			),
		);
	}

	public function field_sandbox_public_key()
	{
		return array(
			'title'             => __( 'Sandbox Public Key', 'woo-mundipagg-payments' ),
			'custom_attributes' => array(
				'data-field' => 'sandbox-public-key',
			),
		);
	}

	public function field_production_secret_key()
	{
		return array(
			'title'             => __( 'Production Secret Key', 'woo-mundipagg-payments' ),
			'custom_attributes' => array(
				'data-field' => 'production-secret-key',
			),
		);
	}

	public function field_production_public_key()
	{
		return array(
			'title'             => __( 'Production Public Key', 'woo-mundipagg-payments' ),
			'custom_attributes' => array(
				'data-field' => 'production-public-key',
			),
		);
	}

	public function field_enable_billet()
	{
		return array(
			'title'   => __( 'Billet Banking', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Billet Banking', 'woo-mundipagg-payments' ),
			'default' => 'yes',
		);
	}

	public function field_enable_credit_card()
	{
		return array(
			'title'   => __( 'Credit Card', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Credit Card', 'woo-mundipagg-payments' ),
			'default' => 'yes',
		);
	}

	public function field_multimethods_billet_card()
	{
		return array(
			'title'   => __( 'Multimethods </br>(Billet + Credit Card)', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Multimethods (Billet + Credit Card)', 'woo-mundipagg-payments' ),
			'default' => 'no',
		);
	}

	public function field_multimethods_2_cards()
	{
		return array(
			'title'   => __( 'Multimethods </br>(2 Credit Cards)', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Multimethods (2 Credit Cards)', 'woo-mundipagg-payments' ),
			'default' => 'no',
		);
	}

	public function field_multicustomers()
	{
		return array(
			'title'   => __( 'Multicustomers', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Multicustomers', 'woo-mundipagg-payments' ),
			'default' => 'no',
		);
	}

	public function section_antifraud()
	{
		return array(
			'title' => __( 'Anti-fraud settings', 'woo-mundipagg-payments' ),
			'type'  => 'title',
		);
	}

	public function antifraud_enabled()
	{
		return array(
			'title'   => __( 'Enable', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable anti-fraud', 'woo-mundipagg-payments' ),
			'default' => 'no',
		);
	}

	public function antifraud_min_value()
	{
		return array(
			'title'             => __( 'Minimum value', 'woo-mundipagg-payments' ),
			'type'              => 'text',
			'description'       => __( 'Minimum anti-fraud value', 'woo-mundipagg-payments' ),
			'desc_tip'          => true,
			'placeholder'       => 'Ex.: 100,00',
			'custom_attributes' => array(
				'data-mask'         => '#.##0,00',
				'data-mask-reverse' => 'true',
			),
		);
	}

	public function section_billet()
	{
		return array(
			'title' => __( 'Billet settings', 'woo-mundipagg-payments' ),
			'type'  => 'title',
		);
	}

	public function field_billet_bank()
	{
		return array(
			'type'    => 'select',
			'title'   => __( 'Bank', 'woo-mundipagg-payments' ),
			'class'   => 'wc-enhanced-select',
			'default' => 0,
			'options' => array(
				''    => __( 'Select a bank', 'woo-mundipagg-payments' ),
				'341' => 'Banco Itaú S.A.',
				'237' => 'Banco Bradesco S.A.',
				'033' => 'Banco Santander S.A.',
				'745' => 'Banco Citibank S.A.',
				'001' => 'Banco do Brasil S.A.',
				'104' => 'Caixa Econômica Federal',
			),
		);
	}

	public function field_billet_deadline_days()
	{
		return array(
			'title'       => __( 'Number of Days', 'woo-mundipagg-payments' ),
			'description' => __( 'Days of expiry of the billet after printed.', 'woo-mundipagg-payments' ),
			'desc_tip'    => true,
			'placeholder' => 5,
			'default'     => 5,
		);
	}

	public function field_billet_instructions()
	{
		return array(
			'title'       => __( 'Instructions', 'woo-mundipagg-payments' ),
			'type'        => 'text',
			'description' => __( 'Instructions for the billet.', 'woo-mundipagg-payments' ),
			'desc_tip'    => true,
		);
	}

	public function section_credit_card()
	{
		return array(
			'title' => __( 'Credit Card settings', 'woo-mundipagg-payments' ),
			'type'  => 'title',
		);
	}

	public function field_cc_soft_descriptor()
	{
		return array(
			'title'             => __( 'Soft Descriptor', 'woo-mundipagg-payments' ),
			'desc_tip'          => true,
			'placeholder'       => __( 'Maximum of 13 characters', 'woo-mundipagg-payments' ),
			'description'       => __( 'It allows the shopkeeper to send a text of up to 13 characters that will be printed on the bearer\'s invoice, next to the shop identification, respecting the length of the flags.', 'woo-mundipagg-payments' ),
			'custom_attributes' => array(
				'data-action'    => 'soft-descriptor',
				'data-element'   => 'validate',
				'maxlength'      => 13,
				'data-error-msg' => __( 'This field is required.', 'woo-mundipagg-payments' ),
			),
		);
	}

	public function field_cc_operation_type()
	{
		return array(
			'type'    => 'select',
			'title'   => __( 'Operation Type', 'woo-mundipagg-payments' ),
			'class'   => 'wc-enhanced-select',
			'default' => 1,
			'options' => array(
				1 => __( 'Authorize', 'woo-mundipagg-payments' ),
				2 => __( 'Authorize and Capture', 'woo-mundipagg-payments' ),
			),
		);
	}

	public function field_cc_allow_save()
	{
		return array(
			'title'   => __( 'Enable storage', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Allow card salvage for future purchases', 'woo-mundipagg-payments' ),
			'default' => 'yes',
		);
	}

	public function field_cc_manual_capture()
	{
		return array(
			'title'   => __( 'Manual Capture', 'woo-mundipagg-payments' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Manual Capture', 'woo-mundipagg-payments' ),
			'default' => 'yes',
		);
	}

	public function field_cc_flags()
	{
		return array(
			'type'              => 'multiselect',
			'title'             => __( 'Flags', 'woo-mundipagg-payments' ),
			'select_buttons'    => false,
			'class'             => 'wc-enhanced-select',
			'desc_tip'          => __( 'Select one or more flags', 'woo-mundipagg-payments' ),
			'options'           => $this->model->settings->get_flags_list(),
			'custom_attributes' => array(
				'data-element' => 'flags-select',
				'data-action'  => 'flags',
			),
		);
	}

	public function field_cc_installment_type()
	{
		return array(
			'title'   => __( 'Installment type', 'woo-mundipagg-payments' ),
			'type'    => 'select',
			'class'   => 'wc-enhanced-select',
			'label'   => __( 'Choose the installment type', 'woo-mundipagg-payments' ),
			'default' => 1,
			'options' => array(
				Gateway::CC_TYPE_SINGLE  => __( 'Single installment', 'woo-mundipagg-payments' ),
				Gateway::CC_TYPE_BY_FLAG => __( 'Installment by flags', 'woo-mundipagg-payments' ),
			),
			'custom_attributes' => array(
				'data-element' => 'installments-type-select',
				'data-action'  => 'installments-type',
			),
		);
	}

	public function field_cc_installment_fields( $field )
	{
		$installments = array();

		$installments['maximum'] = array(
			'title'             => __( 'Maximum installments number', 'woo-mundipagg-payments' ),
			'type'              => 'select',
			'description'       => __( 'Force a maximum number of installments for payment.', 'woo-mundipagg-payments' ),
			'desc_tip'          => true,
			'default'           => 12,
			'options'           => $this->model->get_installment_options(),
			'custom_attributes' => array(
				'data-field' => 'installments-maximum',
			),
		);

		$installments['without_interest'] = array(
			'title'             => __( 'Without Interest', 'woo-mundipagg-payments' ),
			'type'              => 'select',
			'description'       => __( 'Defines which installment will have no interest.', 'woo-mundipagg-payments' ),
			'desc_tip'          => true,
			'default'           => 3,
			'options'           => $this->model->get_installment_options(),
			'custom_attributes' => array(
				'data-field' => 'installments-without-interest',
			),
		);

		$installments['interest'] = array(
			'title'             => __( 'Initial interest', 'woo-mundipagg-payments' ),
			'type'              => 'text',
			'description'       => __( 'Interest to be applied to the installment.', 'woo-mundipagg-payments' ),
			'desc_tip'          => true,
			'placeholder'       => '0,00',
			'custom_attributes' => array(
				'data-field'        => 'installments-interest',
				'data-mask'         => '##0,00',
				'data-mask-reverse' => 'true',
			),
		);

		$installments['interest_increase'] = array(
			'title'             => __( 'Interest increase', 'woo-mundipagg-payments' ),
			'type'              => 'text',
			'description'       => __( 'Interest to be increamented for each installment.', 'woo-mundipagg-payments' ),
			'desc_tip'          => true,
			'placeholder'       => '0,00',
			'custom_attributes' => array(
				'data-field'        => 'installments-interest-increase',
				'data-mask'         => '##0,00',
				'data-mask-reverse' => 'true',
			),
		);

		$installments['flags'] = array(
			'title' => __( 'Settings by flag', 'woo-mundipagg-payments' ),
			'type'  => 'installments_by_flag',
		);

		return $installments[ $field ];
	}

	/**
	 * Get HTML for descriptions.
	 *
	 * @param  array $data
	 * @return string
	 */
	public function get_description_html( $data )
	{
		if ( $data['desc_tip'] === true ) {
			return;
		} elseif ( ! empty( $data['desc_tip'] ) ) {
			$description = $data['description'];
		} elseif ( ! empty( $data['description'] ) ) {
			$description = $data['description'];
		} else {
			return;
		}

		return sprintf(
			'<p class="description %s">%s</p>',
			sanitize_html_class( Utils::get_value_by( $data, 'class_p' ) ),
			strip_tags( $description, '<a><span>' )
		);
	}

	public function generate_installments_by_flag_html( $key, $data )
	{
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data  = wp_parse_args( $data, $defaults );
		$value = (array) $this->get_option( $key, array() );
		$flags = $this->model->settings->get_flags_list();

		ob_start();

		?>
		<style>
			.woocommerce table.form-table p.flag input.small-input { width: 150px; }
		</style>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $data ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset data-field="installments-by-flag">
					<?php
					foreach ( $flags as $flag_key => $flag_name ) :
						$interest          = isset( $value['interest'][ $flag_key ] ) ? $value['interest'][ $flag_key ] : '';
						$interest_increase = isset( $value['interest_increase'][ $flag_key ] ) ? $value['interest_increase'][ $flag_key ] : '';
						$max_installment   = isset( $value['max_installment'][ $flag_key ] ) ? $value['max_installment'][ $flag_key ] : 12;
						$no_interest       = isset( $value['no_interest'][ $flag_key ] ) ? $value['no_interest'][ $flag_key ] : 1;
					?>
					<p class="flag" data-flag="<?php echo $flag_key; ?>">

						<input class="small-input" type="text" value="<?php echo $flag_name; ?>"
							<?php disabled( 1, true ); ?> />

						<input class="small-input" type="number" min="1" max="12"
							name="<?php echo esc_attr( $field_key ); ?>[max_installment][<?php echo $flag_key; ?>]"
							id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo intval( $max_installment ); ?>" />

						<input class="small-input" type="number" min="1" max="12"
							name="<?php echo esc_attr( $field_key ); ?>[no_interest][<?php echo $flag_key; ?>]"
							id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo intval( $no_interest ); ?>" />

						<input class="small-input" type="text"
							placeholder="0,00"
							data-mask="##0,00" data-mask-reverse="true"
							name="<?php echo esc_attr( $field_key ); ?>[interest][<?php echo $flag_key; ?>]"
							id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price( $interest ) ?>" />%

						<input class="small-input" type="text"
							placeholder="0,00"
							data-mask="##0,00" data-mask-reverse="true"
							name="<?php echo esc_attr( $field_key ); ?>[interest_increase][<?php echo $flag_key; ?>]"
							id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price( $interest_increase ) ?>" />%
					</p>
					<?php endforeach; ?>

					<?php echo $this->get_description_html( $data ); ?>

					</br><p class="description">
						<strong><?php _e( 'Columns', 'woo-mundipagg-payments' ); ?>:</strong>
						<?php _e( 'Flag', 'woo-mundipagg-payments' ); ?>,
						<?php _e( 'Max installment', 'woo-mundipagg-payments' ); ?>,
						<?php _e( 'No interest', 'woo-mundipagg-payments' ); ?>,
						<?php _e( 'Initial interest', 'woo-mundipagg-payments' ); ?>,
						<?php _e( 'Interest increase', 'woo-mundipagg-payments' ); ?>
					</p>

				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	public function validate_installments_by_flag_field( $key, $value )
	{
		return $value;
	}

	public function section_tools()
	{
		return array(
			'title' => __( 'Tools', 'woo-mundipagg-payments' ),
			'type'  => 'title',
		);
	}

	public function field_enabled_logs()
	{
		return array(
			'title'       => __( 'Logs', 'woo-mundipagg-payments' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable', 'woo-mundipagg-payments' ),
			'default'     => 'no',
			'description' => __( 'Log MundiPagg events, you can check this log in WooCommerce>Status>Logs.', 'woo-mundipagg-payments' ),
		);
	}
}
