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
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17h4"/><path d="M3 17h2"/><path d="M15 17h2"/><path d="M13 17V6H4v11"/><path d="M13 9h4l3 5v3h-2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
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
