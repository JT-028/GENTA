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
  // optional group for a dedicated wrong-password expression
  var wrongGroup = svg.querySelector('#wrong_pass_eyes');
  // optional group for happy expression (used on successful register/login)
  var happyGroup = svg.querySelector('#happy_eyes');
  // optional group for pending expression (used when account is unverified/pending)
  var pendingGroup = svg.querySelector('#pending_eyes');

  // Helper: ensure a pending_eyes group exists inside the loaded SVG. If missing,
  // create one with three circles so the pending animation is always available.
  function ensurePendingGroup() {
    try {
      if (pendingGroup && pendingGroup.parentNode) return pendingGroup;
      // try to locate again in case of timing
      pendingGroup = svg.querySelector('#pending_eyes');
      if (pendingGroup && pendingGroup.parentNode) return pendingGroup;
      // create group and three circles positioned roughly where the eyes are in the SVG
      var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
      g.setAttribute('id', 'pending_eyes');
      g.setAttribute('data-name', 'pending eyes');
  // Increased spacing between dots for better legibility
  var cx1 = 117.06, cx2 = 139.06, cx3 = 161.06, cy = 140, r = 6.5;
      var c1 = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      c1.setAttribute('class', 'pending-dot'); c1.setAttribute('cx', cx1); c1.setAttribute('cy', cy); c1.setAttribute('r', r);
      var c2 = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      c2.setAttribute('class', 'pending-dot'); c2.setAttribute('cx', cx2); c2.setAttribute('cy', cy); c2.setAttribute('r', r);
      var c3 = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      c3.setAttribute('class', 'pending-dot'); c3.setAttribute('cx', cx3); c3.setAttribute('cy', cy); c3.setAttribute('r', r);
      g.appendChild(c1); g.appendChild(c2); g.appendChild(c3);
      // Append near the face group if present, otherwise append to svg root
      var face = svg.querySelector('#face') || svg;
      face.appendChild(g);
      pendingGroup = g;
      return pendingGroup;
    } catch (e) { return pendingGroup; }
  }

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
  var __mascotStateLockUntil = 0; // when set, prevent other states from overriding (used for pending)
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
  // ensure wrong-pass group (if present) is hidden initially so it doesn't show beneath others
  prepareGroupForFade(wrongGroup);
  // ensure happy group (if present) is hidden initially
  prepareGroupForFade(happyGroup);
  // ensure pending group (if present) is hidden initially
  prepareGroupForFade(pendingGroup);
    
    // helper: show a minimal in-page toast when Swal (SweetAlert2) is not available
    function _showFallbackToast(txt, type) {
      try {
        var t = document.createElement('div');
        t.className = 'genta-toast';
        t.textContent = txt;
        t.setAttribute('role', 'status');
        var bg = type === 'error' ? '#fee2e2' : (type === 'success' ? '#dcfce7' : '#eef2ff');
        var color = '#0f172a';
        Object.assign(t.style, {
          position: 'fixed',
          top: '16px',
          right: '16px',
          zIndex: 11000,
          background: bg,
          color: color,
          padding: '10px 14px',
          borderRadius: '8px',
          boxShadow: '0 6px 18px rgba(2,6,23,0.08)',
          maxWidth: '340px',
          fontSize: '0.95rem'
        });
        document.body.appendChild(t);
        // auto-dismiss
        setTimeout(function(){ try{ t.style.transition='opacity 300ms'; t.style.opacity='0'; setTimeout(function(){ try{ t.remove(); }catch(e){} }, 300); }catch(e){} }, 3600);
      } catch (e) {}
    }

    // Detect server-side flash elements and show toasts (Swal if available, fallback otherwise).
    function detectAndHandleFlash() {
      try {
  var flashEls = document.querySelectorAll('.alert.alert-danger, .flash-error, .message.error, .alert-danger, .alert.alert-success, .flash-success, .message.success, .alert-success');
        var foundError = false;
        var foundAny = false;
        var SwalToast = null;
        try {
              if (typeof Swal !== 'undefined' && Swal && typeof Swal.mixin === 'function') {
                // Match default toast duration for pending alerts (4000ms)
                SwalToast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true });
              }
        } catch (e) { SwalToast = null; }

        flashEls.forEach(function(el){
          try {
            var txt = (el.textContent || '').trim();
            if (!txt) return;
            foundAny = true;
            var isError = /invalid email|invalid password|invalid email or password|error/i.test(txt) || el.classList.contains('alert-danger') || el.classList.contains('flash-error');
            var isSuccess = el.classList.contains('alert-success') || el.classList.contains('flash-success') || /success|registered|created|saved/i.test(txt);
            if (SwalToast) {
              try {
                // Use the same options as other site toasts (profile page) so appearance matches exactly
                Swal.fire({
                  icon: isError ? 'error' : (isSuccess ? 'success' : 'info'),
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: isError ? 3000 : 1800,
                  title: txt,
                });
              } catch (e) {}
            } else {
              _showFallbackToast(txt, isError ? 'error' : (isSuccess ? 'success' : 'info'));
            }
            // If this is a success flash (e.g., registration or profile saved), show happy eyes
            var isRegistrationSuccess = isSuccess && /registered|you successfully registered|successfully registered a new account/i.test(txt);
            if (isSuccess) {
              try {
                // For registration-specific success messages, set a flag so
                // initialization won't immediately reset the mascot to 'open'.
                if (isRegistrationSuccess) {
                  try { window.__mascotHappyFlash = true; } catch(e) {}
                }
                try { showEyes('happy', true); } catch(e) {}
              } catch(e) {}
            }
            // Detect pending/unverified account messages and show pending animation
            // Do NOT show pending when the success flash is actually the registration confirmation
            var isPending = /not active|pending admin approval|pending approval|account is not active|awaiting approval/i.test(txt);
            if (isPending && !isRegistrationSuccess) {
              // mark that we found a pending flash so initialization won't override it
              try { window.__mascotPendingFlash = true; } catch(e){}
              try { if (SwalToast) { Swal.fire({ icon: 'info', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, title: txt }); } else { _showFallbackToast(txt, 'info'); } } catch(e){}
              try { showEyes('pending', true); } catch(e){}
            }
            // hide the server-rendered flash to avoid duplicate messages on-screen
            try { el.style.display = 'none'; } catch (e) {}
            if (isError && /invalid email|invalid password|invalid email or password/i.test(txt)) foundError = true;
          } catch(e){}
        });

        if (!foundAny) {
          var bodyText = (document.body && document.body.textContent) ? document.body.textContent : '';
          if (/Invalid email or password\.|invalid email or password/i.test(bodyText)) {
            foundError = true;
            if (SwalToast) {
              try {
                Swal.fire({ icon: 'error', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, title: 'Invalid email or password.' });
              } catch(e){}
            } else { _showFallbackToast('Invalid email or password.', 'error'); }
          }
        }
        return foundError;
      } catch (e) { return false; }
    }
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
        // If a state lock is active, do not change to a different state until it expires.
        // Allow forced overrides (force === true) and always allow the 'open' state
        // so pending can revert back to open when its timeout fires.
        if (now < __mascotStateLockUntil && state !== __mascotCurrentEyeState) {
          if (!force && state !== 'open') {
            window.__mascotLog && window.__mascotLog('state locked, ignoring', state);
            return;
          }
        }
        // No persistent force-peak window. Peak is shown immediately when toggled
        // or when the password input is focused and revealed. Do not persist peak
        // after the user moves focus to another field.
        // If a temporary suppression is active, ignore requests to show 'open'
        if (state === 'open' && now < __mascotSuppressOpenUntil && !force) return;

        // avoid redundant transitions
        if (!force && __mascotCurrentEyeState === state) return;

        // No DOM overlay cleanup needed here; SVG-based pending eyes are used when available.

        // show/hide with cross-fade (support extra 'wrong_pass' group if present)
        if (state === 'wrong_pass') {
            // If the SVG contains a dedicated wrong-pass group, show it briefly.
            var wrongG = wrongGroup;
            if (wrongG) {
              // hide others, show wrongGroup
              _hideGroup(openGroup); _hideGroup(closedGroup); _hideGroup(peakGroup);
              _showGroup(wrongG);
              // revert to open after a short timeout
              setTimeout(function(){ try{ _hideGroup(wrongG); showEyes('open'); }catch(e){} }, 700);
              __mascotCurrentEyeState = state;
              return;
            }
          // fallback: show closed eyes and a brief shake animation on the mascot container
          try {
            _hideGroup(openGroup); _hideGroup(peakGroup); _showGroup(closedGroup);
            if (container) container.classList.add('mascot-wrong');
            setTimeout(function(){ try{ if (container) container.classList.remove('mascot-wrong'); showEyes('open'); }catch(e){} }, 700);
            __mascotCurrentEyeState = state;
            return;
          } catch (e) {
            /* ignore */
          }
        }

        // pending expression for unverified / awaiting approval accounts
        if (state === 'pending') {
          try {
            // Lock other state changes for the duration of pending animation
            __mascotStateLockUntil = Date.now() + 4000;
            var p = ensurePendingGroup();
            if (p) {
              _hideGroup(openGroup); _hideGroup(closedGroup); _hideGroup(peakGroup);
              _showGroup(p);
              // revert to open after a timeout matching the pending toast (keep in sync)
              setTimeout(function(){ try{ _hideGroup(p); showEyes('open'); }catch(e){} }, 4000);
              __mascotCurrentEyeState = state;
              return;
            }
            // Fallback: show closed eyes (do NOT use peak as a fallback for pending)
            _hideGroup(openGroup); _hideGroup(peakGroup); _showGroup(closedGroup);
            setTimeout(function(){ try{ _hideGroup(closedGroup); showEyes('open'); }catch(e){} }, 4000);
            __mascotCurrentEyeState = state;
            return;
          } catch (e) {}
        }

        // peak expression: show the peak_eyes group (used for password reveal)
        if (state === 'peak') {
          try {
            var pg = peakGroup;
            if (pg) {
              _hideGroup(openGroup); _hideGroup(closedGroup); _hideGroup(pendingGroup);
              _showGroup(pg);
              __mascotCurrentEyeState = state;
              return;
            }
            // Fallback: show open eyes if peak group is not present
            _hideGroup(closedGroup); _hideGroup(pendingGroup); _showGroup(openGroup);
            __mascotCurrentEyeState = state;
            return;
          } catch (e) {}
        }

        // happy expression for success cases
        if (state === 'happy') {
          try {
            var h = happyGroup;
            if (h) {
              _hideGroup(openGroup); _hideGroup(closedGroup); _hideGroup(peakGroup);
              _showGroup(h);
              // revert to open after a short timeout
              setTimeout(function(){ try{ _hideGroup(h); showEyes('open'); }catch(e){} }, 900);
              __mascotCurrentEyeState = state;
              return;
            }
            // fallback: briefly show peak
            _hideGroup(closedGroup); _hideGroup(openGroup); _showGroup(peakGroup);
            setTimeout(function(){ try{ showEyes('open'); }catch(e){} }, 900);
            __mascotCurrentEyeState = state;
            return;
          } catch (e) {}
        }

        // Default behaviour for open/closed/peak
        if (state === 'open') { _showGroup(openGroup); } else { _hideGroup(openGroup); }
        if (state === 'closed') { _showGroup(closedGroup); } else { _hideGroup(closedGroup); }
        if (state === 'peak') { _showGroup(peakGroup); } else { _hideGroup(peakGroup); }

        __mascotCurrentEyeState = state;
      } catch (e) {
        // silent
      }
    }

    // NOTE: DOM overlay fallback removed -- prefer SVG #pending_eyes vector group.

    // If there's a server-side login failure, show wrong_pass immediately and suppress the brief open flash
    var __initialError = detectAndHandleFlash();
    if (__initialError) {
      // show wrong_pass immediately; it will revert to open after a short delay
      try { showEyes('wrong_pass', true); } catch(e) { showEyes('open'); }
    } else {
      // Prefer pending animation when present, otherwise prefer a registration-success
      // happy expression (if detected). Only fall back to open if neither applies.
      if (window.__mascotPendingFlash) {
        try { showEyes('pending', true); } catch(e) { showEyes('open'); }
        try { window.__mascotPendingFlash = false; } catch(e) {}
      } else if (window.__mascotHappyFlash) {
        try { showEyes('happy', true); } catch(e) { showEyes('open'); }
        try { window.__mascotHappyFlash = false; } catch(e) {}
      } else {
        // initialize to open eyes
        showEyes('open');
      }
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

    // Flash handling is managed earlier via detectAndHandleFlash()

    // Expose a helper so other scripts can trigger the wrong-password expression programmatically
    try { window.triggerMascotWrongPassword = function() { try{ showEyes('wrong_pass', true); } catch(e){} }; } catch(e) {}

    window.addEventListener('pageshow', function () { showEyes('open'); });
    
    // Intercept login form submissions to show happy mascot on successful login before redirecting
    try {
      var loginForm = document.getElementById('loginForm') || document.querySelector('form[action*="/Users/login"], form[action*="/users/login"]');
      if (loginForm) {
        loginForm.addEventListener('submit', function (ev) {
          try {
            // If user agent doesn't support fetch, allow normal submit
            if (typeof fetch !== 'function') return;
            ev.preventDefault();
            var fd = new FormData(loginForm);
            fetch(loginForm.action || window.location.href, { method: 'POST', credentials: 'same-origin', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
              .then(function (res) {
                return res.text().then(function(text){ return { url: res.url, text: text }; });
              }).then(function (obj) {
                try {
                  var text = obj.text || '';
                  // If the response contains the invalid credentials message -> show error
                    if (/invalid email|invalid password|invalid email or password/i.test(text)) {
                    // show toast and wrong-pass
                    try { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, title: (text.match(/Invalid email or password\.|invalid email or password/i) || ['Invalid email or password.'])[0] }); } catch(e) {}
                    try { showEyes('wrong_pass', true); } catch(e) {}
                    return;
                  }
                    // If the response contains a pending/unverified account message -> show pending state
                    var isRegistrationResponse = /registered|you successfully registered|successfully registered a new account/i.test(text);
                    if (/not active|pending admin approval|pending approval|account is not active|awaiting approval/i.test(text) && !isRegistrationResponse) {
                      try { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, title: (text.match(/Your account is not active\.|pending admin approval|pending approval|awaiting approval/i) || [text.trim()])[0] }); } catch(e) {}
                      try { showEyes('pending', true); } catch(e) {}
                      return;
                    }
                  // Otherwise assume successful login (server redirected to dashboard)
                  try { showEyes('happy', true); } catch(e) {}
                  // Show a success toast matching the happy expression before redirecting
                  try {
                    if (typeof Swal !== 'undefined') {
                      Swal.fire({ icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 900, title: 'Logged in successfully' });
                    } else {
                      _showFallbackToast('Logged in successfully', 'success');
                    }
                  } catch (e) {}
                  // Delay to let the user see the happy expression and toast, then navigate to the final URL
                  setTimeout(function(){ try{ window.location.href = obj.url || '/'; }catch(e){ window.location.reload(); } }, 900);
                } catch (e) {
                  // Fallback to normal submit on error
                  loginForm.submit && loginForm.submit();
                }
              }).catch(function(err){
                // On network error, fallback to normal submit so server handles
                loginForm.submit && loginForm.submit();
              });
          } catch (e) {}
        });
      }
    } catch (e) {}
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
