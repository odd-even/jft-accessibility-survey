<?php
/**
 * Survey markup template.
 *
 * @package JFT_Accessibility_Survey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="jft-survey">
	<div class="jft-shell">
		<header class="jft-header">
			<span class="jft-badge">
				<svg viewBox="0 0 100.6 73.16" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M86.88,18.27h-13.71l-.01-18.27L8.95.04C3.86.04-.04,4.35,0,9.35v50.08l9.16.01c0,7.73,6.3,13.77,13.8,13.72,7.43-.05,13.63-6.06,13.62-13.73h27.44c0,7.74,6.3,13.78,13.8,13.73,7.44-.05,13.61-6.04,13.63-13.72l9.15-.01v-22.85l-13.72-18.31ZM23.75,66.22c-3.81.48-7.13-2.21-7.64-5.81-.53-3.78,2.11-7.35,6.07-7.75,3.93-.4,7.07,2.47,7.47,6.04.43,3.77-2.33,7.06-5.9,7.52ZM66.66,46.88H6.34V11.45c0-2.98,2.41-5.39,5.38-5.39h54.94v40.82ZM78.61,66.22c-3.81.48-7.13-2.21-7.64-5.81-.53-3.78,2.12-7.35,6.08-7.75,3.92-.4,7.07,2.47,7.47,6.04.42,3.77-2.33,7.06-5.91,7.52ZM73.17,36.57v-11.43l11.41.01,8.88,11.37-20.29.05Z"/></svg>
				Jolly Farmer Transport
			</span>
			<h1 class="jft-title">Accessibility Survey</h1>
		</header>

		<form id="jft-form" class="jft-card" novalidate>
			<div class="jft-progress-wrap" id="jft-progress-wrap" hidden>
				<div class="jft-progress-meta">
					<span id="jft-step-label" aria-live="polite"><?php esc_html_e( 'Question 1 of 7', 'jft-accessibility-survey' ); ?></span>
					<span><strong id="jft-percent">0%</strong> <?php esc_html_e( 'complete', 'jft-accessibility-survey' ); ?></span>
				</div>
				<div class="jft-progress-track" role="progressbar" aria-label="<?php esc_attr_e( 'Survey progress', 'jft-accessibility-survey' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="jft-progress-role">
					<div class="jft-progress-bar" id="jft-progress-bar"></div>
				</div>
			</div>

			<div class="jft-body" id="jft-body"></div>

			<div class="jft-footer" id="jft-footer" hidden>
				<button type="button" class="jft-btn jft-btn-ghost" id="jft-back">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
					<?php esc_html_e( 'Back', 'jft-accessibility-survey' ); ?>
				</button>
				<button type="button" class="jft-btn jft-btn-primary" id="jft-next">
					<?php esc_html_e( 'Next', 'jft-accessibility-survey' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
				</button>
				<button type="submit" class="jft-btn jft-btn-primary" id="jft-submit" hidden>
					<?php esc_html_e( 'Submit survey', 'jft-accessibility-survey' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
				</button>
			</div>
		</form>

		<p class="jft-footnote"><?php esc_html_e( 'Your responses are confidential and help us improve accessibility for everyone.', 'jft-accessibility-survey' ); ?></p>
	</div>
</div>
