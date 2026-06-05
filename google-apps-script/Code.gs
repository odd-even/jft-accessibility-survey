/**
 * Jolly Farmer Transport — Accessibility Survey
 * Google Apps Script Web App backend.
 *
 * This receives a POST from index.html and appends each submission as a
 * row in the bound Google Sheet. See GOOGLE-SHEETS-SETUP.md for setup.
 *
 * Deploy:  Deploy > New deployment > Web app
 *          - Execute as:      Me
 *          - Who has access:  Anyone
 * Then copy the Web app URL into JFT_ENDPOINT in index.html.
 */

// Name of the tab to write responses into (created automatically).
var SHEET_NAME = 'Responses';

// Column order for the sheet. Each entry maps a question id (from the
// survey payload) to the human-readable header shown in the Sheet.
// (If you add/rename questions in index.html, update this list to match.)
var COLUMNS = [
  { id: 'conditions',            header: 'Q1 — Difficulties / long-term conditions' },
  { id: 'interacted',            header: 'Q2 — Interacted in past 12 months' },
  { id: 'physical_environment',  header: 'Q3 — Physical environment difficulties' },
  { id: 'technology',            header: 'Q4 — Technology difficulties' },
  { id: 'communication',         header: 'Q5 — Communication difficulties' },
  { id: 'other_difficulties',    header: 'Q6 — Other difficulties' },
  { id: 'suggestions',           header: 'Q7 — Suggestions / feedback' }
];

/**
 * Handles form submissions.
 */
function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(30000); // avoid two submissions racing on the same row
  try {
    var payload = JSON.parse(e.postData.contents);
    var answers = payload.answers || {};

    var sheet = getSheet_();
    ensureHeaders_(sheet);

    var row = [];
    // Column 1: when the visitor submitted (from the browser).
    row.push(payload.submitted_at ? new Date(payload.submitted_at) : new Date());

    COLUMNS.forEach(function (col) {
      row.push(formatAnswer_(answers[col.id]));
    });

    sheet.appendRow(row);

    return json_({ result: 'success' });
  } catch (err) {
    return json_({ result: 'error', message: String(err) });
  } finally {
    lock.releaseLock();
  }
}

/**
 * A friendly response if someone opens the Web app URL in a browser.
 */
function doGet() {
  return json_({ result: 'ok', message: 'JFT Accessibility Survey endpoint is live. Submit via POST.' });
}

/* ---------------- helpers ---------------- */

function getSheet_() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(SHEET_NAME);
  if (!sheet) sheet = ss.insertSheet(SHEET_NAME);
  return sheet;
}

function ensureHeaders_(sheet) {
  if (sheet.getLastRow() > 0) return; // headers already present
  var headers = ['Submitted at'].concat(COLUMNS.map(function (c) { return c.header; }));
  sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
  sheet.getRange(1, 1, 1, headers.length).setFontWeight('bold');
  sheet.setFrozenRows(1);
}

/**
 * Turns one answer object from the payload into a readable cell string.
 */
function formatAnswer_(a) {
  if (!a) return '';

  // Free-text question (suggestions)
  if (typeof a.value === 'string' && !a.label) {
    return a.value || '';
  }

  // Single-choice (radio)
  if (a.label !== undefined && a.selected === undefined) {
    return a.label || a.value || '';
  }

  // Multi-choice (checkbox)
  if (a.selected !== undefined) {
    var parts = (a.labels || []).filter(function (l) { return l && l !== 'Other'; });
    if (parts.length === 0 && a.selected.length > 0) {
      parts = a.selected.slice();
    }
    if (a.other) parts.push('Other: ' + a.other);
    return parts.join('\n');
  }

  return '';
}

function json_(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
