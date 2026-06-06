=== JFT Accessibility Survey ===
Contributors: jollyfarmertransport
Tags: survey, accessibility, form, google sheets
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embeds the Jolly Farmer Transport accessibility survey on any WordPress page or post. Submissions can go to Google Sheets, email, or both.

== Description ==

A fully accessible, multi-step survey with progress bar, validation, autosave, and flexible delivery options.

**Features**

* 7-step wizard with smooth transitions
* Keyboard navigable with ARIA labels and focus management
* Scoped styles that won't clash with your theme
* Autosave to the browser (localStorage)
* Google Sheets integration via Apps Script
* Configurable email notifications via WordPress wp_mail()

== Installation ==

1. Zip the `jft-accessibility-survey` folder (the folder itself must be at the root of the zip).
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin** and upload the zip.
3. Click **Activate**.
4. Go to **Settings → JFT Survey** and configure Google Sheets and/or email notifications.
5. Add `[jft_accessibility_survey]` to any page or post and publish.

**Manual install:** copy the `jft-accessibility-survey` folder to `wp-content/plugins/` and activate from the Plugins screen.

== Google Sheets setup ==

See the `GOOGLE-SHEETS-SETUP.md` file in the main repository for step-by-step instructions on creating the Sheet and deploying the Apps Script.

== Frequently Asked Questions ==

= Can I use the survey on more than one page? =

Yes. Use the shortcode on any page. Use one survey per page (the form uses fixed element IDs).

= Can I get an email when someone submits? =

Yes. In **Settings → JFT Survey**, enable **Email me when someone submits the survey**, enter one or more addresses, and save. WordPress sends a plain-text summary of every response using `wp_mail()` (the same mail system as the rest of your site).

= Can I use both Google Sheets and email? =

Yes. Enable both — the plugin saves to your Sheet and sends an email notification for each submission.

= What if I leave the endpoint blank? =

The survey runs in demo mode: it shows the success screen and logs the payload to the browser console, but nothing is saved.

== Changelog ==

= 1.1.0 =
* Email notification settings (recipients, subject, on/off).
* Submissions route through WordPress REST API; server forwards to Sheets and/or email.

= 1.0.0 =
* Initial WordPress plugin release.
