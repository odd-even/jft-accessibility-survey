<?php
/**
 * Plugin Name:       JFT Accessibility Survey
 * Plugin URI:        https://github.com/odd-even/jft-accessibility-survey
 * Description:       Embeds the Jolly Farmer Transport accessibility survey on any page or post. Responses go to Google Sheets and/or email.
 * Version:           1.2.2
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Jolly Farmer Transport
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       jft-accessibility-survey
 *
 * @package JFT_Accessibility_Survey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JFT_SURVEY_VERSION', '1.2.2' );
define( 'JFT_SURVEY_PLUGIN_FILE', __FILE__ );
define( 'JFT_SURVEY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JFT_SURVEY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
final class JFT_Accessibility_Survey_Plugin {

	const OPTION_ENDPOINT    = 'jft_survey_google_endpoint';
	const OPTION_EMAIL_ON    = 'jft_survey_email_enabled';
	const OPTION_EMAIL_TO    = 'jft_survey_email_recipients';
	const OPTION_EMAIL_SUBJ  = 'jft_survey_email_subject';

	/** @var self|null */
	private static $instance = null;

	/** @var bool */
	private $assets_enqueued = false;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_ajax_jft_survey_submit', array( $this, 'handle_ajax_submission' ) );
		add_action( 'wp_ajax_nopriv_jft_survey_submit', array( $this, 'handle_ajax_submission' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( JFT_SURVEY_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
	}

	public function register_shortcode(): void {
		add_shortcode( 'jft_accessibility_survey', array( $this, 'render_shortcode' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$this->enqueue_assets();

		ob_start();
		include JFT_SURVEY_PLUGIN_DIR . 'templates/survey.php';
		return (string) ob_get_clean();
	}

	private function enqueue_assets(): void {
		if ( $this->assets_enqueued ) {
			return;
		}

		wp_enqueue_style(
			'jft-accessibility-survey',
			JFT_SURVEY_PLUGIN_URL . 'assets/survey.css',
			array(),
			JFT_SURVEY_VERSION
		);

		wp_enqueue_script(
			'jft-accessibility-survey',
			JFT_SURVEY_PLUGIN_URL . 'assets/survey.js',
			array(),
			JFT_SURVEY_VERSION,
			true
		);

		wp_localize_script(
			'jft-accessibility-survey',
			'jftSurveyConfig',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'ajaxAction'     => 'jft_survey_submit',
				'ajaxNonce'      => wp_create_nonce( 'jft_survey_submit' ),
				'sheetsEndpoint' => esc_url_raw( (string) get_option( self::OPTION_ENDPOINT, '' ) ),
				'storageKey'     => 'jft-accessibility-survey-v1',
				'version'        => JFT_SURVEY_VERSION,
			)
		);

		$this->assets_enqueued = true;
	}

	public function register_rest_routes(): void {
		register_rest_route(
			'jft-survey/v1',
			'/submit',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_submission' ),
				'permission_callback' => array( $this, 'verify_submit_nonce' ),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request object.
	 */
	public function verify_submit_nonce( WP_REST_Request $request ): bool {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		return (bool) wp_verify_nonce( is_string( $nonce ) ? $nonce : '', 'wp_rest' );
	}

	/**
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_submission( WP_REST_Request $request ) {
		$result = $this->process_submission( $request->get_json_params() );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Handle submissions via admin-ajax.php (works when REST API / wp-json is blocked).
	 */
	public function handle_ajax_submission(): void {
		if ( ! check_ajax_referer( 'jft_survey_submit', 'nonce', false ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Your session expired. Please refresh the page and try again.', 'jft-accessibility-survey' ),
				),
				403
			);
		}

		$raw     = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';
		$payload = is_string( $raw ) ? json_decode( $raw, true ) : null;

		$result = $this->process_submission( $payload );
		if ( is_wp_error( $result ) ) {
			$status = 400;
			$data   = $result->get_error_data();
			if ( is_array( $data ) && isset( $data['status'] ) ) {
				$status = (int) $data['status'];
			}
			wp_send_json(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				),
				$status
			);
		}

		wp_send_json( $result, 200 );
	}

	/**
	 * @param mixed $payload Submission payload.
	 * @return array<string, mixed>|WP_Error
	 */
	private function process_submission( $payload ) {
		if ( ! is_array( $payload ) || empty( $payload['answers'] ) || ! is_array( $payload['answers'] ) ) {
			return new WP_Error(
				'jft_invalid_payload',
				__( 'Invalid survey submission.', 'jft-accessibility-survey' ),
				array( 'status' => 400 )
			);
		}

		$email_sent       = false;
		$sheets_forwarded = false;
		$demo_mode        = false;

		if ( $this->is_email_enabled() ) {
			$email_sent = $this->send_notification_email( $payload );
			if ( ! $email_sent ) {
				return new WP_Error(
					'jft_email_failed',
					__( 'Your response could not be emailed. Please try again or contact the site administrator.', 'jft-accessibility-survey' ),
					array( 'status' => 500 )
				);
			}
		}

		$sheets_url = (string) get_option( self::OPTION_ENDPOINT, '' );
		if ( '' !== $sheets_url ) {
			$sheets_forwarded = $this->forward_to_google_sheets( $sheets_url, $payload );
			if ( ! $sheets_forwarded && ! $email_sent ) {
				return new WP_Error(
					'jft_sheets_failed',
					__( 'Your response could not be saved. Please try again.', 'jft-accessibility-survey' ),
					array( 'status' => 500 )
				);
			}
		}

		if ( ! $email_sent && ! $sheets_forwarded ) {
			$demo_mode = true;
		}

		return array(
			'success'          => true,
			'email_sent'       => $email_sent,
			'sheets_forwarded' => $sheets_forwarded,
			'demo_mode'        => $demo_mode,
		);
	}

	private function is_email_enabled(): bool {
		return (bool) get_option( self::OPTION_EMAIL_ON, false ) && ! empty( $this->get_recipient_emails() );
	}

	/**
	 * @return string[]
	 */
	private function get_recipient_emails(): array {
		$raw = (string) get_option( self::OPTION_EMAIL_TO, '' );
		$parts = preg_split( '/[\s,;]+/', $raw ) ?: array();
		$emails = array();

		foreach ( $parts as $part ) {
			$part = sanitize_email( trim( $part ) );
			if ( is_email( $part ) ) {
				$emails[] = $part;
			}
		}

		return array_values( array_unique( $emails ) );
	}

	/**
	 * @param array<string, mixed> $payload Submission payload.
	 */
	private function send_notification_email( array $payload ): bool {
		$recipients = $this->get_recipient_emails();
		if ( empty( $recipients ) ) {
			return false;
		}

		$subject = trim( (string) get_option( self::OPTION_EMAIL_SUBJ, '' ) );
		if ( '' === $subject ) {
			$subject = sprintf(
				/* translators: %s: site name */
				__( '[%s] New accessibility survey response', 'jft-accessibility-survey' ),
				wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
			);
		}

		$body    = $this->build_email_body( $payload );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		return (bool) wp_mail( $recipients, $subject, $body, $headers );
	}

	/**
	 * @param array<string, mixed> $payload Submission payload.
	 */
	private function build_email_body( array $payload ): string {
		$lines   = array();
		$lines[] = __( 'New JFT Accessibility Survey response', 'jft-accessibility-survey' );
		$lines[] = '';
		$lines[] = __( 'Submitted:', 'jft-accessibility-survey' ) . ' ' . (string) ( $payload['submitted_at'] ?? gmdate( 'c' ) );
		$lines[] = __( 'Site:', 'jft-accessibility-survey' ) . ' ' . home_url( '/' );
		$lines[] = '';

		foreach ( (array) ( $payload['answers'] ?? array() ) as $answer ) {
			if ( ! is_array( $answer ) ) {
				continue;
			}
			$lines[] = str_repeat( '-', 40 );
			$lines[] = (string) ( $answer['question'] ?? '' );
			$formatted = $this->format_answer_text( $answer );
			if ( '' === $formatted ) {
				$lines[] = __( '(no answer)', 'jft-accessibility-survey' );
			} else {
				foreach ( preg_split( "/\r\n|\r|\n/", $formatted ) as $line ) {
					$lines[] = $line;
				}
			}
			$lines[] = '';
		}

		return implode( "\n", $lines );
	}

	/**
	 * @param array<string, mixed> $answer Single answer object.
	 */
	private function format_answer_text( array $answer ): string {
		if ( isset( $answer['value'] ) && is_string( $answer['value'] ) && ! array_key_exists( 'selected', $answer ) ) {
			return $answer['value'];
		}

		if ( array_key_exists( 'label', $answer ) && ! array_key_exists( 'selected', $answer ) ) {
			$label = $answer['label'] ?? '';
			$value = $answer['value'] ?? '';
			return is_string( $label ) && '' !== $label ? $label : ( is_string( $value ) ? $value : '' );
		}

		if ( isset( $answer['selected'] ) && is_array( $answer['selected'] ) ) {
			$labels = array();
			if ( ! empty( $answer['labels'] ) && is_array( $answer['labels'] ) ) {
				foreach ( $answer['labels'] as $label ) {
					if ( is_string( $label ) && '' !== $label && 'Other' !== $label ) {
						$labels[] = $label;
					}
				}
			}
			if ( empty( $labels ) && ! empty( $answer['selected'] ) ) {
				$labels = array_map( 'strval', $answer['selected'] );
			}
			if ( ! empty( $answer['other'] ) && is_string( $answer['other'] ) ) {
				$labels[] = 'Other: ' . $answer['other'];
			}
			return implode( "\n", $labels );
		}

		return '';
	}

	/**
	 * @param string               $url     Google Apps Script URL.
	 * @param array<string, mixed> $payload Submission payload.
	 */
	private function forward_to_google_sheets( string $url, array $payload ): bool {
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Content-Type' => 'text/plain; charset=utf-8',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		return $code >= 200 && $code < 400;
	}

	public function register_settings(): void {
		register_setting(
			'jft_survey_settings',
			self::OPTION_ENDPOINT,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_endpoint' ),
				'default'           => '',
			)
		);

		register_setting(
			'jft_survey_settings',
			self::OPTION_EMAIL_ON,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => false,
			)
		);

		register_setting(
			'jft_survey_settings',
			self::OPTION_EMAIL_TO,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_recipients' ),
				'default'           => '',
			)
		);

		register_setting(
			'jft_survey_settings',
			self::OPTION_EMAIL_SUBJ,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
	}

	public function sanitize_checkbox( $value ): bool {
		return ! empty( $value );
	}

	public function sanitize_recipients( $value ): string {
		$parts  = preg_split( '/[\s,;]+/', (string) $value ) ?: array();
		$valid  = array();

		foreach ( $parts as $part ) {
			$part = sanitize_email( trim( $part ) );
			if ( is_email( $part ) ) {
				$valid[] = $part;
			}
		}

		return implode( ', ', array_unique( $valid ) );
	}

	public function sanitize_endpoint( $value ): string {
		$value = esc_url_raw( trim( (string) $value ) );
		if ( '' !== $value && 0 !== strpos( $value, 'https://script.google.com/' ) ) {
			add_settings_error(
				self::OPTION_ENDPOINT,
				'invalid_endpoint',
				__( 'The Google Apps Script URL must start with https://script.google.com/', 'jft-accessibility-survey' )
			);
			return (string) get_option( self::OPTION_ENDPOINT, '' );
		}
		return $value;
	}

	public function register_settings_page(): void {
		add_options_page(
			__( 'JFT Accessibility Survey', 'jft-accessibility-survey' ),
			__( 'JFT Survey', 'jft-accessibility-survey' ),
			'manage_options',
			'jft-accessibility-survey',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$endpoint     = (string) get_option( self::OPTION_ENDPOINT, '' );
		$email_on     = (bool) get_option( self::OPTION_EMAIL_ON, false );
		$email_to     = (string) get_option( self::OPTION_EMAIL_TO, '' );
		$email_subj   = (string) get_option( self::OPTION_EMAIL_SUBJ, '' );
		$admin_email  = (string) get_option( 'admin_email' );
		$nothing_set  = '' === $endpoint && ( ! $email_on || '' === $email_to );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'JFT Accessibility Survey', 'jft-accessibility-survey' ); ?></h1>
			<p><?php esc_html_e( 'Add the survey to any page or post with this shortcode:', 'jft-accessibility-survey' ); ?></p>
			<p><code>[jft_accessibility_survey]</code></p>
			<p class="description">
				<?php
				printf(
					/* translators: %s: plugin version number */
					esc_html__( 'Plugin version: %s', 'jft-accessibility-survey' ),
					esc_html( JFT_SURVEY_VERSION )
				);
				?>
			</p>
			<p class="description">
				<?php esc_html_e( 'Submissions use admin-ajax.php (compatible with IIS and hosts where /wp-json/ is blocked). Exclude the survey page from full-page caching so the security token stays valid.', 'jft-accessibility-survey' ); ?>
			</p>

			<?php if ( $nothing_set ) : ?>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'No Google Sheets URL or email notifications are configured — submissions will show the success screen but will not be saved anywhere.', 'jft-accessibility-survey' ); ?></p>
				</div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php settings_fields( 'jft_survey_settings' ); ?>

				<h2><?php esc_html_e( 'Google Sheets', 'jft-accessibility-survey' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="jft_survey_google_endpoint"><?php esc_html_e( 'Apps Script Web App URL', 'jft-accessibility-survey' ); ?></label>
						</th>
						<td>
							<input
								type="url"
								id="jft_survey_google_endpoint"
								name="<?php echo esc_attr( self::OPTION_ENDPOINT ); ?>"
								value="<?php echo esc_attr( $endpoint ); ?>"
								class="large-text code"
								placeholder="https://script.google.com/macros/s/…/exec"
							/>
							<p class="description">
								<?php esc_html_e( 'Optional. Each submission is forwarded to this URL and appended as a row in your Sheet.', 'jft-accessibility-survey' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Email notifications', 'jft-accessibility-survey' ); ?></h2>
				<p class="description">
					<?php
					printf(
						/* translators: %s: WordPress admin email address */
						esc_html__( 'Uses WordPress wp_mail() — typically the same mail setup as your site. Emails are sent from %s unless your mail plugin overrides it.', 'jft-accessibility-survey' ),
						'<code>' . esc_html( $admin_email ) . '</code>'
					);
					?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Send email', 'jft-accessibility-survey' ); ?></th>
						<td>
							<label for="jft_survey_email_enabled">
								<input
									type="checkbox"
									id="jft_survey_email_enabled"
									name="<?php echo esc_attr( self::OPTION_EMAIL_ON ); ?>"
									value="1"
									<?php checked( $email_on ); ?>
								/>
								<?php esc_html_e( 'Email me when someone submits the survey', 'jft-accessibility-survey' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="jft_survey_email_recipients"><?php esc_html_e( 'Send to', 'jft-accessibility-survey' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="jft_survey_email_recipients"
								name="<?php echo esc_attr( self::OPTION_EMAIL_TO ); ?>"
								value="<?php echo esc_attr( $email_to ); ?>"
								class="large-text"
								placeholder="<?php echo esc_attr( $admin_email ); ?>"
							/>
							<p class="description">
								<?php esc_html_e( 'One or more email addresses, separated by commas.', 'jft-accessibility-survey' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="jft_survey_email_subject"><?php esc_html_e( 'Subject line', 'jft-accessibility-survey' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="jft_survey_email_subject"
								name="<?php echo esc_attr( self::OPTION_EMAIL_SUBJ ); ?>"
								value="<?php echo esc_attr( $email_subj ); ?>"
								class="large-text"
								placeholder="<?php esc_attr_e( '[Your Site] New accessibility survey response', 'jft-accessibility-survey' ); ?>"
							/>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param string[] $links Plugin action links.
	 * @return string[]
	 */
	public function plugin_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=jft-accessibility-survey' ) ),
			esc_html__( 'Settings', 'jft-accessibility-survey' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

JFT_Accessibility_Survey_Plugin::instance();

register_activation_hook(
	JFT_SURVEY_PLUGIN_FILE,
	static function (): void {
		JFT_Accessibility_Survey_Plugin::instance()->register_rest_routes();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	JFT_SURVEY_PLUGIN_FILE,
	static function (): void {
		flush_rewrite_rules();
	}
);
