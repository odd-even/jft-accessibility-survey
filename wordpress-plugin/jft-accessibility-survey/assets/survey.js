(function () {
  "use strict";

  var cfg = window.jftSurveyConfig || {};
  var JFT_ENDPOINT = cfg.endpoint || "";
  var JFT_STORAGE_KEY = cfg.storageKey || "jft-accessibility-survey-v1";

  /* ----- Survey content (data-driven) ----- */
  var QUESTIONS = [
    {
      id: "conditions",
      type: "checkbox",
      title: "Do you have any of the following difficulties or long-term conditions?",
      help: "Select all that apply.",
      options: [
        { value: "seeing", label: "Difficulty seeing even when wearing glasses or contact lenses" },
        { value: "hearing", label: "Difficulty hearing even when using a hearing aid or cochlear implant" },
        { value: "mobility", label: "Difficulty walking, using stairs, using your hands or fingers, or doing other physical activities" },
        { value: "cognitive", label: "Difficulty learning, remembering or concentrating" },
        { value: "mental_health", label: "Any emotional, psychological or mental health conditions (e.g., anxiety, depression, bipolar disorder, substance abuse, anorexia)" },
        { value: "other_condition", label: "Any other health problem or long-term condition that has lasted or is expected to last for six or more months" },
        { value: "none", label: "I do not have any difficulty or long-term condition that has lasted or is expected to last for six or more months", exclusive: true }
      ]
    },
    {
      id: "interacted",
      type: "radio",
      title: "Have you interacted with Jolly Farmer in the past 12 months?",
      options: [
        { value: "yes", label: "Yes" },
        { value: "no", label: "No" }
      ]
    },
    {
      id: "physical_environment",
      type: "checkbox",
      title: "In the past 12 months, have you experienced difficulties with any of the following features in the physical environment at Jolly Farmer because of your condition?",
      help: "Select all that apply.",
      options: [
        { value: "entrances", label: "Entrances or exits (e.g., narrow steps, lack of ramps, difficult to open doors)" },
        { value: "sidewalks", label: "Sidewalks or pedestrian paths (e.g., poor condition, difficulty with width or slope)" },
        { value: "layout", label: "Layout of workplace (e.g., confusing floorplans, narrow hallways or stairs)" },
        { value: "lighting_sound", label: "Lighting or sound levels" },
        { value: "parking_washrooms", label: "Lack of accessible parking, washrooms" },
        { value: "signs", label: "Complicated or unclear signs" },
        { value: "other", label: "Other", other: true },
        { value: "none", label: "I have not experienced any difficulties with any features in the physical environment because of my condition", exclusive: true }
      ]
    },
    {
      id: "technology",
      type: "checkbox",
      title: "In the past 12 months, have you experienced any of the following difficulties related to technology at Jolly Farmer because of your condition?",
      help: "Select all that apply.",
      options: [
        { value: "adaptive_hardware", label: "Lack of access to required adaptive hardware or software" },
        { value: "inaccessible_files", label: "Electronic files or documents with a lack of accessibility features" },
        { value: "virtual_meetings", label: "Virtual meeting platforms with a lack of accessibility features" },
        { value: "complicated_tech", label: "Complicated or unclear technology (e.g., designs or navigation issues of websites)" },
        { value: "incompatible_assistive", label: "Assistive technology not compatible with software" },
        { value: "connectivity", label: "Poor Internet connectivity limited use of accessibility or adaptive features" },
        { value: "equipment_repair", label: "Equipment or technology in need of repair or upgrade limited use of accessibility or adaptive features" },
        { value: "other", label: "Other", other: true },
        { value: "none", label: "I have not experienced any difficulties related to technology because of my condition", exclusive: true }
      ]
    },
    {
      id: "communication",
      type: "checkbox",
      title: "In the past 12 months, have you experienced any of the following difficulties related to communication at Jolly Farmer because of your condition?",
      help: "Select all that apply.",
      options: [
        { value: "alt_formats", label: "Physical and online materials not offered or available in alternate formats (e.g., embossed or electronic braille, large print, digital audio formats)" },
        { value: "plain_language", label: "Instructions, feedback or job criteria unclear or not given in plain language" },
        { value: "comm_aids", label: "Lack of availability of required technical communication aids (e.g., voice synthesizer, TTY, infrared system or portable note-taker)" },
        { value: "sign_language", label: "Sign language interpretation services not offered or available" },
        { value: "captioning", label: "No captioning or verbal descriptions of images, videos or printed text" },
        { value: "text_size", label: "Physical or online materials with text size that was too small or font that was difficult to read" },
        { value: "poorly_organized", label: "Files or documents that were unclear or poorly organized" },
        { value: "other", label: "Other", other: true },
        { value: "none", label: "I have not experienced any difficulties related to communication because of my condition", exclusive: true }
      ]
    },
    {
      id: "other_difficulties",
      type: "checkbox",
      title: "In the past 12 months, have you experienced any of the following other difficulties because of your condition?",
      help: "Select all that apply.",
      options: [
        { value: "accommodations", label: "Difficulties related to accommodations (adjustments or alternative arrangements such as flexible schedules, workstation modifications, specialized software, aids or assistive devices)" },
        { value: "discrimination", label: "Discrimination from colleagues or managers" },
        { value: "lack_support", label: "Lack of support or respect from colleagues or managers" },
        { value: "disclosing", label: "Did not feel comfortable disclosing disability or condition" },
        { value: "unaware_options", label: "Unaware of accessibility options in the workplace" },
        { value: "new_role", label: "Difficulties when starting a new job, position or role (e.g., no role clarity, slow process, information overload)" },
        { value: "training_needs", label: "Training opportunities did not meet accessibility needs" },
        { value: "training_market", label: "Training or experience was not adequate for the current job market" },
        { value: "screening_complex", label: "Application or screening process was long or complex" },
        { value: "inaccessible_application", label: "Inaccessible online job application or automated screening process (e.g., did not work with assistive device or limited formats offered)" },
        { value: "other", label: "Other", other: true },
        { value: "none", label: "I have not experienced any other difficulties because of my condition", exclusive: true }
      ]
    },
    {
      id: "suggestions",
      type: "textarea",
      optional: true,
      title: "Do you have any suggestions or feedback on how Jolly Farmer Transport can improve the accessibility of our organization?",
      help: "Optional — share anything you think would help.",
      placeholder: "Type your suggestions here (optional)…"
    }
  ];

  var TOTAL = QUESTIONS.length;

  /* ----- Tiny SVG icon helpers ----- */
  var ICON_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>';
  var ICON_DOT = '<span class="jft-dot"></span>';
  var ICON_WARN = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5"/><path d="M12 16.5h.01"/></svg>';

  /* ----- State ----- */
  var state = load() || {};
  var current = -1;

  /* ----- Element refs (set in boot) ----- */
  var form, body, footer, progressWrap, backBtn, nextBtn, submitBtn;
  var progressBar, progressRole, stepLabel, percentEl;

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function buildIntro() {
    var el = document.createElement("div");
    el.className = "jft-step is-active";
    el.setAttribute("data-step", "intro");
    el.innerHTML =
      '<div class="jft-panel">' +
        '<div class="jft-panel-icon">' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>' +
        '</div>' +
        '<h2 tabindex="-1" class="jft-q-title" id="jft-intro-title">We\u2019d love your input</h2>' +
        '<p>This short, confidential survey helps Jolly Farmer Transport identify and remove barriers so everyone can take part fully.</p>' +
        '<ul class="jft-meta-list">' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><span>Takes about 3\u20135 minutes \u2014 7 short questions.</span></li>' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Your answers are confidential.</span></li>' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg><span>You can go back and change any answer.</span></li>' +
        '</ul>' +
        '<div class="jft-panel-actions">' +
          '<button type="button" class="jft-btn jft-btn-primary" id="jft-start">Start survey' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>' +
          '</button>' +
        '</div>' +
      '</div>';
    return el;
  }

  function buildQuestion(q, index) {
    var el = document.createElement("section");
    el.className = "jft-step";
    el.setAttribute("data-step", index);
    el.setAttribute("data-qid", q.id);

    var titleId = "jft-q-" + q.id + "-title";
    var html = "";
    html += '<fieldset>';
    html += '<legend>';
    html += '<h2 class="jft-q-title" id="' + titleId + '" tabindex="-1">' + escapeHtml(q.title) + '</h2>';
    if (q.help) html += '<p class="jft-q-help">' + escapeHtml(q.help) + '</p>';
    html += '</legend>';

    if (q.type === "textarea") {
      var saved = typeof state[q.id] === "string" ? state[q.id] : "";
      html += '<textarea class="jft-textarea" id="jft-ta-' + q.id + '" ' +
              'aria-labelledby="' + titleId + '" ' +
              'placeholder="' + escapeHtml(q.placeholder || "") + '">' + escapeHtml(saved) + '</textarea>';
    } else {
      html += '<div class="jft-options" role="' + (q.type === "radio" ? "radiogroup" : "group") + '" aria-labelledby="' + titleId + '">';
      q.options.forEach(function (opt) {
        var inputId = "jft-" + q.id + "-" + opt.value;
        var inputType = q.type === "radio" ? "radio" : "checkbox";
        var mark = q.type === "radio" ? ICON_DOT : ICON_CHECK;
        html += '<label class="jft-option" data-type="' + inputType + '" for="' + inputId + '">';
        html += '<input type="' + inputType + '" name="' + q.id + '" id="' + inputId + '" value="' + opt.value + '"' +
                (opt.exclusive ? ' data-exclusive="true"' : '') +
                (opt.other ? ' data-other="true"' : '') + ' />';
        html += '<span class="jft-mark" aria-hidden="true">' + mark + '</span>';
        html += '<span class="jft-option-text">' + escapeHtml(opt.label) + '</span>';
        html += '</label>';
        if (opt.other) {
          html += '<div class="jft-other-wrap" id="jft-otherwrap-' + q.id + '">' +
                    '<div class="jft-other-inner">' +
                      '<input type="text" class="jft-other-input" id="jft-other-' + q.id + '" ' +
                      'placeholder="Please specify\u2026" aria-label="Please specify other difficulties" />' +
                    '</div>' +
                  '</div>';
        }
      });
      html += '</div>';
    }

    html += '<p class="jft-error" id="jft-error-' + q.id + '" role="alert">' + ICON_WARN + '<span>Please choose an answer to continue.</span></p>';
    html += '</fieldset>';

    el.innerHTML = html;
    return el;
  }

  function init() {
    form = document.getElementById("jft-form");
    body = document.getElementById("jft-body");
    footer = document.getElementById("jft-footer");
    progressWrap = document.getElementById("jft-progress-wrap");
    backBtn = document.getElementById("jft-back");
    nextBtn = document.getElementById("jft-next");
    submitBtn = document.getElementById("jft-submit");
    progressBar = document.getElementById("jft-progress-bar");
    progressRole = document.getElementById("jft-progress-role");
    stepLabel = document.getElementById("jft-step-label");
    percentEl = document.getElementById("jft-percent");

    if (!form || !body) return;

    body.appendChild(buildIntro());
    QUESTIONS.forEach(function (q, i) {
      body.appendChild(buildQuestion(q, i));
    });

    QUESTIONS.forEach(function (q) {
      var data = state[q.id];
      if (!data) return;
      if (q.type === "textarea") return;
      if (q.type === "radio") {
        var r = body.querySelector('input[name="' + q.id + '"][value="' + cssEscape(data) + '"]');
        if (r) r.checked = true;
      } else if (data && data.selected) {
        data.selected.forEach(function (v) {
          var c = body.querySelector('input[name="' + q.id + '"][value="' + cssEscape(v) + '"]');
          if (c) c.checked = true;
        });
        if (data.other) {
          var oi = document.getElementById("jft-other-" + q.id);
          if (oi) oi.value = data.other;
        }
        syncOtherVisibility(q.id);
      }
    });

    bindEvents();
    document.getElementById("jft-start").addEventListener("click", function () { goTo(0); });
  }

  function cssEscape(v) { return String(v).replace(/["\\]/g, "\\$&"); }

  function steps() { return body.querySelectorAll(".jft-step"); }

  function goTo(index) {
    if (current >= 0 && current < TOTAL) {
      persistQuestion(QUESTIONS[current].id);
    }
    var all = steps();
    all.forEach(function (s) { s.classList.remove("is-active"); });

    var domIndex = index + 1;
    var target = all[domIndex];
    void target.offsetWidth;
    target.classList.add("is-active");
    current = index;

    updateChrome();

    var heading = target.querySelector(".jft-q-title");
    if (heading) heading.focus({ preventScroll: true });

    try {
      var root = document.getElementById("jft-survey");
      var top = root.getBoundingClientRect().top + window.pageYOffset - 12;
      if (window.pageYOffset > top || window.pageYOffset < top - 200) {
        window.scrollTo({ top: Math.max(top, 0), behavior: "smooth" });
      }
    } catch (e) {}
  }

  function updateChrome() {
    var isIntro = current < 0;
    progressWrap.hidden = isIntro;
    footer.hidden = isIntro;
    if (isIntro) return;

    var isLast = current === TOTAL - 1;
    var pctComplete = Math.round(((current + 1) / TOTAL) * 100);

    progressBar.style.width = pctComplete + "%";
    progressRole.setAttribute("aria-valuenow", String(pctComplete));
    percentEl.textContent = pctComplete + "%";
    stepLabel.textContent = "Question " + (current + 1) + " of " + TOTAL;

    backBtn.hidden = false;
    nextBtn.hidden = isLast;
    submitBtn.hidden = !isLast;
  }

  function bindEvents() {
    body.addEventListener("change", function (e) {
      var input = e.target;
      if (!input.name) return;
      var qid = input.name;
      var q = getQuestion(qid);
      if (!q) return;

      if (q.type === "checkbox") {
        if (input.dataset.exclusive === "true" && input.checked) {
          body.querySelectorAll('input[name="' + qid + '"]').forEach(function (other) {
            if (other !== input) other.checked = false;
          });
          syncOtherVisibility(qid);
        } else if (input.checked) {
          var excl = body.querySelector('input[name="' + qid + '"][data-exclusive="true"]');
          if (excl) excl.checked = false;
        }
        if (input.dataset.other === "true") {
          syncOtherVisibility(qid);
        }
      }
      clearError(qid);
      persistQuestion(qid);
    });

    body.addEventListener("input", function (e) {
      var t = e.target;
      if (t.classList.contains("jft-other-input")) {
        persistQuestion(t.id.replace("jft-other-", ""));
      } else if (t.classList.contains("jft-textarea")) {
        state[t.id.replace("jft-ta-", "")] = t.value;
        save();
      }
    });

    backBtn.addEventListener("click", function () {
      if (current <= 0) { goTo(-1); } else { goTo(current - 1); }
    });
    nextBtn.addEventListener("click", function () {
      if (validate(current)) goTo(current + 1);
    });

    form.addEventListener("submit", onSubmit);
  }

  function syncOtherVisibility(qid) {
    var wrap = document.getElementById("jft-otherwrap-" + qid);
    if (!wrap) return;
    var otherInput = body.querySelector('input[name="' + qid + '"][data-other="true"]');
    var input = document.getElementById("jft-other-" + qid);
    if (otherInput && otherInput.checked) {
      wrap.classList.add("is-open");
    } else {
      wrap.classList.remove("is-open");
      if (input) input.value = "";
    }
  }

  function getQuestion(id) {
    for (var i = 0; i < QUESTIONS.length; i++) if (QUESTIONS[i].id === id) return QUESTIONS[i];
    return null;
  }

  function validate(index) {
    var q = QUESTIONS[index];
    if (!q || q.optional) return true;

    var ok = false;
    if (q.type === "textarea") {
      ok = true;
    } else {
      var checked = body.querySelectorAll('input[name="' + q.id + '"]:checked');
      ok = checked.length > 0;
      var otherInput = body.querySelector('input[name="' + q.id + '"][data-other="true"]');
      if (ok && otherInput && otherInput.checked) {
        var spec = document.getElementById("jft-other-" + q.id);
        if (spec && spec.value.trim() === "") {
          showError(q.id, "Please describe the \u201cOther\u201d option, or unselect it.");
          spec.focus();
          return false;
        }
      }
    }
    if (!ok) showError(q.id, "Please choose an answer to continue.");
    return ok;
  }

  function showError(qid, msg) {
    var err = document.getElementById("jft-error-" + qid);
    if (!err) return;
    if (msg) err.querySelector("span").textContent = msg;
    err.classList.add("is-shown");
  }

  function clearError(qid) {
    var err = document.getElementById("jft-error-" + qid);
    if (err) err.classList.remove("is-shown");
  }

  function readAnswerFromDom(q) {
    if (q.type === "textarea") {
      var ta = document.getElementById("jft-ta-" + q.id);
      return ta ? ta.value : "";
    }
    if (q.type === "radio") {
      var sel = body.querySelector('input[name="' + q.id + '"]:checked');
      return sel ? sel.value : null;
    }
    var selected = [];
    body.querySelectorAll('input[name="' + q.id + '"]:checked').forEach(function (c) {
      selected.push(c.value);
    });
    var otherInput = document.getElementById("jft-other-" + q.id);
    return {
      selected: selected,
      other: otherInput ? otherInput.value.trim() : ""
    };
  }

  function persistQuestion(qid) {
    var q = getQuestion(qid);
    if (!q) return;
    state[qid] = readAnswerFromDom(q);
    save();
  }

  function syncAllFromDom() {
    QUESTIONS.forEach(function (q) { persistQuestion(q.id); });
  }

  function save() {
    try { localStorage.setItem(JFT_STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
  }

  function load() {
    try { return JSON.parse(localStorage.getItem(JFT_STORAGE_KEY)); } catch (e) { return null; }
  }

  function buildPayload() {
    syncAllFromDom();
    var answers = {};
    QUESTIONS.forEach(function (q) {
      var labels = {};
      (q.options || []).forEach(function (o) { labels[o.value] = o.label; });
      var data = readAnswerFromDom(q);
      if (q.type === "textarea") {
        answers[q.id] = { question: q.title, value: data || "" };
      } else if (q.type === "radio") {
        answers[q.id] = { question: q.title, value: data || null, label: data ? labels[data] : null };
      } else {
        var sel = data.selected || [];
        answers[q.id] = {
          question: q.title,
          selected: sel,
          labels: sel.map(function (v) { return labels[v]; }),
          other: data.other || ""
        };
      }
    });
    return {
      survey: "Jolly Farmer Transport Accessibility Survey",
      submitted_at: new Date().toISOString(),
      answers: answers
    };
  }

  function onSubmit(e) {
    e.preventDefault();
    if (!validate(current)) return;

    var payload = buildPayload();
    submitBtn.disabled = true;
    submitBtn.querySelector("svg") && (submitBtn.firstChild.nodeValue = "Submitting\u2026 ");

    if (cfg.restUrl) {
      fetch(cfg.restUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": cfg.nonce || ""
        },
        body: JSON.stringify(payload)
      })
        .then(function (res) {
          return res.json().then(function (data) {
            if (!res.ok || !data || !data.success) {
              var msg = (data && data.message) ? data.message : "Submission failed.";
              throw new Error(msg);
            }
            if (data.demo_mode) {
              console.log("[JFT Survey] Demo mode — configure Google Sheets or email in Settings \u2192 JFT Survey.", payload);
            }
            showSuccess();
          });
        })
        .catch(function (err) {
          console.error("[JFT Survey] Submission failed:", err);
          submitBtn.disabled = false;
          showError(QUESTIONS[current].id, err.message || "Something went wrong sending your response. Please check your connection and try again.");
        });
      return;
    }

    if (!JFT_ENDPOINT) {
      console.log("[JFT Survey] Submission payload (demo mode):", payload);
      setTimeout(function () { showSuccess(); }, 500);
      return;
    }

    fetch(JFT_ENDPOINT, {
      method: "POST",
      mode: "no-cors",
      headers: { "Content-Type": "text/plain;charset=utf-8" },
      body: JSON.stringify(payload)
    })
      .then(function () { showSuccess(); })
      .catch(function (err) {
        console.error("[JFT Survey] Submission failed:", err);
        submitBtn.disabled = false;
        showError(QUESTIONS[current].id, "Something went wrong sending your response. Please check your connection and try again.");
      });
  }

  function showSuccess() {
    try { localStorage.removeItem(JFT_STORAGE_KEY); } catch (e) {}
    progressBar.style.width = "100%";
    progressRole.setAttribute("aria-valuenow", "100");
    percentEl.textContent = "100%";
    footer.hidden = true;
    progressWrap.hidden = true;

    body.innerHTML =
      '<div class="jft-panel">' +
        '<div class="jft-success-anim">' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>' +
        '</div>' +
        '<h2 tabindex="-1" class="jft-q-title" id="jft-done">Thank you!</h2>' +
        '<p>Your responses have been recorded. We genuinely appreciate you taking the time \u2014 your feedback helps us make Jolly Farmer Transport more accessible for everyone.</p>' +
      '</div>';
    var done = document.getElementById("jft-done");
    if (done) done.focus({ preventScroll: true });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
