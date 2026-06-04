# Collecting responses in a Google Sheet

Survey responses are sent to a **Google Apps Script Web App**, which appends
each submission as a row in a Google Sheet you own. It's free, unlimited, and
the data stays in your Google account.

You only have to do this once (~5–10 minutes).

---

## Step 1 — Create the Google Sheet

1. Go to <https://sheets.google.com> and create a **blank** spreadsheet.
2. Name it something like **"JFT Accessibility Survey — Responses"**.

(You don't need to add any headers — the script creates them automatically.)

## Step 2 — Add the script

1. In the sheet, click **Extensions → Apps Script**.
2. Delete any starter code in the editor.
3. Open [`google-apps-script/Code.gs`](google-apps-script/Code.gs) from this
   repo, copy **all** of it, and paste it into the Apps Script editor.
4. Click the **Save** icon (disk) — name the project anything, e.g. "JFT Survey".

## Step 3 — Deploy as a Web App

1. Click **Deploy → New deployment**.
2. Click the gear icon next to "Select type" and choose **Web app**.
3. Set:
   - **Description:** JFT Survey (optional)
   - **Execute as:** **Me**
   - **Who has access:** **Anyone**
4. Click **Deploy**.
5. Google will ask you to **authorize** — click through, choose your account,
   and "Allow". (If you see an "unverified app" warning: click
   **Advanced → Go to … (unsafe)**. It's your own script, so it's safe.)
6. Copy the **Web app URL**. It looks like:
   `https://script.google.com/macros/s/AKfy…/exec`

## Step 4 — Tell me (or paste it into the form)

Either:

- **Paste the URL to me in chat**, and I'll wire it in, commit, and push; **or**
- Do it yourself: open `index.html`, find this line near the top of the
  `<script>` and paste your URL between the quotes:

  ```js
  var JFT_ENDPOINT = "";   // paste your /exec URL here
  ```

  Then commit and push (`git add -A && git commit -m "Connect Google Sheet" && git push`).

That's it — new submissions will appear as rows in your sheet within a second
of someone clicking **Submit**.

---

## Tips

- **Email alerts on new responses:** in the Sheet, go to **Tools → Notification
  settings** (or **Notification rules**) and choose "Notify me… when a user
  submits a form / any changes are made."
- **Test it:** after wiring the URL in, submit the live survey once — a row
  should appear in the **Responses** tab.
- **Each multi-select answer** is written into one cell with each selected
  option on its own line; "Other" text is included as `Other: …`.
- **If you change the questions** in `index.html`, update the `COLUMNS` list at
  the top of `Code.gs` so the columns stay aligned.
- **Updating the script later:** after editing `Code.gs`, you must
  **Deploy → Manage deployments → Edit (pencil) → Version: New version → Deploy**
  for changes to take effect. The URL stays the same.
