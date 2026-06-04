# Jolly Farmer Transport — Accessibility Survey

**Live preview:** https://odd-even.github.io/jft-accessibility-survey/
**Repository:** https://github.com/odd-even/jft-accessibility-survey
**Embed file (raw):** https://raw.githubusercontent.com/odd-even/jft-accessibility-survey/main/index.html

A slick, fully accessible, multi-step web survey. Questions "page in" one at a
time with smooth transitions, a progress bar, validation, and autosave. The
whole thing lives in a **single self-contained file** (`index.html`) — all CSS
and JavaScript are inline, with no external dependencies — so it's trivial to
drop into a self-hosted WordPress site.

## Features

- **7-step wizard** — one question per screen, smooth page-in animation.
- **Progress bar** + "Question X of 7" / percent complete.
- **Validation** before advancing (Q7 feedback is optional).
- **Multi-select** with smart **"none of the above"** exclusivity, plus
  **"Other — specify"** fields that reveal a text input.
- **Autosave** to `localStorage` — a refresh won't lose answers.
- **Accessible by design** (it's an accessibility survey, after all):
  keyboard navigable, ARIA roles/labels, focus management, `role="alert"`
  errors, high-contrast colors, and `prefers-reduced-motion` support.
- **Scoped styles** under `#jft-survey` so it won't fight your WordPress theme.

## Quick preview (locally)

Just open `index.html` in a browser, or serve the folder:

```bash
python3 -m http.server 8000
# then visit http://localhost:8000
```

## Where responses go (Google Sheets)

Submissions are sent to a **Google Apps Script Web App** that appends each
response as a row in a Google Sheet you own — free, unlimited, no plugin.

**Setup (one time, ~5 min):** follow [`GOOGLE-SHEETS-SETUP.md`](GOOGLE-SHEETS-SETUP.md).
You create a Sheet, paste in [`google-apps-script/Code.gs`](google-apps-script/Code.gs),
deploy it as a Web app, and paste the resulting URL into the **CONFIGURATION**
block near the top of the `<script>` in `index.html`:

```js
var JFT_ENDPOINT = "";   // paste your https://script.google.com/macros/s/.../exec URL
```

- Leave it **empty** to run in **demo mode** — the form shows the success
  screen and logs the JSON payload to the browser console (great for testing).
- Set it to your Web app URL and each submission becomes a row in the Sheet.

The submitted JSON (what the Apps Script receives) looks like:

```json
{
  "survey": "Jolly Farmer Transport Accessibility Survey",
  "submitted_at": "2026-06-04T14:30:00.000Z",
  "answers": {
    "conditions": { "question": "...", "selected": ["seeing"], "labels": ["Difficulty seeing..."], "other": "" },
    "interacted": { "question": "...", "value": "yes", "label": "Yes" },
    "...": {}
  }
}
```

## Deploying to self-hosted WordPress

**Option A — Custom HTML block (easiest):**
1. Edit the page where you want the survey.
2. Add a **Custom HTML** block.
3. Paste the contents of `index.html`. (You can omit the `<!DOCTYPE>`,
   `<html>`, `<head>`, `<body>` wrappers if you like — the `<style>`,
   the `#jft-survey` markup, and the `<script>` are what matter.)
4. Publish.

**Option B — Standalone page:** upload `index.html` to your server and link to
it directly.

**Wiring up submissions (Option B for the backend):** the cleanest path on
WordPress is a small REST route in a plugin or `functions.php` that receives the
JSON and emails it / stores it as a custom post type. Point `JFT_ENDPOINT` at
that route. (Happy to generate that PHP handler next.)

## Editing questions

All survey content is data-driven in the `QUESTIONS` array inside the
`<script>`. To add, remove, or reword a question or option, edit that array —
the UI, validation, progress bar, and payload all update automatically.

| Property     | Meaning                                                        |
|--------------|----------------------------------------------------------------|
| `type`       | `"checkbox"` (multi), `"radio"` (single), or `"textarea"`      |
| `optional`   | `true` to skip the "must answer" validation                    |
| `exclusive`  | option that clears all others (the "none of the above" entries)|
| `other`      | option that reveals a free-text "specify" input                |
```
