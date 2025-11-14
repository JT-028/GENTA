(function(){
  // Combined loader + interactivity for the mascot
  // Note: we intentionally do NOT create separate pupil circles for this mascot.
  // The "eyes" in the provided SVG are rounded rectangles (`left_eye` / `right_eye`)
  // and should themselves be translated slightly to simulate pupil movement.

  function initInteractivity() {
    if (window.__gentaMascotInitialized) return; // idempotent
    window.__gentaMascotInitialized = true;

  var email = document.getElementById('email');
    var password = document.getElementById('password');
  var firstName = document.getElementById('first_name');
  var lastName = document.getElementById('last_name');
    var container = document.getElementById('genta-mascot-container');
    if (!container) return;

    var svg = container.querySelector('svg');
    if (!svg) return;

    // eye groups present in mascot_head.svg
    var openGroup = svg.querySelector('#open_eyes');
    var closedGroup = svg.querySelector('#closed_eyes');
    var peakGroup = svg.querySelector('#peak_eyes');

  // Diagnostic logger (disabled by default). Enable by setting window.__mascotDebug = true
  // or appending ?mascotDebug=1 to the URL. Logs are kept in window.__mascotEventLog (capped).
  (function(){
    try {
      var debugOn = false;
      try {
        if (window.__mascotDebug) debugOn = true;
        else if (location && location.search && /[?&]mascotDebug=1/.test(location.search)) debugOn = true;
      } catch(e) {}
      window.__mascotEventLog = window.__mascotEventLog || [];
      window.__mascotLog = function() {
        if (!debugOn) return;
        try {
          var args = Array.prototype.slice.call(arguments);
          var entry = { t: Date.now(), s: (new Date()).toISOString(), args: args };
          window.__mascotEventLog.push(entry);
          if (window.__mascotEventLog.length > 200) window.__mascotEventLog.shift();
          console.debug.apply(console, ['[mascot]'].concat(args));
        } catch (e) {}
      };
      window.__dumpMascotLog = function() { return (window.__mascotEventLog || []).slice(); };
    } catch (e) { window.__mascotLog = function(){}; window.__dumpMascotLog = function(){ return []; }; }
  })();

  // open eyes rects (to support subtle caret-following)
    var openLeft = openGroup ? openGroup.querySelector('#left_eye-3') : null;
    var openRight = openGroup ? openGroup.querySelector('#right_eye-3') : null;

    // helper: translate the open eye rects to simulate pupils moving inside the rounded squares
    function setPupils(x, y) {
      try {
        var max = 6; // px
        var xx = Math.max(-max, Math.min(max, x));
        var yy = Math.max(-max, Math.min(max, y));
        if (openLeft) openLeft.setAttribute('transform', 'translate(' + xx + ',' + yy + ')');
        if (openRight) openRight.setAttribute('transform', 'translate(' + xx + ',' + yy + ')');
      } catch (e) {}
    }

    function resetOpenEyes() { setPupils(0,0); }

    // compute caret pixel position in the input (best-effort)
    function getCaretPixelPos(input) {
      try {
        var style = window.getComputedStyle(input);
        var mirror = document.createElement('span');
        var value = input.value || '';
        var pos = input.selectionStart || value.length;
        mirror.style.visibility = 'hidden';
        mirror.style.whiteSpace = 'pre';
        mirror.style.font = style.font;
        mirror.style.letterSpacing = style.letterSpacing;
        mirror.style.padding = style.padding;
        mirror.style.border = style.border;
        mirror.textContent = value.substring(0, pos) || '\u200B';
        document.body.appendChild(mirror);
        var rect = mirror.getBoundingClientRect();
        var inputRect = input.getBoundingClientRect();
        var caretX = inputRect.left + rect.width - input.scrollLeft;
        document.body.removeChild(mirror);
        return { x: caretX, y: inputRect.top + inputRect.height / 2 };
      } catch (e) { return null; }
    }

    // email caret follow
    function onEmailInput() {
      window.__mascotLog('onEmailInput');
      if (!openLeft || !openRight || !email) return;
      var c = getCaretPixelPos(email);
      if (!c) { resetOpenEyes(); return; }
      var leftRect = openLeft.getBoundingClientRect();
      var rightRect = openRight.getBoundingClientRect();
      var centerL = { x: leftRect.left + leftRect.width / 2, y: leftRect.top + leftRect.height / 2 };
      var centerR = { x: rightRect.left + rightRect.width / 2, y: rightRect.top + rightRect.height / 2 };
      var dxL = (c.x - centerL.x) / 12;
      var dyL = (c.y - centerL.y) / 12;
      var dxR = (c.x - centerR.x) / 12;
      var dyR = (c.y - centerR.y) / 12;
      setPupils((dxL + dxR) / 2, (dyL + dyR) / 2);
      }

      // generic caret follow for any text input
      function followCaretFor(input) {
        try {
          if (!openLeft || !openRight || !input) return;
          var c = getCaretPixelPos(input);
          if (!c) { resetOpenEyes(); return; }
          var leftRect = openLeft.getBoundingClientRect();
          var rightRect = openRight.getBoundingClientRect();
          var centerL = { x: leftRect.left + leftRect.width / 2, y: leftRect.top + leftRect.height / 2 };
          var centerR = { x: rightRect.left + rightRect.width / 2, y: rightRect.top + rightRect.height / 2 };
          var dxL = (c.x - centerL.x) / 12;
          var dyL = (c.y - centerL.y) / 12;
          var dxR = (c.x - centerR.x) / 12;
          var dyR = (c.y - centerR.y) / 12;
          setPupils((dxL + dxR) / 2, (dyL + dyR) / 2);
        } catch (e) {}
      }

      // helper to attach caret-follow behavior to an input element
      function attachCaretFollower(input) {
        if (!input) return;
        try {
          input.addEventListener('focus', function () { showEyes('open'); followCaretFor(input); });
          input.addEventListener('input', function () { showEyes('open'); followCaretFor(input); });
          input.addEventListener('keyup', function () { followCaretFor(input); });
          input.addEventListener('click', function () { followCaretFor(input); });
          input.addEventListener('blur', function () { showEyes('open'); resetOpenEyes(); });
          // Ensure single-click focuses reliably: preempt pointerdown to show open eyes and schedule caret follow
          input.addEventListener('pointerdown', function (e) {
            try {
              window.__mascotLog && window.__mascotLog('input.pointerdown', input && input.id);
              if (document.activeElement !== input) {
                showEyes('open', true);
                setTimeout(function(){ followCaretFor(input); }, 0);
              }
            } catch (err) {}
          });
        } catch (e) {}
      }

  var __mascotSuppressOpenUntil = 0;
  var __mascotForcePeakUntil = 0; // kept for compatibility but not used for persistent peak
  var __mascotToggleInProgress = false;
  var __mascotCurrentEyeState = null;
    // prepare cross-fade transitions for the eye groups
    var __eyeHideTimers = {};
    function prepareGroupForFade(g) {
      try {
        if (!g) return;
        g.style.transition = 'opacity 220ms ease';
        g.style.opacity = '0';
        g.style.visibility = 'hidden';
        g.style.willChange = 'opacity';
      } catch (e) {}
    }
    prepareGroupForFade(openGroup);
    prepareGroupForFade(closedGroup);
    prepareGroupForFade(peakGroup);
    function _showGroup(g) {
      try {
        window.__mascotLog('_showGroup', g && g.getAttribute && g.getAttribute('id'));
        if (!g) return;
        // clear any pending hide timer
        var id = g.getAttribute && g.getAttribute('id');
        if (id && __eyeHideTimers[id]) { clearTimeout(__eyeHideTimers[id]); __eyeHideTimers[id] = null; }
        g.style.visibility = 'visible';
        g.style.opacity = '1';
      } catch (e) {}
    }
    function _hideGroup(g) {
      try {
        window.__mascotLog('_hideGroup', g && g.getAttribute && g.getAttribute('id'));
        if (!g) return;
        g.style.opacity = '0';
        // after transition, hide to remove from a11y and pointer events
        var id = g.getAttribute && g.getAttribute('id');
        if (id) {
          if (__eyeHideTimers[id]) clearTimeout(__eyeHideTimers[id]);
          __eyeHideTimers[id] = setTimeout(function(){ try{ g.style.visibility='hidden'; __eyeHideTimers[id]=null; }catch(e){} }, 260);
        }
      } catch (e) {}
    }
    function showEyes(state, force) {
      try {
        window.__mascotLog('showEyes', state, !!force, Date.now());
        var now = Date.now();
        // No persistent force-peak window. Peak is shown immediately when toggled
        // or when the password input is focused and revealed. Do not persist peak
        // after the user moves focus to another field.
        // If a temporary suppression is active, ignore requests to show 'open'
        if (state === 'open' && now < __mascotSuppressOpenUntil && !force) return;

        // avoid redundant transitions
        if (!force && __mascotCurrentEyeState === state) return;

        // show/hide with cross-fade
        if (state === 'open') { _showGroup(openGroup); } else { _hideGroup(openGroup); }
        if (state === 'closed') { _showGroup(closedGroup); } else { _hideGroup(closedGroup); }
        if (state === 'peak') { _showGroup(peakGroup); } else { _hideGroup(peakGroup); }

        __mascotCurrentEyeState = state;
      } catch (e) {
        // silent
      }
    }

    // If there's a server-side login failure, show wrong_pass immediately and suppress the brief open flash
    var __initialError = detectAndHandleFlash();
    if (__initialError) {
      // show wrong_pass immediately; it will revert to open after a short delay
      try { showEyes('wrong_pass', true); } catch(e) { showEyes('open'); }
    } else {
      // initialize to open eyes
      showEyes('open');
    }

    if (email) attachCaretFollower(email);

    // attach to first_name and last_name if present
    if (firstName) attachCaretFollower(firstName);
    if (lastName) attachCaretFollower(lastName);

    // Global focusin listener to ensure state matches focused element immediately
    document.addEventListener('focusin', function (ev) {
      try {
        window.__mascotLog('focusin', ev.target && ev.target.id);
        if (ev.target === email || ev.target === firstName || ev.target === lastName) {
          showEyes('open', true);
          if (ev.target === email) onEmailInput(); else followCaretFor(ev.target);
        } else if (ev.target === password) {
          passwordStateRefresh(true);
        }
      } catch (e) {}
    });

    function passwordStateRefresh(force) {
      window.__mascotLog('passwordStateRefresh', force, password && password.type, document.activeElement && document.activeElement.id);
      if (!password) return;
      var now = Date.now();
      // show peak only if password is focused and revealed; do not use a post-toggle force window
      if (password.type === 'text' && document.activeElement === password) {
        showEyes('peak', !!force);
      } else if (document.activeElement === password) {
        // password focused and not revealed
        showEyes('closed', !!force);
      } else {
        // otherwise, prefer open for other fields
        showEyes('open', !!force);
      }
    }

    if (password) {
      password.addEventListener('focus', passwordStateRefresh);
      password.addEventListener('input', passwordStateRefresh);
      password.addEventListener('blur', function () { showEyes('open'); });

      // Observe attribute changes to detect type toggles (password <-> text)
      try {
        var mo = new MutationObserver(function (muts) {
          muts.forEach(function (m) {
            try { window.__mascotLog('mutation', m.attributeName, m.oldValue); } catch(e){}
            if (m.attributeName === 'type') passwordStateRefresh();
          });
        });
        mo.observe(password, { attributes: true });
      } catch (e) { /* ignore */ }

      // In case a separate toggle button changes the type, handle clicks and re-evaluate
      document.addEventListener('click', function () {
        setTimeout(passwordStateRefresh, 40);
      });
      // Password visibility toggle button (if present)
      var toggleBtn = document.getElementById('toggle-password-visibility');
      if (toggleBtn) {
        // pointerdown used to preempt focus/change events so we can suppress transient open flashes
        // fallback to mousedown/touchstart for older browsers
        var addPointerDown = function(fn) {
          if (window.PointerEvent) {
            toggleBtn.addEventListener('pointerdown', fn);
          } else {
            toggleBtn.addEventListener('mousedown', fn);
            toggleBtn.addEventListener('touchstart', fn);
          }
        };

        addPointerDown(function () {
          try {
            window.__mascotLog('toggle.pointerdown');
            __mascotToggleInProgress = true;
            setTimeout(function(){ __mascotToggleInProgress = false; }, 150);
            // also set a short suppression window immediately
            __mascotSuppressOpenUntil = Date.now() + 300;
            // ensure peak is shown immediately while toggle interaction is happening
            showEyes('peak', true);
          } catch (e) {}
        });

        toggleBtn.addEventListener('click', function (e) {
          try {
            window.__mascotLog('toggle.click', password && password.type);
            if (!password) return;
            // Prevent other click handlers from running and possibly showing 'open'
            if (e && typeof e.stopPropagation === 'function') {
              e.stopPropagation();
              e.preventDefault && e.preventDefault();
            }
            // activate a short suppression window so other handlers do not briefly show 'open' eyes
            __mascotSuppressOpenUntil = Date.now() + 1200; // 1200ms suppression
            if (password.type === 'password') {
              password.type = 'text';
              // update icon if using mdi
              var i = toggleBtn.querySelector('i'); if (i) { i.className = 'mdi mdi-eye-outline'; }
                // immediately show peak eyes (no persistent force window)
                showEyes('peak', true);
            } else {
              password.type = 'password';
              var i2 = toggleBtn.querySelector('i'); if (i2) { i2.className = 'mdi mdi-eye-off-outline'; }
              // after hiding, show closed eyes if still focused (or open otherwise)
              setTimeout(function(){ passwordStateRefresh(true); }, 10);
            }
            // keep focus on the password field
            password.focus();
            // notify state change (ensure MutationObserver also catches it)
            passwordStateRefresh();
          } catch (err) { console.warn('toggle password failed', err); }
        });
      }
    }

    window.addEventListener('pageshow', function () { showEyes('open'); });
  }

  function loadMascotAndInit() {
    var container = document.getElementById('genta-mascot-container');
    if (!container) return;
  var svgUrl = '/GENTA/assets/images/mascot_head.svg';
    function insertSvgText(text) {
      try {
        try { window.__mascotLog('insertSvgText', text && text.length); } catch (e) {}
        var parser = new DOMParser();
        var doc = parser.parseFromString(text, 'image/svg+xml');
        var svg = doc.documentElement;
        container.innerHTML = '';
        svg = document.importNode(svg, true);
        container.appendChild(svg);
      } catch (e) {
        container.innerHTML = '';
      }
      // Small timeout to ensure elements are in the DOM
      setTimeout(initInteractivity, 30);
    }

    if (typeof fetch === 'function') {
      fetch(svgUrl).then(function(res){
        if (!res.ok) throw new Error('SVG not found: ' + res.status);
        return res.text();
      }).then(function(text){
        insertSvgText(text);
      }).catch(function(err){
        console.warn('Could not load mascot SVG via fetch:', err);
        // fallback to XHR
        try {
          var xhr = new XMLHttpRequest();
          xhr.open('GET', svgUrl);
          xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
              if (xhr.status >= 200 && xhr.status < 300) insertSvgText(xhr.responseText);
              else {
                console.warn('Could not load mascot SVG via XHR:', xhr.status);
                setTimeout(initInteractivity, 30);
              }
            }
          };
          xhr.send();
        } catch (e) {
          setTimeout(initInteractivity, 30);
        }
      });
    } else {
      // fetch not available: use XHR
      try {
        var xhr2 = new XMLHttpRequest();
        xhr2.open('GET', svgUrl);
        xhr2.onreadystatechange = function() {
          if (xhr2.readyState === 4) {
            if (xhr2.status >= 200 && xhr2.status < 300) insertSvgText(xhr2.responseText);
            else { console.warn('Could not load mascot SVG via XHR:', xhr2.status); setTimeout(initInteractivity, 30); }
          }
        };
        xhr2.send();
      } catch (e) {
        setTimeout(initInteractivity, 30);
      }
    }
  }

  // Start on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadMascotAndInit);
  } else {
    loadMascotAndInit();
  }
})();
