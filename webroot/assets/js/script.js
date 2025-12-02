// CUSTOM JS SCRIPTS
// APP_BASE helper: ensure client-side code builds URLs that respect the application's base path
// window.APP_BASE is set in the layout (teacher-layout.php / guest-layout.php) and typically contains a trailing slash, e.g. '/GENTA/'
var __GENTA_APP_BASE =
    typeof window.APP_BASE !== "undefined" ? String(window.APP_BASE) : "";
function buildUrl(path) {
    if (!path) return path;
    var p = String(path);
    // absolute URL? return as-is
    if (/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//.test(p)) return p;
    // if path already contains APP_BASE prefix, return normalized
    if (__GENTA_APP_BASE && p.indexOf(__GENTA_APP_BASE) === 0) return p;
    // ensure APP_BASE ends with '/'
    var base = __GENTA_APP_BASE || "/";
    if (base && base.slice(-1) !== "/") base = base + "/";
    // remove leading slashes from path
    p = p.replace(/^\/+/, "");
    // concat
    return base + p;
}
// Normalize an app-relative path and collapse duplicated APP_BASE segments.
function normalizeAppPath(path) {
    if (!path) return path;
    var p = String(path);
    // Absolute URLs (http(s)://) should be returned as-is
    if (/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//.test(p)) return p;
    // If APP_BASE is configured, collapse repeated occurrences like '/GENTA/GENTA/...'
    if (__GENTA_APP_BASE) {
        var doubled = __GENTA_APP_BASE + __GENTA_APP_BASE;
        while (p.indexOf(doubled) === 0) {
            p = p.replace(doubled, __GENTA_APP_BASE);
        }
    }
    // Use buildUrl to ensure proper final prefixing
    return buildUrl(p);
}
// Expose a placeholder so other scripts (shepherd-init) can detect WalkthroughSystem presence
if (!window.WalkthroughSystem) window.WalkthroughSystem = {};
// Helper: initialize page-specific behaviors that need to run after AJAX page swaps
function initPage() {
    // DataTable init (idempotent)
    try {
        // Prevent DataTables from showing browser alert() on Ajax errors.
        // Instead route errors to console so users don't see a blocking modal.
        try {
            if ($ && $.fn && $.fn.DataTable && $.fn.DataTable.ext) {
                $.fn.DataTable.ext.errMode = function (settings, helpPage, message) {
                    try { console.error('[DataTables] ajax error', {settings: settings, helpPage: helpPage, message: message}); } catch (e) {}
                    // do not show alert()
                };
            }
        } catch (e) { /* ignore if DataTables not loaded yet */ }
        if ($.fn && $.fn.DataTable) {
            // Initialize or adjust each table individually to avoid race conditions
            $(".defaultDataTable").each(function () {
                var $tbl = $(this);
                try {
                    if ($.fn.DataTable.isDataTable($tbl)) {
                        // If already initialized, ensure columns & responsive layout are recalculated
                        var tblApi = $tbl.DataTable();
                        try {
                            tblApi.columns().adjust();
                        } catch (e) {
                            /* noop */
                        }
                        try {
                            if (tblApi.responsive) tblApi.responsive.recalc();
                        } catch (e) {
                            /* noop */
                        }
                    } else {
                        // Init with responsive and reasonable defaults
                        var tblApi = $tbl.DataTable({
                            responsive: true,
                            autoWidth: false,
                        });
                        // A short delay to allow CSS/layout to settle, then adjust
                        setTimeout(function () {
                            try {
                                tblApi.columns().adjust();
                            } catch (e) {
                                /* noop */
                            }
                            try {
                                if (tblApi.responsive)
                                    tblApi.responsive.recalc();
                            } catch (e) {
                                /* noop */
                            }
                        }, 80);
                    }
                } catch (e) {
                    // individual table init failed; continue gracefully
                    console.warn(
                        "DataTable init/adjust failed for .defaultDataTable",
                        e
                    );
                }
            });
        }
    } catch (e) {
        console.warn("Global DataTable init failed", e);
    }

    // Input mask for LRN (lrn) - idempotent
    try {
        if ($ && $.fn && typeof $.fn.inputmask === "function") {
            // Apply 12-digit numeric mask; works for inputs rendered server-side and those loaded via AJAX
            // Primary field name is 'lrn' after migration; keep fallback for 'student_code' while rolling out
            $('[name="lrn"]').each(function () {
                try {
                    $(this).inputmask("999999999999", { placeholder: "" });
                } catch (e) {
                    /* noop */
                }
            });
            $('[name="student_code"]').each(function () {
                try {
                    $(this).inputmask("999999999999", { placeholder: "" });
                } catch (e) {
                    /* noop */
                }
            });
        }
    } catch (e) {
        // fail silently; inputmask not available
    }

    // Any non-delegated handlers that must be re-attached can go here.
    // (Most of the behavior uses delegated handlers attached to document/body.)

    // Update sidebar active link based on current location
    try {
        updateActiveNav();
    } catch (e) {
        /* ignore */
    }

    // If the loaded page contains an updated profile image, sync it into the sidebar
        try {
            var $contentProfileImg = $(".content-wrapper")
                .find('img[src*="/uploads/profile_images/"]')
                .first();
            if ($contentProfileImg && $contentProfileImg.length) {
                var src = $contentProfileImg.attr("src");
                // Normalize before comparing/assigning to avoid propagating double-base
                var normSrc = normalizeAppPath(src);
                var $sidebarImg = $("#sidebar .nav-profile-image img").first();
                if (
                    $sidebarImg &&
                    $sidebarImg.length &&
                    normalizeAppPath($sidebarImg.attr("src")) !== normSrc
                ) {
                    $sidebarImg.attr("src", normSrc);
                }
            }
        } catch (e) {
            /* ignore */
        }

    // Attach profile form handlers (AJAX submit and client-side preview)
    try {
        // Profile form: upload and profile changes - Use proper event delegation
        var $profileForm = $("#profileForm");
        if ($profileForm && $profileForm.length) {
            console.log("[initPage] Profile form found, attaching handlers");

            // Preview file input
            $profileForm
                .find('input[type=file][name="profile_image"]')
                .off("change.preview")
                .on("change.preview", function (e) {
                    var file = this.files && this.files[0];
                    if (!file) return;
                    var reader = new FileReader();
                    reader.onload = function (ev) {
                        // Show a small preview in the form if an img.preview exists, otherwise create one
                        var $img = $profileForm.find("img.profile-preview");
                        if (!$img || $img.length === 0) {
                            $img = $(
                                '<img class="profile-preview" style="max-width:100px; margin-top:10px; display:block;">'
                            );
                            $profileForm
                                .find('input[type=file][name="profile_image"]')
                                .after($img);
                        }
                        $img.attr("src", ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });

            // AJAX submit - Remove any existing handlers first
            $profileForm.off("submit").on("submit", function (e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("[Profile Form] Submit intercepted");

                var form = this;
                var fd = new FormData(form);

                // CRITICAL: Add the submit button value manually (FormData doesn't capture clicked button)
                fd.append("submit", "profile");

                // ensure _csrfToken included via meta
                var csrf = $('meta[name="csrfToken"]').attr("content");
                if (csrf) fd.append("_csrfToken", csrf);

                console.log("[Profile Form] Sending AJAX request");
                fetch($(form).attr("action") || window.location.href, {
                    method: "POST",
                    credentials: "same-origin",
                    body: fd,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                })
                    .then(function (resp) {
                        console.log(
                            "[Profile Form] Response status:",
                            resp.status
                        );
                        return resp.text().then(function (text) {
                            console.log(
                                "[Profile Form] Raw response (first 500 chars):",
                                text.substring(0, 500)
                            );
                            try {
                                return JSON.parse(text);
                            } catch (err) {
                                console.error(
                                    "[Profile Form] JSON parse error:",
                                    err
                                );
                                return {
                                    success: false,
                                    message:
                                        "Invalid JSON response from server.",
                                };
                            }
                        });
                    })
                    .then(function (data) {
                        console.log("[Profile Form] Response data:", data);
                        if (data && data.success) {
                            // Update sidebar image if provided
                            if (data.profile_image) {
                                var $sidebarImg = $(
                                    "#sidebar .nav-profile-image img"
                                ).first();
                                    var norm = normalizeAppPath(data.profile_image);
                                    // Add a small cache-busting query so browsers update immediately
                                    var normWithBust =
                                        norm + (norm.indexOf("?") === -1 ? "?_=" + Date.now() : "&_=" + Date.now());
                                    if ($sidebarImg && $sidebarImg.length)
                                        $sidebarImg.attr("src", normWithBust);
                                    // Also update any preview images
                                    $profileForm
                                        .find("img.profile-preview")
                                        .attr("src", normWithBust);
                                    // Update any profile image tags in the current content (profile/details page)
                                    try {
                                        $(".content-wrapper")
                                            .find('img[src*="/uploads/profile_images/"]')
                                            .each(function () {
                                                $(this).attr("src", normWithBust);
                                            });
                                    } catch (e) {
                                        /* noop */
                                    }
                                    // Remove any temporary profile-preview images inserted during file selection
                                    try {
                                        $profileForm.find('img.profile-preview').remove();
                                    } catch (e) {
                                        /* noop */
                                    }
                            }
                            if (data.full_name) {
                                $(
                                    "#sidebar .nav-profile-text .font-weight-bold"
                                ).text(data.full_name);
                            }
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "success",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 1800,
                                    title: data.message || "Profile saved",
                                });
                            } else {
                                alert(data.message || "Profile saved");
                            }
                        } else {
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "error",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000,
                                    title:
                                        data && data.message
                                            ? data.message
                                            : "Error saving profile",
                                });
                            } else {
                                alert(
                                    data && data.message
                                        ? data.message
                                        : "Error saving profile"
                                );
                            }
                        }
                    })
                    .catch(function (err) {
                        console.error("Profile AJAX save failed", err);
                        if (typeof Swal !== "undefined") {
                            Swal.fire({
                                icon: "error",
                                title: "Network error",
                                text: "Unable to save profile. Please try again.",
                            });
                        } else {
                            alert("Network error saving profile.");
                        }
                    });

                return false; // Extra safety to prevent default submission
            });
        } else {
            console.log("[initPage] Profile form NOT found");
        }

        // Password form AJAX submit
        var $passwordForm = $("#passwordForm");
        if ($passwordForm && $passwordForm.length) {
            console.log("[initPage] Password form found, attaching handlers");

            $passwordForm.off("submit").on("submit", function (e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("[Password Form] Submit intercepted");

                var form = this;
                var fd = new FormData(form);

                // CRITICAL: Add the submit button value manually
                fd.append("submit", "password");

                var csrf = $('meta[name="csrfToken"]').attr("content");
                if (csrf) fd.append("_csrfToken", csrf);

                console.log("[Password Form] Sending AJAX request");
                fetch($(form).attr("action") || window.location.href, {
                    method: "POST",
                    credentials: "same-origin",
                    body: fd,
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                })
                    .then(function (resp) {
                        console.log(
                            "[Password Form] Response status:",
                            resp.status
                        );
                        return resp.text().then(function (text) {
                            console.log(
                                "[Password Form] Raw response (first 500 chars):",
                                text.substring(0, 500)
                            );
                            try {
                                return JSON.parse(text);
                            } catch (err) {
                                console.error(
                                    "[Password Form] JSON parse error:",
                                    err
                                );
                                return {
                                    success: false,
                                    message:
                                        "Invalid JSON response from server.",
                                };
                            }
                        });
                    })
                    .then(function (data) {
                        console.log("[Password Form] Response data:", data);
                        if (data && data.success) {
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "success",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 1800,
                                    title: data.message || "Password updated",
                                });
                            } else {
                                alert(data.message || "Password updated");
                            }
                            // reset password fields
                            $passwordForm.find("input[type=password]").val("");
                        } else {
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    icon: "error",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000,
                                    title:
                                        data && data.message
                                            ? data.message
                                            : "Error updating password",
                                });
                            } else {
                                alert(
                                    data && data.message
                                        ? data.message
                                        : "Error updating password"
                                );
                            }
                        }
                    })
                    .catch(function (err) {
                        console.error("Password AJAX failed", err);
                        if (typeof Swal !== "undefined") {
                            Swal.fire({
                                icon: "error",
                                title: "Network error",
                                text: "Unable to update password. Please try again.",
                            });
                        } else {
                            alert("Network error updating password.");
                        }
                    });

                return false; // Extra safety to prevent default submission
            });
        } else {
            console.log("[initPage] Password form NOT found");
        }
    } catch (e) {
        console.warn("Profile handlers not attached", e);
    }

    // DEBUG: Log sample data-link values after page init to diagnose doubled IDs
    try {
        var sampleToggle = document.querySelector(".toggleQuestionStatusBtn");
        var sampleDelete = document.querySelector(".deleteQuestionBtn");
        if (sampleToggle)
            console.debug(
                "[initPage] toggleBtn data-link=",
                sampleToggle.getAttribute("data-link")
            );
        if (sampleDelete)
            console.debug(
                "[initPage] deleteBtn data-link=",
                sampleDelete.getAttribute("data-link")
            );
    } catch (e) {
        /* ignore */
    }

    // ============================================================
    // QUESTIONS SUBJECT DROPDOWN HANDLER (AJAX reload)
    // Intercept the subject dropdown change and reload via AJAX
    // ============================================================
    $(document).on('change', '#questionsSubject', function(e) {
        e.preventDefault();
        var selectedSubject = $(this).val();
        var $form = $('#questionsSubjectForm');
        
        if (!$form.length) return;
        
        // Build URL with query parameter
        var formAction = $form.attr('action');
        var url = formAction + (formAction.indexOf('?') > -1 ? '&' : '?') + 'questionsSubject=' + encodeURIComponent(selectedSubject);
        
        console.info('[Questions] Loading questions for subject via AJAX:', selectedSubject);
        
        // Use loadPage if available for AJAX navigation
        if (typeof loadPage === 'function') {
            loadPage(url, true);
        } else {
            // Fallback to manual AJAX
            $.ajax({
                url: url,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(html) {
                    try {
                        var $newContent = $(html);
                        var $contentWrapper = $('#content-wrapper');
                        if ($contentWrapper.length) {
                            $contentWrapper.html($newContent.html());
                            initPage();
                        }
                    } catch (err) {
                        console.error('[Questions] Failed to update content', err);
                    }
                },
                error: function() {
                    console.error('[Questions] AJAX load failed');
                }
            });
        }
    });
}

    // ============================================================
    // MELC MODAL HANDLERS (global)
    // Add / Edit MELC using modal patterned after students
    // ============================================================

    window.openMelcFormModal = function (url, title) {
        var $modal = $("#melcModal");
        if ($modal.length === 0) {
            console.error("MELC modal not found");
            return;
        }

        $modal.find(".modal-title").text(title || "MELC");
        $modal
            .find(".modal-body")
            .html(
                '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            );
        // Show modal (Bootstrap or fallback)
        if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
            try {
                var inst = new bootstrap.Modal($modal[0]);
                inst.show();
                $modal.data("bs.instance", inst);
            } catch (e) {
                $modal.addClass("show").css("display", "block");
                if ($(".modal-backdrop").length === 0)
                    $('<div class="modal-backdrop fade show"></div>').appendTo(
                        document.body
                    );
            }
        } else {
            $modal.addClass("show").css("display", "block");
            if ($(".modal-backdrop").length === 0)
                $('<div class="modal-backdrop fade show"></div>').appendTo(
                    document.body
                );
        }

        if (!url) return;
        $.ajax({
            url: url,
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            cache: false,
        })
            .done(function (html) {
                try {
                    if (
                        typeof html === "string" &&
                        /<form[^>]+action=["']?[^"'>]*\/users?\/login["']?/i.test(html)
                    ) {
                        window.location.reload();
                        return;
                    }
                } catch (err) {
                    /* ignore */
                }
                $modal.find(".modal-body").html(html);

                // Attach AJAX submit for form inside modal
                $modal
                    .find("form")
                    .off("submit")
                    .on("submit", function (e) {
                        e.preventDefault();
                        var $form = $(this);
                        $form.find(".is-invalid").removeClass("is-invalid");
                        $form
                            .find(".invalid-feedback")
                            .addClass("d-none")
                            .text("");

                        // Client-side guard: ensure a subject is selected
                        var $subject = $form.find('[name="subject_id"]');
                        var subjVal = $subject.val();
                        if (!subjVal) {
                            $subject.addClass('is-invalid');
                            var $fb = $form.find('.invalid-feedback[data-field="subject_id"]');
                            if ($fb && $fb.length) {
                                $fb.removeClass('d-none').text('Please select a subject.');
                            } else {
                                alert('Please select a subject.');
                            }
                            return;
                        }

                        var method = ($form.attr("method") || "POST").toUpperCase();
                        var csrf = $("meta[name=csrfToken]").attr("content") || "";
                        $.ajax({
                            url: $form.attr("action"),
                            method: method,
                            data: $form.serialize(),
                            dataType: "json",
                            headers: { "X-CSRF-Token": csrf },
                        })
                            .done(function (res) {
                                if (res && res.success) {
                                    // Close modal then refresh the page fragment to update the list
                                    try {
                                        var inst = $modal.data("bs.instance");
                                        if (inst && typeof inst.hide === "function") inst.hide();
                                        else {
                                            $modal.removeClass("show").css("display", "none");
                                            $(".modal-backdrop").remove();
                                        }
                                    } catch (e) {}
                                    // Prefer reloading the current fragment via AJAX loader if available
                                    try {
                                        if (typeof loadPage === "function") {
                                            loadPage(window.location.href);
                                        } else {
                                            window.location.reload();
                                        }
                                    } catch (e) {
                                        window.location.reload();
                                    }
                                } else {
                                    if (res && res.errors) {
                                        $.each(res.errors, function (field, errs) {
                                            var $input = $modal.find('[name="' + field + '"]');
                                            $input.addClass("is-invalid");
                                            $modal
                                                .find('.invalid-feedback[data-field="' + field + '"]')
                                                .removeClass("d-none")
                                                .text(errs && errs.join ? errs.join(", ") : errs);
                                        });
                                    } else {
                                        var msg = res && res.message ? res.message : "Please check the form for errors.";
                                        if (window.Swal && typeof Swal.fire === "function") Swal.fire({ icon: "error", title: "Error", text: msg });
                                        else alert(msg);
                                    }
                                }
                            })
                            .fail(function (jqXHR) {
                                console.error("MELC form submit failed", jqXHR.status, jqXHR.responseText);
                                if (window.Swal && typeof Swal.fire === "function") Swal.fire({ icon: "error", title: "Error", text: "Server error" });
                                else alert("Server error");
                            });
                    });
            })
            .fail(function (jqXHR) {
                var resp = jqXHR.responseText || "";
                if (resp && resp.length > 50) {
                    $modal.find(".modal-body").html(resp);
                } else {
                    $modal
                        .find(".modal-body")
                        .html('<div class="text-danger text-center">Failed to load form. (' + jqXHR.status + ")</div>");
                }
            });
    };

    // Delegated handlers for Add/Edit MELC buttons
    $(document).on("click", ".btn-add-melc, .btn-edit-melc", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var raw = $(this).data("href") || $(this).attr("href") || "";
        var href = raw && raw !== "#" && raw !== "javascript:void(0)" ? raw : $(this).data("href");
        var title = $(this).hasClass("btn-add-melc") ? "Add MELC" : "Edit MELC";
        window.openMelcFormModal(href, title);
    });

    // Close handlers for MELC modal (data-bs-dismiss & backdrop & ESC)
    $(document).on("click", '#melcModal [data-bs-dismiss="modal"], #melcModal .btn-close', function (e) {
        e.preventDefault();
        var $m = $("#melcModal");
        try {
            var inst = $m.data("bs.instance");
            if (inst && typeof inst.hide === "function") {
                inst.hide();
                return;
            }
        } catch (err) {}
        $m.removeClass("show").css("display", "none");
        $(".modal-backdrop").remove();
    });

    $(document).on("click", ".modal-backdrop", function (e) {
        var $m = $("#melcModal");
        if ($m.length && $m.hasClass("show")) {
            try {
                var inst = $m.data("bs.instance");
                if (inst && typeof inst.hide === "function") {
                    inst.hide();
                    return;
                }
            } catch (e) {}
            $m.removeClass("show").css("display", "none");
            $(".modal-backdrop").remove();
        }
    });

    $(document).on("keydown", function (e) {
        var $m = $("#melcModal");
        if ($m.length && $m.hasClass("show") && (e.key === "Escape" || e.key === "Esc" || e.keyCode === 27)) {
            try {
                var inst = $m.data("bs.instance");
                if (inst && typeof inst.hide === "function") {
                    inst.hide();
                    return;
                }
            } catch (e) {}
            $m.removeClass("show").css("display", "none");
            $(".modal-backdrop").remove();
        }
    });

// Update the sidebar navigation active state to reflect the current URL
function updateActiveNav() {
    try {
        var currentPath = window.location.pathname.replace(/\/+$/, ""); // strip trailing slash
        // Only select nav-links that are NOT inside nav-profile (exclude profile card at top)
        var $links = $("#sidebar .nav-item:not(.nav-profile) a.nav-link");
        $links.each(function () {
            var $a = $(this);
            // Clear previous active classes
            $a.removeClass("active");
            $a.closest(".nav-item").removeClass("active");
        });
        // Find best matching link: exact pathname match or prefix match
        var bestMatch = null;
        var bestLen = 0;
        $links.each(function () {
            var href = $(this).attr("href") || "";
            try {
                var linkUrl = new URL(href, window.location.origin);
                var lp = linkUrl.pathname.replace(/\/+$/, "");
                if (currentPath === lp) {
                    bestMatch = this;
                    bestLen = lp.length;
                    return false; // exact match -> stop
                }
                if (currentPath.indexOf(lp) === 0 && lp.length > bestLen) {
                    bestMatch = this;
                    bestLen = lp.length;
                }
            } catch (e) {
                // ignore malformed href
            }
        });
        if (bestMatch) {
            var $bm = $(bestMatch);
            $bm.addClass("active");
            $bm.closest(".nav-item").addClass("active");
        }
    } catch (e) {
        console.warn("updateActiveNav failed", e);
    }
}

// PJAX-like page loader: fetches URL, replaces .content-wrapper, updates title and csrf meta
function loadPage(url, pushState = true) {
    if (!url) return;
    
    // Prevent concurrent loadPage calls (debounce)
    if (window._loadPageInProgress) {
        console.warn("[loadPage] already in progress, skipping duplicate call", url);
        return;
    }
    window._loadPageInProgress = true;
    
    // Ensure the initial full-page loader (Lottie) does not re-appear on AJAX navigation.
    try {
        var pageLoader = document.getElementById("page-loader");
        if (pageLoader) {
            pageLoader.classList.add("hidden");
            // set display none to be extra-safe in case CSS transitions are still running
            pageLoader.style.display = "none";
        }
    } catch (e) {
        /* ignore */
    }
    var fetchUrl = url;
    console.debug("[loadPage] fetching", fetchUrl);
    fetch(fetchUrl, {
        credentials: "same-origin",
        headers: { "X-Requested-With": "XMLHttpRequest" },
    })
        .then(function (resp) {
            // If the server responds with 401/403, session likely expired -> do a full reload so user is redirected to login
            if (resp.status === 401 || resp.status === 403) {
                console.warn(
                    "[loadPage] server returned",
                    resp.status,
                    "— reloading top-level to trigger login redirect"
                );
                window.location.reload();
                throw new Error("session-expired");
            }
            return resp.text();
        })
        .then(function (html) {
            try {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, "text/html");

                // Detect if server returned the login page HTML (session expired or redirect to login)
                // If so, perform a full reload so the browser receives the proper redirect/login flow
                try {
                    var loginForm = doc.querySelector(
                        'form[action*="/Users/login"], form[action*="/users/login"]'
                    );
                    var authBtn = doc.querySelector(".auth-form-btn");
                    var h4 = doc.querySelector("h4");
                    var loginHeading =
                        h4 &&
                        h4.textContent &&
                        h4.textContent.indexOf("Welcome to GENTA") !== -1;
                    // NOTE: pages like Profile may contain email/password inputs (for updates)
                    // so DO NOT treat presence of those inputs alone as a login page.
                    // Keep detection strict: require explicit login form/action, auth button, or known heading.

                    if (loginForm || authBtn || loginHeading) {
                        // Log which detection triggered before reloading
                        console.warn(
                            "[loadPage] detected login HTML from AJAX response",
                            {
                                url: fetchUrl,
                                loginForm: !!loginForm,
                                authBtn: !!authBtn,
                                loginHeading: !!loginHeading,
                            }
                        );
                        // Force a full reload so the browser performs the proper redirect/login flow
                        window.location.reload();
                        return;
                    }
                } catch (e) {
                    /* ignore and continue */
                }

                // Replace content-wrapper with a small fade animation, initialize widgets while hidden,
                // then reveal. This reduces layout jitter and DataTables flicker.
                var newContent = doc.querySelector(".content-wrapper");
                var curContent = document.querySelector(".content-wrapper");
                if (newContent && curContent) {
                    try {
                        // Fade out current content
                        curContent.style.transition =
                            curContent.style.transition || "opacity 220ms ease";
                        curContent.style.opacity = "0";
                    } catch (e) {
                        /* ignore */
                    }

                    setTimeout(function () {
                        try {
                            // Preserve current height to avoid layout jump while swapping content
                            var curHeight = 0;
                            try {
                                curHeight = Math.ceil(
                                    curContent.getBoundingClientRect().height
                                );
                            } catch (e) {
                                curHeight = 0;
                            }
                            if (curHeight > 0) {
                                curContent.style.minHeight = curHeight + "px";
                            }

                            // Prepare new content off-DOM to minimize reflows
                            var temp = document.createElement("div");
                            temp.innerHTML = newContent.innerHTML;

                            // DEBUG: log a sample data-link to see if IDs are already doubled in the server response
                            try {
                                var sampleLink =
                                    temp.querySelector("[data-link]");
                                if (sampleLink) {
                                    console.debug(
                                        "[loadPage] sample data-link in temp:",
                                        sampleLink.getAttribute("data-link")
                                    );
                                }
                            } catch (e) {
                                /* ignore */
                            }

                            // Collect scripts from temp and remove them from fragment so they are executed separately
                            var scripts = temp.querySelectorAll("script");
                            var scriptList = [];
                            scripts.forEach(function (s) {
                                scriptList.push({
                                    src: s.src || null,
                                    text: s.textContent || "",
                                });
                                if (s.parentNode) s.parentNode.removeChild(s);
                            });

                            // Fast replace children with minimal DOM thrash
                            while (curContent.firstChild)
                                curContent.removeChild(curContent.firstChild);
                            Array.prototype.slice
                                .call(temp.childNodes)
                                .forEach(function (n) {
                                    curContent.appendChild(n);
                                });

                            // Execute collected scripts sequentially (append then remove)
                            scriptList.forEach(function (item) {
                                try {
                                    var ns = document.createElement("script");
                                    if (item.src) {
                                        ns.src = item.src;
                                        ns.async = false;
                                    } else {
                                        ns.text = item.text;
                                    }
                                    document.body.appendChild(ns);
                                    setTimeout(function () {
                                        if (ns.parentNode)
                                            ns.parentNode.removeChild(ns);
                                    }, 0);
                                } catch (e) {
                                    /* ignore script injection errors */
                                }
                            });

                            // Initialize page widgets while content is already in DOM but hidden
                            try {
                                initPage();
                            } catch (e) {
                                /* ignore */
                            }

                            // Trigger walkthrough resume check after AJAX navigation
                            try {
                                if (window.WalkthroughSystem && typeof WalkthroughSystem.checkResume === 'function') {
                                    // Wait for content to settle before checking resume
                                    setTimeout(function() {
                                        try {
                                            // Check if there's a resume marker
                                            var hasResume = false;
                                            try {
                                                if (window.sessionStorage) {
                                                    hasResume = !!sessionStorage.getItem('genta_walkthrough_resume');
                                                }
                                            } catch(e) {}
                                            
                                            // Only resume if: there's a marker AND (it's tour navigation OR no active tour)
                                            var shouldResume = hasResume && (window._tourNavigating || !window._activeShepherdTour);
                                            
                                            if (shouldResume) {
                                                console.info('[loadPage] Calling checkResume', { hasResume: hasResume, tourNavigating: !!window._tourNavigating });
                                                // Clear the navigation flag before resuming
                                                window._tourNavigating = false;
                                                WalkthroughSystem.checkResume();
                                            } else {
                                                console.info('[loadPage] Skipping checkResume', { hasResume: hasResume, tourNavigating: !!window._tourNavigating, hasActiveTour: !!window._activeShepherdTour });
                                            }
                                        } catch(e) {
                                            console.warn('[loadPage] walkthrough resume check failed', e);
                                        }
                                    }, 900);
                                }
                            } catch(e) {
                                /* ignore */
                            }

                            // Allow a short settling time for fonts/CSS and DataTables to initialize
                            setTimeout(function () {
                                try {
                                    if ($ && $.fn && $.fn.DataTable) {
                                        $(".defaultDataTable").each(
                                            function () {
                                                try {
                                                    var tbl =
                                                        $(this).DataTable();
                                                    if (tbl) {
                                                        try {
                                                            tbl.columns().adjust();
                                                        } catch (e) {}
                                                        try {
                                                            if (tbl.responsive)
                                                                tbl.responsive.recalc();
                                                        } catch (e) {}
                                                        try {
                                                            tbl.draw(false);
                                                        } catch (e) {}
                                                    }
                                                } catch (e) {
                                                    /* ignore if not initialized */
                                                }
                                            }
                                        );
                                    }
                                } catch (e) {
                                    /* ignore */
                                }

                                // Reveal content with fade-in
                                try {
                                    curContent.style.visibility = "visible";
                                    curContent.style.opacity = "1";
                                } catch (e) {
                                    /* ignore */
                                }
                            }, 140);
                        } catch (e) {
                            /* ignore */
                        }
                    }, 220);
                } else {
                    // Fallback to full page navigation if selector not found
                    window._loadPageInProgress = false;
                    window.location.href = url;
                    return;
                }

                // Update document title
                var newTitle = doc.querySelector("title");
                if (newTitle) document.title = newTitle.textContent;

                // Update CSRF token meta if present
                var newCsrf = doc.querySelector('meta[name="csrfToken"]');
                if (newCsrf) {
                    var curCsrf = document.querySelector(
                        'meta[name="csrfToken"]'
                    );
                    if (curCsrf)
                        curCsrf.setAttribute(
                            "content",
                            newCsrf.getAttribute("content")
                        );
                    else document.head.appendChild(newCsrf.cloneNode(true));
                }

                // NOTE: scripts and initPage() are already called inside the setTimeout above
                // (while content is hidden) so we do NOT re-execute them here to avoid duplication.

                // Push history state
                if (pushState && window.history && history.pushState) {
                    history.pushState({ url: url }, "", url);
                    // Ensure sidebar active state updates after history change
                    try {
                        setTimeout(updateActiveNav, 10);
                    } catch (e) {
                        /* ignore */
                    }
                }
                
                // Clear the in-progress flag after successful load
                setTimeout(function() {
                    window._loadPageInProgress = false;
                }, 100);
            } catch (e) {
                console.error("AJAX page load failed, falling back", e);
                window._loadPageInProgress = false;
                window.location.href = url;
            }
        })
        .catch(function () {
            window._loadPageInProgress = false;
            window.location.href = url;
        });
}

// Intercept back/forward navigation to load via AJAX
window.addEventListener("popstate", function (e) {
    var state = e.state;
    if (state && state.url) {
        loadPage(state.url, false);
    } else {
        // If no state, just reload
        window.location.reload();
    }
});

$(document).ready(function () {
    // CAKEPHP CSRF TOKEN SUPPORT FOR AJAX
    var csrfToken = $('meta[name="csrfToken"]').attr("content");
    if (csrfToken) {
        $.ajaxSetup({ headers: { "X-CSRF-Token": csrfToken } });
    } else {
        console.warn(
            "CSRF Token meta tag not found! AJAX requests will fail with 403."
        );
    }
    // ============================================================
    // PROFESSIONAL WALKTHROUGH SYSTEM WITH PAGE-SPECIFIC TOURS
    // ============================================================

    const WalkthroughSystem = {
        currentStep: 0,
        currentTour: null,
        isActive: false,
        overlay: null,
        // Handlers that run before attempting to resolve a step's target.
        // Each handler should accept the step object and return true if it opened/handled UI.
        preOpenHandlers: {},

        // Register a named preOpen handler: WalkthroughSystem.registerPreOpenHandler('openStudents', fn)
        registerPreOpenHandler(name, fn){
            if(!this.preOpenHandlers) this.preOpenHandlers = {};
            try{ this.preOpenHandlers[name] = fn; }catch(e){}
        },

        // Default handlers: try to open common navigation destinations or UI elements.
        // These are intentionally conservative and attempt clicks only when a reasonable match is found.
        // You can override or add handlers with registerPreOpenHandler.
        _defaultPreOpenHandlersRegistered: false,
        _registerDefaultPreOpenHandlers: function(){
            if(this._defaultPreOpenHandlersRegistered) return; this._defaultPreOpenHandlersRegistered = true;
            var self = this;

            this.preOpenHandlers.openStudentsSidebar = function(step){
                try{
                    var current = (window.location.pathname || '').toLowerCase();
                    // If already on students list, don't navigate
                    if(current.indexOf('/students') !== -1) return false;
                    var links = document.querySelectorAll('#sidebar a.nav-link, a.nav-link');
                    for(var i=0;i<links.length;i++){
                        var a = links[i];
                        var t = (a.textContent||a.innerText||'').trim().toLowerCase();
                        if(t.indexOf('student') !== -1){ try{ a.click(); return true; }catch(e){} }
                    }
                }catch(e){}
                return false;
            };

            this.preOpenHandlers.openQuestionsMenu = function(step){
                try{
                    var current = (window.location.pathname || '').toLowerCase();
                    if(current.indexOf('/questions') !== -1 || current.indexOf('/quiz') !== -1) return false;
                    var links = document.querySelectorAll('#sidebar a.nav-link, a.nav-link');
                    for(var i=0;i<links.length;i++){
                        var a = links[i];
                        var t = (a.textContent||a.innerText||'').trim().toLowerCase();
                        if(t.indexOf('question') !== -1 || t.indexOf('quiz') !== -1){ try{ a.click(); return true; }catch(e){} }
                    }
                }catch(e){}
                return false;
            };

            this.preOpenHandlers.openProfile = function(step){
                try{
                    // Prefer explicit profile nav if present
                    var current = (window.location.pathname || '').toLowerCase();
                    if(current.indexOf('/profile') !== -1) return false;
                    var prof = document.querySelector('#sidebar .nav-profile a.nav-link, .nav-profile a.nav-link');
                    if(prof){ try{ prof.click(); return true; }catch(e){} }
                    var links = document.querySelectorAll('#sidebar a.nav-link, a.nav-link');
                    for(var i=0;i<links.length;i++){
                        var a = links[i];
                        var t = (a.textContent||a.innerText||'').trim().toLowerCase();
                        if(t.indexOf('profile') !== -1 || t.indexOf('account') !== -1){ try{ a.click(); return true; }catch(e){} }
                    }
                }catch(e){}
                return false;
            };

            this.preOpenHandlers.openAddStudent = function(step){
                try{
                    // Only try if we're on the students page
                    var current = (window.location.pathname || '').toLowerCase();
                    if(current.indexOf('/students') === -1) return false;
                    // Find obvious Add Student buttons
                    var sel = 'button, a';
                    var elems = document.querySelectorAll(sel);
                    for(var i=0;i<elems.length;i++){
                        var e = elems[i];
                        var txt = (e.textContent||'').trim().toLowerCase();
                        if(txt.indexOf('add student') !== -1 || txt.indexOf('add new student') !== -1){ try{ e.click(); return true; }catch(e){} }
                    }
                    // Try a class-based fallback
                    var btn = document.querySelector('.btn-add-student, .add-student, .btn-add');
                    if(btn){ try{ btn.click(); return true; }catch(e){} }
                }catch(e){}
                return false;
            };
        },

        // Page-specific tour definitions
        tours: {
            dashboard: [
                {
                    id: 'dashboard-welcome',
                    title: "👋 Welcome to GENTA!",
                    text: "Welcome to your Dashboard! Let's take a quick tour to help you get started with the platform.",
                    target: null,
                    navigateTo: '/GENTA/teacher',
                    position: "center",
                    icon: "🎓",
                },
                {
                    id: 'dashboard-overview',
                    title: "Dashboard Overview",
                    text: "This is your main dashboard where you can see important statistics and quick access to key features.",
                    target: ".page-header, .row.grid-margin",
                    position: "bottom",
                    icon: "📊",
                },
                {
                    id: 'dashboard-sidebar',
                    title: "Sidebar Navigation",
                    text: "Use this sidebar to navigate between different sections: Dashboard, Students, Quiz Management, and your Profile.",
                    target: ".sidebar",
                    position: "right",
                    icon: "🧭",
                },
                {
                    id: 'dashboard-profile',
                    title: "Your Profile",
                    text: "Click here to view and edit your profile information, change your password, or update your profile picture.",
                    target: ".nav-profile",
                    position: "right",
                    icon: "👤",
                },
                {
                    id: 'dashboard-help-btn',
                    title: "Help Button",
                    text: "Need help anytime? Click the help button in the top navigation to restart this walkthrough.",
                    target: "#help-walkthrough-btn",
                    position: "bottom",
                    icon: "❓",
                },
            ],
            students: [
                {
                    id: 'students-management',
                    title: "👥 Students Management",
                    text: "This page allows you to manage all your students. You can add, edit, view details, and track their progress.",
                    target: null,
                    // keep navigation only on the first step so we land on the students page if starting elsewhere
                    navigateTo: '/GENTA/teacher/dashboard/students',
                    position: "center",
                    icon: "🎓",
                },
                {
                    id: 'students-add-new',
                    title: "Add New Student",
                    text: "Click this button to add a new student to your class. You'll need to provide their basic information.",
                    // Prefer class-based selectors; jQuery :contains is a fallback resolved at runtime
                    target: '.btn-add-student, .add-student, .btn-add, button.add-student, a.add-student, button:contains("Add Student"), a:contains("Add Student")',
                    preOpen: 'openStudentsSidebar',
                    position: "bottom",
                    icon: "➕",
                },
                {
                    id: 'students-search-filter',
                    title: "Search & Filter",
                    text: "Use the search box to quickly find students by name, email, or other details.",
                    target: '#student-search, input[type="search"], .dataTables_filter input, .dataTables_filter input',
                    position: "bottom",
                    icon: "🔍",
                },
                {
                    id: 'students-actions',
                    title: "Student Actions",
                    text: "For each student, you can view their detailed profile, edit their information, or remove them from your class.",
                    target: "tbody tr:first .action-buttons, tbody tr:first td:last, table.defaultDataTable tbody tr:first .action-buttons, .card .table tbody tr:first .action-buttons",
                    preOpen: 'openStudentsSidebar',
                    position: "left",
                    icon: "⚙️",
                },
                {
                    id: 'students-performance',
                    title: "Student Performance",
                    text: "Click on any student to view their quiz performance, grades, and progress over time.",
                    // Attach to the first row if present, otherwise fall back to the page header
                    target: "tbody tr:first, .defaultDataTable tbody tr:first, .page-header, .content-wrapper",
                    position: "right",
                    icon: "📈",
                },
                {
                    id: 'students-export',
                    title: "Export / Bulk Actions",
                    text: "Use export or bulk action controls to manage many students at once.",
                    target: '.export-students, .btn-export, button:contains("Export"), a:contains("Export")',
                    position: 'bottom',
                    icon: '📤'
                },
            ],
            questions: [
                {
                    id: 'questions-management',
                    title: "❓ Quiz Management",
                    text: "Welcome to Quiz Management! Here you can create, edit, and organize all your quiz questions.",
                    target: null,
                    navigateTo: '/GENTA/teacher/dashboard/questions',
                    position: "center",
                    icon: "📝",
                },
                {
                    id: 'questions-add-new',
                    title: "Add New Question",
                    text: "Click here to create a new quiz question. You can add multiple choice, true/false, or other question types.",
                    target: '.btn-add-question, .add-question, .btn-primary.add-question, button:contains("Add Question"), a:contains("Add Question")',
                    preOpen: 'openQuestionsMenu',
                    position: "bottom",
                    icon: "➕",
                },
                {
                    id: 'questions-list',
                    title: "Question List",
                    text: "All your questions are listed here. You can see the question text, type, difficulty level, and status at a glance.",
                    target: ".card .table, .questions-table, table.defaultDataTable",
                    position: "top",
                    icon: "📋",
                },
                {
                    id: 'questions-edit-delete',
                    title: "Edit & Delete",
                    text: "Use these action buttons to edit question details or remove questions you no longer need.",
                    target: "tbody tr:first .action-buttons, tbody tr:first .btn-group, table.defaultDataTable tbody tr:first .action-buttons",
                    position: "left",
                    icon: "✏️",
                },
                {
                    id: 'questions-toggle-status',
                    title: "Toggle Status",
                    text: "Quickly enable or disable questions using the toggle switch. Disabled questions won't appear in active quizzes.",
                    target: 'tbody tr:first .toggle-status, tbody tr:first input[type="checkbox"], .toggleQuestionStatusBtn',
                    position: "left",
                    icon: "🔄",
                },
                {
                    id: 'questions-search',
                    title: 'Search & Filters',
                    text: 'Use the search box and filters to find questions quickly.',
                    target: '.dataTables_filter input, input[type="search"], #question-search',
                    position: 'bottom',
                    icon: '🔎'
                },
            ],
            profile: [
                {
                    id: 'profile-overview',
                    title: "👤 Your Profile",
                    text: "This is your profile page where you can manage your personal information and account settings.",
                    target: null,
                    navigateTo: '/GENTA/teacher/dashboard/profile',
                    position: "center",
                    icon: "⚙️",
                },
                {
                    id: 'profile-picture',
                    title: "Profile Picture",
                    text: "Upload or change your profile picture here. This image will be displayed throughout the platform.",
                    target: '.profile-image, #profileForm .form-group:first, input[type="file"], .nav-profile-image img',
                    preOpen: 'openProfile',
                    position: "right",
                    icon: "📷",
                },
                {
                    id: 'profile-personal-info',
                    title: "Personal Information",
                    text: "Update your name, email, and other personal details. Make sure to save your changes after editing.",
                    target: "#profileForm, form#profileForm",
                    position: "right",
                    icon: "📝",
                },
                {
                    id: 'profile-change-password',
                    title: "Change Password",
                    text: "For security, you can change your password here. You'll need to enter your current password first.",
                    target: "#passwordForm, form#passwordForm",
                    position: "right",
                    icon: "🔒",
                },
                {
                    id: 'profile-save-changes',
                    title: "Save Changes",
                    text: "Don't forget to click the Edit or Save button after making any changes to update your profile.",
                    target: 'button[type="submit"], button.save-profile, button:contains("Save"), button:contains("Edit")',
                    position: "top",
                    icon: "💾",
                },
                {
                    id: 'profile-notifications',
                    title: 'Notifications & Preferences',
                    text: 'Adjust notification settings and other preferences here.',
                    target: '.profile-settings, .notification-settings, #notificationForm',
                    position: 'left',
                    icon: '🔔'
                }
            ],
        },

        init() {
            this.injectStyles();
            this.createOverlay();
            // Ensure default preOpen handlers are registered so steps can request UI pre-opening
            try{ this._registerDefaultPreOpenHandlers(); }catch(e){}
        },

        injectStyles() {
            // Minimal placeholder for compatibility. Shepherd will provide the primary
            // overlay, spotlight, and popup styling. Keep a tiny rule to ensure the
            // highlighted class used by the legacy code doesn't break layout if applied.
            if (document.getElementById("walkthrough-styles")) return;
            const style = document.createElement("style");
            style.id = "walkthrough-styles";
            style.innerHTML = `
                /* Legacy compatibility: minimal highlight rule */
                .walkthrough-highlight { outline: 3px solid rgba(0,0,0,0.06); }
            `;
            document.head.appendChild(style);
        },

        createOverlay() {
            // No-op: Shepherd will manage the modal overlay/spotlight. Keep method
            // to preserve API expected by other parts of the system.
            if (this.overlay) return;
            this.overlay = null;
        },

        detectCurrentPage() {
            const path = window.location.pathname.toLowerCase();
            if (path.includes("/students")) return "students";
            if (path.includes("/questions") || path.includes("/quiz"))
                return "questions";
            if (path.includes("/profile")) return "profile";
            return "dashboard";
        },

        start(pageName = null, isHelp = false) {
            if (this.isActive) return;

            const page = pageName || this.detectCurrentPage();
            this.currentTour = this.tours[page] || this.tours.dashboard;
            this.currentStep = 0;
            this.isActive = true;
            this.isHelpMode = isHelp;

            // If Shepherd is available and the initializer created a mapping, prefer it.
            try {
                if (typeof WalkthroughSystem.startShepherd === "function") {
                    // Shepherd will handle overlay, spotlight, scrolling, and buttons.
                    WalkthroughSystem.startShepherd(page);
                    return;
                }
            } catch (e) {
                console.warn("Shepherd start failed, falling back to legacy walkthrough", e);
            }

            // Fallback: legacy Swal-based walkthrough
            this.disableScroll();
            this.showStep();
        },

        disableScroll() {
            // Store current scroll position and overflow values
            this.scrollTop =
                window.pageYOffset || document.documentElement.scrollTop;
            this.scrollLeft =
                window.pageXOffset || document.documentElement.scrollLeft;

            // Store original overflow values
            this.originalOverflow = {
                html: document.documentElement.style.overflow,
                body: document.body.style.overflow,
            };

            // Prevent scrolling
            document.documentElement.style.overflow = "hidden";
            document.body.style.overflow = "hidden";

            // Create bound function to maintain context
            if (!this.boundPreventScroll) {
                this.boundPreventScroll = this.preventScroll.bind(this);
            }

            // Lock scroll position
            window.addEventListener("scroll", this.boundPreventScroll, {
                passive: false,
            });
            window.addEventListener("wheel", this.boundPreventScroll, {
                passive: false,
            });
            window.addEventListener("touchmove", this.boundPreventScroll, {
                passive: false,
            });
        },

        preventScroll(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        },

        enableScroll() {
            // Restore original overflow values
            if (this.originalOverflow) {
                document.documentElement.style.overflow =
                    this.originalOverflow.html;
                document.body.style.overflow = this.originalOverflow.body;
            }

            // Remove scroll prevention listeners
            if (this.boundPreventScroll) {
                window.removeEventListener("scroll", this.boundPreventScroll);
                window.removeEventListener("wheel", this.boundPreventScroll);
                window.removeEventListener(
                    "touchmove",
                    this.boundPreventScroll
                );
            }
        },

        showStep() {
            if (this.currentStep >= this.currentTour.length) {
                this.complete();
                return;
            }
            const step = this.currentTour[this.currentStep];

            // Remove previous highlights
            $(".walkthrough-highlight").removeClass("walkthrough-highlight");

            // Find and validate target
            let $target = null;
            if (step.target) {
                $target = $(step.target).first();

                // If target exists, highlight it. If not, continue without highlight.
                if ($target && $target.length) {
                    $target.addClass("walkthrough-highlight");
                } else {
                    console.debug("Walkthrough target not found, showing dialog centered:", step.target);
                }
            }

            // Build popup HTML
            const progress = Math.round(((this.currentStep + 1) / this.currentTour.length) * 100);
            const html = `${step.icon ? `<div class="walkthrough-icon">${step.icon}</div>` : ``}
                <div style="text-align: left; margin-bottom: 1rem;">${step.text}</div>
                <div class="walkthrough-progress">
                    <div class="walkthrough-progress-bar"><div class="walkthrough-progress-fill" style="width: ${progress}%"></div></div>
                    <div class="walkthrough-progress-text">Step ${this.currentStep + 1} of ${this.currentTour.length}</div>
                </div>`;

            const swalConfig = {
                title: step.title,
                html: html,
                showCancelButton: this.currentStep > 0,
                confirmButtonText: this.currentStep === this.currentTour.length - 1 ? "✓ Finish" : "Next →",
                cancelButtonText: "← Back",
                allowOutsideClick: true,
                allowEscapeKey: true,
                showCloseButton: true,
                width: "420px",
                position: "center",
                willClose: () => {
                    // Clean up when dialog is about to close
                    $(".walkthrough-highlight").removeClass("walkthrough-highlight");
                }
            };

            Swal.fire(swalConfig).then((result) => {
                if (result.isConfirmed) {
                    this.currentStep++;
                    this.showStep();
                } else if (result.dismiss === Swal.DismissReason.cancel && this.currentStep > 0) {
                    this.currentStep--;
                    this.showStep();
                } else {
                    // Dismiss -> end tour
                    this.isActive = false;
                    $(".walkthrough-highlight").removeClass("walkthrough-highlight");
                    this.enableScroll();
                }
            });
        },

        // Removed calculatePosition/positionPopup/createArrow helpers — Shepherd handles positioning.

        complete() {
            // Clean up immediately
            this.isActive = false;
            // No overlay to remove
            $(".walkthrough-highlight").removeClass("walkthrough-highlight");
            $(".walkthrough-arrow").remove();

            // Re-enable scrolling
            this.enableScroll();

            // Mark as completed
            if (
                !this.isHelpMode &&
                typeof window.walkthrough_shown !== "undefined" &&
                !window.walkthrough_shown
            ) {
                $.post(buildUrl("/users/set-walkthrough-shown")).done(function (
                    data
                ) {
                    if (data && data.walkthrough_shown) {
                        window.walkthrough_shown = data.walkthrough_shown;
                    } else {
                        window.walkthrough_shown = true;
                    }
                });
            }

            // Close any existing Swal first
            Swal.close();

            // Small delay to ensure previous Swal is fully closed
            setTimeout(() => {
                Swal.fire({
                    title: '<span style="color: var(--brand-primary);">🎉 Tour Complete!</span>',
                    html: '<div style="text-align: center; font-size: 1rem; line-height: 1.6;">You\'re all set! If you need help again, just click the help button in the top navigation bar.</div>',
                    icon: "success",
                    confirmButtonText: "Got it!",
                    confirmButtonColor: "var(--brand-primary)",
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    buttonsStyling: true,
                });
            }, 200);
        },

        cancel() {
            this.isActive = false;
            // No overlay to remove
            $(".walkthrough-highlight").removeClass("walkthrough-highlight");
            $(".walkthrough-arrow").remove();

            // Re-enable scrolling
            this.enableScroll();

            // Close the Swal dialog
            Swal.close();
        },
    };

    // Make the full object available on window for external initializers (shepherd-init.js)
    try{ window.WalkthroughSystem = WalkthroughSystem; console.info('WalkthroughSystem attached to window'); }catch(e){ console.warn('Failed to attach WalkthroughSystem to window', e); }

    // Initialize walkthrough system
    WalkthroughSystem.init();

    // Auto-start for first-time users
    var walkthroughShown =
        typeof window.walkthrough_shown !== "undefined"
            ? window.walkthrough_shown
            : false;
    if (typeof Swal !== "undefined" && !walkthroughShown) {
        setTimeout(function () {
            WalkthroughSystem.start(null, false);
        }, 800);
    }

    // Help button: show a chooser so the teacher can pick a specific tour
    function showTourChooser() {
        var options = {
            dashboard: 'Dashboard — Overview & quick actions',
            students: 'Students — Manage students',
            questions: 'Quiz — Manage questions and questions bank',
            profile: 'Profile — Update your account'
        };

        Swal.fire({
            title: 'Which walkthrough do you want to run?',
            input: 'select',
            inputOptions: options,
            inputPlaceholder: 'Select a walkthrough',
            showCancelButton: true,
            confirmButtonText: 'Start',
            width: '520px'
        }).then(function(result){
            if (!result.isConfirmed || !result.value) return;

            console.info('Walkthrough chooser selected:', result.value, 'startShepherd available?', typeof WalkthroughSystem.startShepherd);

            // If Shepherd isn't ready, show a non-blocking toast so the user knows
            // we will fall back to the lightweight dialog experience.
            if (typeof WalkthroughSystem.startShepherd !== 'function') {
                try {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Advanced tour unavailable — starting simple walkthrough',
                            showConfirmButton: false,
                            timer: 2200
                        });
                    }
                } catch (e) {
                    /* ignore toast failure */
                }
            }

            // Ensure we can re-run tours immediately by clearing the cookie/local marker
            if (typeof $.removeCookie === 'function') {
                try { $.removeCookie('walkthrough_shown', { path: '/' }); } catch(e){}
            } else {
                try { document.cookie = 'walkthrough_shown=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;'; } catch(e){}
            }

            var key = result.value;
            
            // Pre-check: if we're already on the target page for this tour, mark first step as navigated
            // to prevent unnecessary page refresh
            try {
                if (window.WalkthroughSystem && WalkthroughSystem.tours && WalkthroughSystem.tours[key]) {
                    var firstStep = WalkthroughSystem.tours[key][0];
                    if (firstStep && firstStep.navigateTo) {
                        var currentPath = window.location.pathname + (window.location.search || '');
                        var targetPath = firstStep.navigateTo;
                        // Check if we're already on the target page using strict matching
                        // Normalize paths to avoid false positives (e.g., /students shouldn't match /dashboard)
                        var currentNorm = currentPath.toLowerCase().replace(/\/+$/, '');
                        var targetNorm = targetPath.toLowerCase().replace(/\/+$/, '');
                        var alreadyOnPage = (currentNorm === targetNorm) ||
                                           (currentNorm === targetNorm + '/') ||
                                           (currentNorm + '/' === targetNorm);
                        if (alreadyOnPage) {
                            console.info('Already on target page for tour, pre-marking navigation as complete', { tour: key, step: firstStep.id, targetPath: targetPath, currentPath: currentPath });
                            if (window.sessionStorage && firstStep.id) {
                                var navKey = 'genta_nav_' + key + '_' + firstStep.id;
                                sessionStorage.setItem(navKey, 'true');
                                console.info('Set navigation flag BEFORE starting tour:', navKey);
                            }
                        } else {
                            console.info('Not on target page, tour will navigate', { current: currentNorm, target: targetNorm });
                        }
                    }
                }
            } catch(e) {
                console.warn('Pre-check navigation failed', e);
            }
            
            // Small delay to ensure sessionStorage write completes before tour starts
            setTimeout(function() {
                // Check if navigation is needed for first step
                var needsNavigation = false;
                try {
                    if (window.WalkthroughSystem && WalkthroughSystem.tours && WalkthroughSystem.tours[key]) {
                        var firstStep = WalkthroughSystem.tours[key][0];
                        if (firstStep && firstStep.navigateTo) {
                            var currentPath = window.location.pathname + (window.location.search || '');
                            var currentNorm = currentPath.toLowerCase().replace(/\/+$/, '');
                            var targetNorm = firstStep.navigateTo.toLowerCase().replace(/\/+$/, '');
                            var alreadyOnPage = (currentNorm === targetNorm) ||
                                               (currentNorm === targetNorm + '/') ||
                                               (currentNorm + '/' === targetNorm);
                            needsNavigation = !alreadyOnPage;
                        }
                    }
                } catch(e) {
                    console.warn('Navigation check failed', e);
                }

                // If navigation is needed, navigate first THEN start tour via resume mechanism
                if (needsNavigation) {
                    console.info('Tour requires navigation, navigating first before showing tour', key);
                    try {
                        var firstStep = WalkthroughSystem.tours[key][0];
                        var navUrl = firstStep.navigateTo;
                        
                        // Store resume marker so tour starts after navigation
                        if (window.sessionStorage) {
                            var resume = { key: key, stepId: firstStep.id, expectedPath: navUrl };
                            sessionStorage.setItem('genta_walkthrough_resume', JSON.stringify(resume));
                            console.info('Set resume marker before navigation', resume);
                            
                            // Mark navigation flag
                            var navKey = 'genta_nav_' + key + '_' + firstStep.id;
                            sessionStorage.setItem(navKey, 'true');
                        }
                        
                        // Navigate using loadPage
                        window._tourNavigating = true;
                        if (typeof loadPage === 'function') {
                            loadPage(navUrl);
                        } else {
                            window.location.href = navUrl;
                        }
                        return;
                    } catch(e) {
                        console.warn('Pre-navigation failed, starting tour anyway', e);
                    }
                }

                // No navigation needed or fallback - start tour normally
                // Prefer Shepherd if available
                if (typeof WalkthroughSystem.startShepherd === 'function') {
                    try { console.info('Starting Shepherd tour for', key); WalkthroughSystem.startShepherd(key); return; } catch(e){ console.warn('Shepherd start failed', e); }
                } else {
                    console.info('Shepherd not available, falling back to legacy WalkthroughSystem.start');
                }
                // Fallback to legacy start
                try { WalkthroughSystem.start(key, true); } catch(e){ console.error(e); }
            }, 50);
        });
    }

    $(document).on("click", "#help-walkthrough-btn", function (e) {
        e.preventDefault();
        showTourChooser();
    }); // Add walkthrough highlight CSS
    if (!document.getElementById("walkthrough-highlight-style")) {
        const style = document.createElement("style");
        style.id = "walkthrough-highlight-style";
            style.innerHTML = `
            .walkthrough-highlight {
                box-shadow: 0 0 0 4px var(--brand-primary), 0 2px 16px rgba(0,0,0,0.15);
                z-index: 1051 !important;
                position: relative;
                border-radius: 8px;
                transition: box-shadow 0.3s;
            }
            .swal2-container .walkthrough-popup {
                border-radius: 12px;
                box-shadow: 0 4px 32px rgba(0,0,0,0.18);
            }
        `;
        document.head.appendChild(style);
    }

    // Only show walkthrough if not already shown (cookie check, use $.cookie if available)
    // Use server-provided walkthrough_shown if available
    var walkthroughShown =
        typeof window.walkthrough_shown !== "undefined"
            ? window.walkthrough_shown
            : typeof $.cookie === "function"
            ? $.cookie("walkthrough_shown")
            : document.cookie.indexOf("walkthrough_shown=1") !== -1;
    if (typeof Swal !== "undefined" && !walkthroughShown) {
        setTimeout(function () {
            showAnchoredWalkthrough(false);
        }, 600); // Delay to ensure page is ready
    }

    // Deprecated legacy handler removed. The help button now opens the chooser above.
    // Run page initializers
    initPage();

    // DASHBOARD - STUDENT
    $("#editRemarksBtn").on("click", function (e) {
        e.preventDefault();

        $("#editRemarksBtn").addClass("d-none");
        $("#submitRemarksBtn").removeClass("d-none");

        $("#remarks").prop("disabled", false);
    });

    // DASHBOARD - QUESTIONS: use SweetAlert2 for delete confirmation
    // ============================================================
    // DELETE QUESTION BUTTON HANDLER
    // ============================================================
    $(document).on("click", ".deleteQuestionBtn", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $btn = $(this);
        var questionId = $btn.attr("data-question-id");

        if (!questionId) {
            console.error("No question ID found on delete button");
            return;
        }

        Swal.fire({
            title: "Delete Question?",
            text: "This will mark the question as deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d33",
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var csrf = $('meta[name="csrfToken"]').attr("content") || "";
            var url = buildUrl(
                "/teacher/dashboard/delete-question/" + questionId
            );

            fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: "_csrfToken=" + encodeURIComponent(csrf),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        // Remove the table row
                        $btn.closest("tr").fadeOut(300, function () {
                            $(this).remove();
                        });

                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text:
                                data.message || "Question deleted successfully",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || "Failed to delete question",
                        });
                    }
                })
                .catch(function (error) {
                    console.error("Delete error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Network error occurred",
                    });
                });
        });
    });

    // ============================================================
    // DELETE ASSESSMENTS (student quiz records) HANDLER
    // ============================================================
    $(document).on("click", ".deleteAssessmentsBtn", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $btn = $(this);
        var studentHash = $btn.attr("data-student");
        var subjectHash = $btn.attr("data-subject");

        if (!studentHash || !subjectHash) {
            console.error(
                "Missing student or subject id for deleteAssessments"
            );
            return;
        }

        Swal.fire({
            title: "Delete all attempts?",
            text: "This will permanently delete all attempts for this student in the selected subject. This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete them",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d33",
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var csrf = $('meta[name="csrfToken"]').attr("content") || "";
            var url = buildUrl(
                "/teacher/dashboard/delete-assessments/" +
                    studentHash +
                    "/" +
                    subjectHash
            );

            fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: "_csrfToken=" + encodeURIComponent(csrf),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        // Remove the table row for this grouped assessment.
                        // If the row is managed by DataTables, use the API to remove it so
                        // the table's internal state stays consistent; otherwise fall back
                        // to a simple fade-out DOM removal.
                        try {
                            var $tr = $btn.closest('tr');
                            var $tbl = $tr.closest('table');
                            // Helper to fall back to a full reload when we cannot reliably
                            // update the client-side table (e.g., responsive child rows,
                            // server-side processing, or other DataTables internals).
                            function fallbackRefresh() {
                                try {
                                    // If DataTables is using ajax source, prefer reload to keep paging
                                    if ($.fn && $.fn.DataTable) {
                                        try {
                                            var mainDt = $(".defaultDataTable").DataTable();
                                            if (mainDt && mainDt.ajax && typeof mainDt.ajax.reload === 'function') {
                                                mainDt.ajax.reload(null, false);
                                                return;
                                            }
                                        } catch (e) {
                                            /* ignore and fallback to page reload */
                                        }
                                    }
                                    // As a last resort, reload the current page fragment via AJAX
                                    if (typeof loadPage === 'function') {
                                        loadPage(window.location.href);
                                        return;
                                    }
                                    // Finally fallback to full reload
                                    window.location.reload();
                                } catch (ee) { window.location.reload(); }
                            }

                            if (
                                $.fn &&
                                $.fn.DataTable &&
                                $tbl.length &&
                                $.fn.DataTable.isDataTable($tbl[0] ? $tbl[0] : $tbl)
                            ) {
                                var dt = $($tbl).DataTable();
                                // Attempt to remove the row via DataTables API and redraw without resetting paging
                                try {
                                    var rowApi = dt.row($tr[0]);
                                    // If DataTables couldn't find the node (responsive child rows), try to locate by data-id
                                    if (!rowApi || rowApi.node() === null) {
                                        // try to find parent row node by searching for closest parent with data-id
                                        var pid = $tr.attr('data-id') || $tr.data('id');
                                        if (!pid) {
                                            // try to find a parent row element two levels up (responsive child)
                                            var parent = $tr.closest('table').find('tbody tr').has($tr).first();
                                            if (parent && parent.length) {
                                                rowApi = dt.row(parent[0]);
                                            }
                                        } else {
                                            var bySelector = dt.rows().nodes().to$().filter(function() { return $(this).attr('data-id') == pid || $(this).data('id') == pid; });
                                            if (bySelector && bySelector.length) rowApi = dt.row(bySelector[0]);
                                        }
                                    }

                                    if (rowApi && rowApi.node() !== null) {
                                        rowApi.remove();
                                        dt.draw(false);
                                        // If the row is still present in DOM after a short delay, fallback
                                        setTimeout(function(){ if ($tr.closest('table').find('tbody').find($tr).length) fallbackRefresh(); }, 220);
                                    } else {
                                        // Could not find a matching DataTable row node; fallback
                                        fallbackRefresh();
                                    }
                                } catch (e) {
                                    // If remove fails, try a DOM-only fade removal then check
                                    try {
                                        $tr.fadeOut(300, function () { $(this).remove(); });
                                        setTimeout(function(){ if ($tbl.find('tbody').find($tr).length) fallbackRefresh(); }, 400);
                                    } catch (ee) { fallbackRefresh(); }
                                }
                            } else {
                                // Not a DataTable - remove DOM row and ensure it's gone
                                try {
                                    $tr.fadeOut(300, function () { $(this).remove(); });
                                    setTimeout(function(){ if ($tbl.find('tbody').find($tr).length) fallbackRefresh(); }, 400);
                                } catch (e) { fallbackRefresh(); }
                            }
                        } catch (e) {
                            console.warn('Failed to remove assessment row', e);
                            // conservative fallback
                            try { loadPage && loadPage(window.location.href); } catch (ee) { window.location.reload(); }
                        }
                        // Diagnostic: indicate successful delete and which update path we used
                        try { console.info('[deleteAssessments] deletion successful, attempting client-side update'); } catch(e) {}

                        // If this table is managed by DataTables with an ajax source, prefer reloading
                        try {
                            var $tbl = $btn.closest('table');
                            function conservativeFallbackAfterReload() {
                                try {
                                    if (typeof loadPage === 'function') {
                                        loadPage(window.location.href);
                                        return;
                                    }
                                } catch (e) {}
                                try { window.location.reload(); } catch (e) {}
                            }

                            if ($.fn && $.fn.DataTable && $tbl.length && $.fn.DataTable.isDataTable($tbl[0] ? $tbl[0] : $tbl)) {
                                var tableApi = $($tbl).DataTable();
                                try {
                                    // Only call ajax.reload if DataTables was configured with a valid ajax URL.
                                    var canReload = false;
                                    try {
                                        if (tableApi && tableApi.ajax && typeof tableApi.ajax.reload === 'function') {
                                            if (typeof tableApi.ajax.url === 'function') {
                                                var url = tableApi.ajax.url();
                                                canReload = !!url;
                                            } else {
                                                // If ajax.url() isn't available, be conservative and assume no remote source
                                                canReload = false;
                                            }
                                        }
                                    } catch (inner) { canReload = false; }

                                    if (canReload) {
                                        console.info('[deleteAssessments] reloading DataTable via ajax.reload');
                                        // Attach a one-time error handler to catch DataTables Ajax failure
                                        $tbl.one('error.dt', function (e, settings, techNote, message) {
                                            console.warn('[deleteAssessments] DataTable ajax reload emitted error:', techNote, message);
                                            conservativeFallbackAfterReload();
                                        });
                                        tableApi.ajax.reload(null, false);
                                    } else {
                                        // No ajax source configured for this table - draw to ensure internal state updated
                                        try { tableApi.draw(false); } catch (ee) { /* ignore */ }
                                    }
                                } catch (e) {
                                    console.info('[deleteAssessments] DataTable ajax.reload failed, drawing instead', e);
                                    try { tableApi.draw(false); } catch (ee) {}
                                }
                            } else if ($.fn && $.fn.DataTable && $.fn.DataTable.isDataTable('.defaultDataTable')) {
                                try {
                                    var main = $('.defaultDataTable').DataTable();
                                    var mainCanReload = false;
                                    try {
                                        if (main && main.ajax && typeof main.ajax.reload === 'function' && typeof main.ajax.url === 'function') {
                                            mainCanReload = !!main.ajax.url();
                                        }
                                    } catch (inner) { mainCanReload = false; }
                                    if (mainCanReload) {
                                        console.info('[deleteAssessments] reloading main defaultDataTable via ajax.reload');
                                        $('.defaultDataTable').one('error.dt', function (e, settings, techNote, message) {
                                            console.warn('[deleteAssessments] main DataTable ajax reload error:', techNote, message);
                                            conservativeFallbackAfterReload();
                                        });
                                        main.ajax.reload(null, false);
                                    } else {
                                        try { main.draw(false); } catch (e) { /* ignore */ }
                                    }
                                } catch (e) {
                                    /* ignore */
                                }
                            }
                        } catch (e) { console.warn('[deleteAssessments] table reload attempt failed', e); }

                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: data.message || "Assessments deleted",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                data.message || "Failed to delete assessments",
                        });
                    }
                })
                .catch(function (err) {
                    console.error("Delete assessments error", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Network error occurred",
                    });
                });
        });
    });

    // ============================================================
    // DOCUMENT (Tailored Module / Analysis) CHECK BEFORE OPENING
    // Prevents opening a broken link - show SweetAlert if not available
    // ============================================================
    $(document).on("click", "a.doc-link", function (e) {
        var $a = $(this);
        var href = $a.attr("href");
        var type = $a.data("type");
        var studentHash = $a.data("student");

        if (!href || !type || !studentHash) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        var url = buildUrl(
            "/teacher/dashboard/check-document/" + studentHash + "/" + type
        );
        fetch(url, {
            method: "GET",
            credentials: "same-origin",
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then(function (resp) {
                return resp.json();
            })
            .then(function (data) {
                if (data && data.exists) {
                    // Open the original href in a new tab/window
                    window.open(href, "_blank");
                } else {
                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            icon: "info",
                            title: "Not available",
                            text:
                                "There is no available " +
                                (type === "tailored"
                                    ? "tailored module"
                                    : "analysis document") +
                                " to download for this student.",
                        });
                    } else {
                        alert(
                            "There is no available " +
                                (type === "tailored"
                                    ? "tailored module"
                                    : "analysis document") +
                                " to download for this student."
                        );
                    }
                }
            })
            .catch(function (err) {
                console.error("Document check failed", err);
                // On error, fall back to opening the link to allow remote server to handle 404
                window.open(href, "_blank");
            });
    });

    // ============================================================
    // TOGGLE QUESTION STATUS BUTTON HANDLER
    // ============================================================
    $(document).on("click", ".toggleQuestionStatusBtn", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $btn = $(this);
        var questionId = $btn.attr("data-question-id");

        if (!questionId) {
            console.error("No question ID found on toggle button");
            return;
        }

        Swal.fire({
            title: "Change Status?",
            text: "Toggle between Active and Suspended",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, change it",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#3085d6",
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var csrf = $('meta[name="csrfToken"]').attr("content") || "";
            var url = buildUrl(
                "/teacher/dashboard/toggle-question-status/" + questionId
            );

            fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: "_csrfToken=" + encodeURIComponent(csrf),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        // Update status badge
                        var $badge = $btn.closest("tr").find(".status-badge");
                        if (data.newStatus === 1) {
                            $badge
                                .removeClass("bg-secondary")
                                .addClass("bg-success")
                                .text("Active");
                        } else {
                            $badge
                                .removeClass("bg-success")
                                .addClass("bg-secondary")
                                .text("Suspended");
                        }

                        Swal.fire({
                            icon: "success",
                            title: "Updated!",
                            text: data.message || "Status updated successfully",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || "Failed to update status",
                        });
                    }
                })
                .catch(function (error) {
                    console.error("Toggle error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Network error occurred",
                    });
                });
        });
    });

    // ============================================================
    // NAVIGATION HANDLERS
    // ============================================================

    // Sidebar navigation with active state (exclude profile card at top)
    $(document).on(
        "click",
        "#sidebar .nav-item:not(.nav-profile) a.nav-link",
        function (e) {
            var $a = $(this);
            var href = $a.attr("href");
            if (!href || $a.is("[data-no-ajax]")) return;

            e.preventDefault();
            try {
                // Only remove active from regular nav items, not the profile card
                $("#sidebar .nav-item:not(.nav-profile) a.nav-link")
                    .removeClass("active")
                    .closest(".nav-item")
                    .removeClass("active");
                $a.addClass("active").closest(".nav-item").addClass("active");
                loadPage(href);
            } catch (err) {
                console.warn(
                    "Sidebar AJAX navigation failed, falling back to full load",
                    err
                );
                window.location.href = href;
            }
        }
    );

    // Profile card click handler (top of sidebar)
    $(document).on(
        "click",
        "#sidebar .nav-item.nav-profile a.nav-link",
        function (e) {
            var $a = $(this);
            var href = $a.attr("href");
            if (!href || $a.is("[data-no-ajax]")) return;

            e.preventDefault();
            loadPage(href);
        }
    );

    // General link interception for AJAX navigation
    $(document).on(
        "click",
        'a.nav-link, a.navbar-brand, a.menu-title, a:not([target="_blank"])',
        function (e) {
            var $a = $(this);
            var href = $a.attr("href");
            if (!href) return;

            // Don't intercept links that explicitly open modals or are marked no-ajax
            if ($a.is(".btn-edit-question, .btn-add-question")) return;

            var origin =
                window.location.origin ||
                window.location.protocol + "//" + window.location.host;
            if (href.indexOf("http") === 0 && href.indexOf(origin) !== 0)
                return;
            if (href.indexOf("#") === 0) return;
            if ($a.is("[data-no-ajax]")) return;
            if (href.match(/\.(pdf|zip|xls|xlsx|docx|png|jpg|jpeg)$/i)) return;

            e.preventDefault();
            loadPage(href);
        }
    );

    // Create/Edit question links (non-modal) - exclude links intended to open the modal
    $(document).on(
        "click",
        'a[href*="createEditQuestion"]:not(.btn-edit-question):not(.btn-add-question)',
        function (e) {
            var href = $(this).attr("href");
            if (!href) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            loadPage(href);
        }
    );

    // ============================================================
    // LOGOUT CONFIRMATION
    // ============================================================
    $(document).off("click.logout");
    $(document).on(
        "click.logout",
        'a[href*="/Users/logout"], a.nav-link[href*="/Users/logout"], a[href$="/Users/logout"], a[href$="/users/logout"]',
        function (e) {
            e.preventDefault();
            var logoutUrl = $(this).attr("href");
            console.log(
                "[SweetAlert2 Logout] Clicked logout link:",
                logoutUrl,
                "Swal:",
                typeof Swal
            );
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You will be logged out of your session.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "var(--brand-primary)",
                    cancelButtonColor: "rgba(228, 61, 61, 1)",
                    confirmButtonText: "Yes, log me out!",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = logoutUrl;
                    }
                });
            } else {
                // Fallback: just logout if SweetAlert2 is not loaded
                window.location.href = logoutUrl;
            }
        }
    );

    // ==========================================
    // QUESTION MODAL HANDLERS
    // ==========================================

    // Helper functions for question modal (with Bootstrap 5 fallback)
    window.showQuestionModal = function () {
        var $modal = $("#questionModal");
        if ($modal.length === 0) {
            console.error("Question modal not found");
            return;
        }

        if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
            var m = new bootstrap.Modal($modal[0]);
            m.show();
            $modal.data("bs.instance", m);
        } else {
            // Fallback for when Bootstrap JS is not available
            $modal.addClass("show").css("display", "block");
            if ($(".modal-backdrop").length === 0) {
                $('<div class="modal-backdrop fade show"></div>').appendTo(
                    document.body
                );
            }
        }
    };

    window.hideQuestionModal = function () {
        var $modal = $("#questionModal");
        var inst = $modal.data("bs.instance");
        if (inst && typeof inst.hide === "function") {
            inst.hide();
        } else {
            $modal.removeClass("show").css("display", "none");
            $(".modal-backdrop").remove();
        }
    };

    // Open question form in modal (Add/Edit)
    window.openQuestionFormModal = function (url, title) {
        var questionModalEl = $("#questionModal");
        if (questionModalEl.length === 0) {
            console.error("Question modal not found in DOM");
            return;
        }

        questionModalEl.find(".modal-title").text(title);
        questionModalEl
            .find(".modal-body")
            .html(
                '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            );
        window.showQuestionModal();

        $.ajax({
            url: url,
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            cache: false,
        })
            .done(function (html) {
                questionModalEl.find(".modal-body").html(html);

                // Attach form submit handler
                questionModalEl
                    .find("form")
                    .off("submit")
                    .on("submit", function (e) {
                        e.preventDefault();
                        var $form = $(this);
                        var formData = new FormData(this);

                        // Clear previous validation errors
                        $form.find(".is-invalid").removeClass("is-invalid");
                        $form.find(".invalid-feedback").remove();

                        $.ajax({
                            url: $form.attr("action"),
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: { "X-Requested-With": "XMLHttpRequest" },
                        })
                            .done(function (response) {
                                if (response.success) {
                                    window.hideQuestionModal();
                                    Swal.fire({
                                        icon: "success",
                                        title: "Success!",
                                        text:
                                            response.message ||
                                            "Question saved successfully!",
                                        timer: 2000,
                                        showConfirmButton: false,
                                    }).then(function () {
                                        loadPage("questions");
                                    });
                                } else {
                                    // Show validation errors
                                    if (response.errors) {
                                        $.each(
                                            response.errors,
                                            function (field, msgs) {
                                                var $input = $form.find(
                                                    '[name="' + field + '"]'
                                                );
                                                $input.addClass("is-invalid");
                                                var $feedback = $(
                                                    '<div class="invalid-feedback d-block"></div>'
                                                ).text(msgs.join(", "));
                                                $input.after($feedback);
                                            }
                                        );
                                    }
                                    if (response.message) {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: response.message,
                                        });
                                    }
                                }
                            })
                            .fail(function () {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Failed to save question. Please try again.",
                                });
                            });
                    });
            })
            .fail(function () {
                questionModalEl
                    .find(".modal-body")
                    .html(
                        '<div class="alert alert-danger">Failed to load form. Please try again.</div>'
                    );
            });
    };

    // Add question button handler (delegated)
    $(document).on("click", ".btn-add-question", function (e) {
        e.preventDefault();
        console.log("Add question button clicked");
        var href = buildUrl("/teacher/dashboard/createEditQuestion");
        window.openQuestionFormModal(href, "Add Question");
    });

    // ============================================================
    // STUDENT MODAL HANDLERS (global) - ensure early attachment
    // These handle the Add/Edit Student modals at a global level so
    // clicks before inline/template JS attaches won't trigger a full
    // navigation.
    // ============================================================

    window.openStudentFormModal = function (url, title) {
        var $modal = $("#studentModal");
        if ($modal.length === 0) {
            console.error("Student modal not found");
            return;
        }

        $modal.find(".modal-title").text(title || "Student");
        $modal
            .find(".modal-body")
            .html(
                '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            );
        // Use Bootstrap if available
        if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
            try {
                var inst = new bootstrap.Modal($modal[0]);
                inst.show();
                $modal.data("bs.instance", inst);
            } catch (e) {
                $modal.addClass("show").css("display", "block");
                if ($(".modal-backdrop").length === 0)
                    $('<div class="modal-backdrop fade show"></div>').appendTo(
                        document.body
                    );
            }
        } else {
            $modal.addClass("show").css("display", "block");
            if ($(".modal-backdrop").length === 0)
                $('<div class="modal-backdrop fade show"></div>').appendTo(
                    document.body
                );
        }

        if (!url) return;
        $.ajax({
            url: url,
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            cache: false,
        })
            .done(function (html) {
                try {
                    // If server returned login HTML, reload to trigger redirect
                    if (
                        typeof html === "string" &&
                        /<form[^>]+action=["']?[^"'>]*\/users?\/login["']?/i.test(
                            html
                        )
                    ) {
                        window.location.reload();
                        return;
                    }
                } catch (err) {
                    /* ignore */
                }
                $modal.find(".modal-body").html(html);

                // Ensure form submission via AJAX (same behaviour as template's handler)
                $modal
                    .find("form")
                    .off("submit")
                    .on("submit", function (e) {
                        e.preventDefault();
                        var $form = $(this);
                        $form.find(".is-invalid").removeClass("is-invalid");
                        $form
                            .find(".invalid-feedback")
                            .addClass("d-none")
                            .text("");

                        var method = (
                            $form.attr("method") || "POST"
                        ).toUpperCase();
                        var csrf =
                            $("meta[name=csrfToken]").attr("content") || "";
                        $.ajax({
                            url: $form.attr("action"),
                            method: method,
                            data: $form.serialize(),
                            dataType: "json",
                            headers: { "X-CSRF-Token": csrf },
                        })
                            .done(function (res) {
                                if (res && res.success) {
                                    // Update or add the student row in any existing DataTable on the page
                                    try {
                                        var s = res.student || {};
                                        var dt = null;
                                        if ($.fn && $.fn.DataTable) {
                                            if (
                                                $.fn.DataTable.isDataTable(
                                                    ".defaultDataTable"
                                                )
                                            ) {
                                                dt =
                                                    $(
                                                        ".defaultDataTable"
                                                    ).DataTable();
                                            }
                                        }

                                        function escapeHtml(str) {
                                            return String(
                                                str === undefined ||
                                                    str === null
                                                    ? ""
                                                    : str
                                            )
                                                .replace(/&/g, "&amp;")
                                                .replace(/</g, "&lt;")
                                                .replace(/>/g, "&gt;")
                                                .replace(/"/g, "&quot;")
                                                .replace(/'/g, "&#039;");
                                        }

                                        function generateActionButtonsHtml(
                                            sobj
                                        ) {
                                            var viewUrl = buildUrl(
                                                "/teacher/dashboard/student/" +
                                                    sobj.id
                                            );
                                            var editUrl = buildUrl(
                                                "/teacher/dashboard/editStudent/" +
                                                    sobj.id
                                            );
                                            var deleteUrl = buildUrl(
                                                "/teacher/dashboard/deleteStudent/" +
                                                    sobj.id
                                            );
                                            var escapedName = $("<div>")
                                                .text(sobj.name || "")
                                                .html();
                                            var html = "";
                                            html +=
                                                '<a class="btn btn-sm btn-outline-secondary btn-view-student" href="' +
                                                viewUrl +
                                                '" title="View"><i class="mdi mdi-eye-outline"></i></a> ';
                                            html +=
                                                '<a class="btn btn-sm btn-outline-primary btn-edit-student" href="#" data-href="' +
                                                editUrl +
                                                '" title="Edit" data-no-ajax="true"><i class="mdi mdi-pencil"></i></a> ';
                                            html +=
                                                '<a class="btn btn-sm btn-outline-danger btn-delete-student" href="#" data-url="' +
                                                deleteUrl +
                                                '" data-name="' +
                                                escapedName +
                                                '" title="Delete"><i class="mdi mdi-delete"></i></a>';
                                            return html;
                                        }

                                        if (dt && s && s.id) {
                                            // Try to find row by data-id attribute (encrypted id)
                                            var id = String(s.id || "").trim();
                                            var rowSelector = $(
                                                dt.rows().nodes()
                                            ).filter(function () {
                                                var attr =
                                                    $(this).attr("data-id");
                                                return (
                                                    attr &&
                                                    String(attr).trim() === id
                                                );
                                            });

                                            if (
                                                !rowSelector ||
                                                !rowSelector.length
                                            ) {
                                                // fallback: search DOM rows directly
                                                rowSelector = $(
                                                    ".defaultDataTable tbody tr"
                                                ).filter(function () {
                                                    var attr =
                                                        $(this).attr(
                                                            "data-id"
                                                        ) || $(this).data("id");
                                                    if (!attr) return false;
                                                    try {
                                                        return (
                                                            String(attr)
                                                                .trim()
                                                                .toLowerCase() ===
                                                            id.toLowerCase()
                                                        );
                                                    } catch (e) {
                                                        return false;
                                                    }
                                                });
                                            }

                                            if (
                                                !rowSelector ||
                                                !rowSelector.length
                                            ) {
                                                // last resort: match by LRN
                                                rowSelector = $(
                                                    ".defaultDataTable tbody tr"
                                                ).filter(function () {
                                                    var code = $(this)
                                                        .find("td")
                                                        .eq(0)
                                                        .text()
                                                        .trim();
                                                    return (
                                                        code === (s.lrn || "")
                                                    );
                                                });
                                            }

                                            if (
                                                rowSelector &&
                                                rowSelector.length
                                            ) {
                                                var node = rowSelector[0];
                                                dt.row(node)
                                                    .data([
                                                        '<span class="fw-bold">' +
                                                            escapeHtml(s.lrn) +
                                                            "</span>",
                                                        escapeHtml(s.name),
                                                        escapeHtml(
                                                            s.grade_section
                                                        ),
                                                        generateActionButtonsHtml(
                                                            s
                                                        ),
                                                    ])
                                                    .draw(false);
                                                try {
                                                    var updatedNode = dt
                                                        .row(node)
                                                        .node();
                                                    $(updatedNode).attr(
                                                        "data-id",
                                                        id
                                                    );
                                                    $(updatedNode)
                                                        .find("td")
                                                        .eq(3)
                                                        .addClass("text-center")
                                                        .css(
                                                            "white-space",
                                                            "nowrap"
                                                        );
                                                } catch (e) {
                                                    /* noop */
                                                }
                                            } else {
                                                // Add new row
                                                var newRow = dt.row
                                                    .add([
                                                        '<span class="fw-bold">' +
                                                            escapeHtml(s.lrn) +
                                                            "</span>",
                                                        escapeHtml(s.name),
                                                        escapeHtml(
                                                            s.grade_section
                                                        ),
                                                        generateActionButtonsHtml(
                                                            s
                                                        ),
                                                    ])
                                                    .draw(false)
                                                    .node();
                                                $(newRow).attr(
                                                    "data-id",
                                                    String(s.id || "")
                                                );
                                                try {
                                                    $(newRow)
                                                        .find("td")
                                                        .eq(3)
                                                        .addClass("text-center")
                                                        .css(
                                                            "white-space",
                                                            "nowrap"
                                                        );
                                                } catch (e) {}
                                            }
                                        }
                                    } catch (err) {
                                        console.warn(
                                            "Student table update failed",
                                            err
                                        );
                                    }

                                    // Hide modal
                                    try {
                                        var inst = $modal.data("bs.instance");
                                        if (
                                            inst &&
                                            typeof inst.hide === "function"
                                        )
                                            inst.hide();
                                        else {
                                            $modal
                                                .removeClass("show")
                                                .css("display", "none");
                                            $(".modal-backdrop").remove();
                                        }
                                    } catch (e) {}
                                    if (
                                        window.Swal &&
                                        typeof Swal.fire === "function"
                                    ) {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Success",
                                            text: res.message || "Saved",
                                            timer: 1500,
                                            showConfirmButton: false,
                                        });
                                    }
                                } else {
                                    if (res && res.errors) {
                                        $.each(
                                            res.errors,
                                            function (field, errs) {
                                                var $input = $modal.find(
                                                    '[name="' + field + '"]'
                                                );
                                                $input.addClass("is-invalid");
                                                $modal
                                                    .find(
                                                        '.invalid-feedback[data-field="' +
                                                            field +
                                                            '"]'
                                                    )
                                                    .removeClass("d-none")
                                                    .text(
                                                        errs && errs.join
                                                            ? errs.join(", ")
                                                            : errs
                                                    );
                                            }
                                        );
                                    } else {
                                        var msg =
                                            res && res.message
                                                ? res.message
                                                : "Please check the form for errors.";
                                        if (
                                            window.Swal &&
                                            typeof Swal.fire === "function"
                                        )
                                            Swal.fire({
                                                icon: "error",
                                                title: "Error",
                                                text: msg,
                                            });
                                        else alert(msg);
                                    }
                                }
                            })
                            .fail(function (jqXHR) {
                                console.error(
                                    "Student form submit failed",
                                    jqXHR.status,
                                    jqXHR.responseText
                                );
                                if (
                                    window.Swal &&
                                    typeof Swal.fire === "function"
                                )
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: "Server error",
                                    });
                                else alert("Server error");
                            });
                    });
            })
            .fail(function (jqXHR) {
                var resp = jqXHR.responseText || "";
                if (resp && resp.length > 50) {
                    $modal.find(".modal-body").html(resp);
                } else {
                    $modal
                        .find(".modal-body")
                        .html(
                            '<div class="text-danger text-center">Failed to load form. (' +
                                jqXHR.status +
                                ")</div>"
                        );
                }
            });
    };

    // Early delegated handler so clicks are handled even before per-page scripts run
    $(document).on(
        "click",
        ".btn-add-student, .btn-edit-student",
        function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var raw = $(this).data("href") || $(this).attr("href") || "";
            var href =
                raw && raw !== "#" && raw !== "javascript:void(0)"
                    ? raw
                    : $(this).data("href");
            var title = $(this).hasClass("btn-add-student")
                ? "Add Student"
                : "Edit Student";
            // call the global opener
            window.openStudentFormModal(href, title);
        }
    );

    // Global handlers to ensure modal can be closed and delete works on first-click
    // Close modal via data-bs-dismiss or btn-close even if per-page handlers haven't attached
    $(document).on(
        "click",
        '#studentModal [data-bs-dismiss="modal"], #studentModal .btn-close',
        function (e) {
            e.preventDefault();
            var $m = $("#studentModal");
            try {
                var inst = $m.data("bs.instance");
                if (inst && typeof inst.hide === "function") {
                    inst.hide();
                    return;
                }
            } catch (err) {
                /* ignore */
            }
            $m.removeClass("show").css("display", "none");
            $(".modal-backdrop").remove();
        }
    );

    // Clicking the backdrop should close the student modal when visible
    $(document).on("click", ".modal-backdrop", function (e) {
        var $m = $("#studentModal");
        if ($m.length && $m.hasClass("show")) {
            try {
                var inst = $m.data("bs.instance");
                if (inst && typeof inst.hide === "function") {
                    inst.hide();
                    return;
                }
            } catch (e) {}
            $m.removeClass("show").css("display", "none");
            $(".modal-backdrop").remove();
        }
    });

    // ESC key closes student modal
    $(document).on("keydown", function (e) {
        var $m = $("#studentModal");
        if (
            $m.length &&
            $m.hasClass("show") &&
            (e.key === "Escape" || e.key === "Esc" || e.keyCode === 27)
        ) {
            try {
                var inst = $m.data("bs.instance");
                if (inst && typeof inst.hide === "function") {
                    inst.hide();
                    return;
                }
            } catch (e) {}
            $m.removeClass("show").css("display", "none");
            $(".modal-backdrop").remove();
        }
    });

    // Global delete handler so first-click triggers Swal and AJAX delete instead of navigation
    $(document).on("click", ".btn-delete-student", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $btn = $(this);
        var url = $btn.data("url") || $btn.attr("href");
        var name = $btn.data("name") || "this student";
        var rowId = $btn.closest("tr").data("id");

        function doDelete() {
            $.post(url, {
                _csrfToken: $("meta[name=csrfToken]").attr("content"),
            })
                .done(function (res) {
                    if (res && res.success) {
                        // remove from DataTable if present
                        try {
                            if (
                                $.fn &&
                                $.fn.DataTable &&
                                $.fn.DataTable.isDataTable(".defaultDataTable")
                            ) {
                                var dt = $(".defaultDataTable").DataTable();
                                if (rowId) {
                                    var row = $(
                                        '.defaultDataTable tbody tr[data-id="' +
                                            rowId +
                                            '"]'
                                    );
                                    if (row.length) {
                                        dt.row(row[0]).remove().draw(false);
                                    }
                                } else {
                                    // fallback: reload the current table page
                                    dt.ajax &&
                                        dt.ajax.reload &&
                                        dt.ajax.reload();
                                }
                            }
                        } catch (err) {
                            console.warn(
                                "Delete: could not update DataTable",
                                err
                            );
                        }

                        if (window.Swal && typeof Swal.fire === "function") {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted",
                                text: res.message || "Deleted",
                            });
                        } else {
                            alert(res.message || "Deleted");
                        }
                    } else {
                        var errMsg =
                            res && res.message
                                ? res.message
                                : "Could not delete.";
                        if (window.Swal && typeof Swal.fire === "function") {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: errMsg,
                            });
                        } else {
                            alert(errMsg);
                        }
                    }
                })
                .fail(function (jqXHR) {
                    console.error(
                        "Student delete failed",
                        jqXHR.status,
                        jqXHR.responseText
                    );
                    if (window.Swal && typeof Swal.fire === "function") {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Server error",
                        });
                    } else {
                        alert("Server error");
                    }
                });
        }

        if (window.Swal && typeof Swal.fire === "function") {
            Swal.fire({
                title: "Delete?",
                text: "Are you sure you want to delete " + name + "?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it",
                cancelButtonText: "Cancel",
            }).then(function (result) {
                if (result.isConfirmed) doDelete();
            });
        } else {
            if (confirm("Are you sure you want to delete " + name + "?"))
                doDelete();
        }
    });

    // Edit question button handler (delegated)
    // Explicit handler for edit buttons (class .btn-edit-question) to avoid
    // any possible selector conflicts with generic anchor handlers.
    $(document).on("click", ".btn-edit-question", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $a = $(this);
        var href = $a.attr("href");
        console.log("[Edit Question] .btn-edit-question clicked, href=", href);
        if (!href) {
            console.warn("[Edit Question] no href found on .btn-edit-question");
            return;
        }
        window.openQuestionFormModal(href, "Edit Question");
    });

    // Fallback generic edit handler (kept for anchors without the class)
    $(document).on(
        "click",
        'a[href*="createEditQuestion"]:not(.btn-add-question):not(.btn-edit-question)',
        function (e) {
            if ($(this).closest("#questionModal").length > 0) {
                return; // Don't intercept clicks inside the modal
            }
            e.preventDefault();
            e.stopImmediatePropagation();
            console.log("[Edit Question] anchor handler clicked");
            var href = $(this).attr("href");
            window.openQuestionFormModal(href, "Edit Question");
        }
    );

    // Ensure modal can be closed when Bootstrap JS is not available or when using fallback
    // Close when any element inside the modal with data-bs-dismiss="modal" or .btn-close is clicked
    $(document).on(
        "click",
        '#questionModal [data-bs-dismiss="modal"], #questionModal .btn-close',
        function (e) {
            e.preventDefault();
            window.hideQuestionModal();
        }
    );

    // Clicking the backdrop should also hide the modal when using the fallback backdrop
    $(document).on("click", ".modal-backdrop", function (e) {
        // Only hide if our modal is visible
        if ($("#questionModal").hasClass("show")) {
            window.hideQuestionModal();
        }
    });

    // Allow ESC key to close the modal when shown (fallback and Bootstrap compatible)
    $(document).on("keydown", function (e) {
        var isShown = $("#questionModal").hasClass("show");
        if (
            isShown &&
            (e.key === "Escape" || e.key === "Esc" || e.keyCode === 27)
        ) {
            window.hideQuestionModal();
        }
    });

    // Global defensive cleanup for stray/backdrop overlays
    // Ensures Bootstrap .modal-backdrop and DataTables Responsive .dtr-modal* elements
    // are removed and body modal state is restored after modals hide.
    // Idempotent guard so handlers are only attached once even if script is included multiple times
    if (!window.__genta_modal_cleanup_attached) {
        window.__genta_modal_cleanup_attached = true;

        window.cleanupModalBackdrops = function () {
            try {
                // Remove Bootstrap backdrops
                document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                    if (b && b.parentNode) b.parentNode.removeChild(b);
                });
                // Remove DataTables Responsive modal elements if present
                document
                    .querySelectorAll('.dtr-modal-background, .dtr-modal')
                    .forEach(function (b) {
                        if (b && b.parentNode) b.parentNode.removeChild(b);
                    });
                // Clear body modal state and restore scrolling
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                // Remove any inline padding added by Bootstrap
                document.body.style.paddingRight = '';
            } catch (e) {
                console.warn('[GlobalModalCleanup] error', e);
            }
        };

        // Attach to Bootstrap modal lifecycle so cleanup runs after any modal is hidden
        if (window && document && document.addEventListener) {
            document.addEventListener('hidden.bs.modal', function () {
                // micro-delay to avoid racing with Bootstrap's own cleanup
                setTimeout(window.cleanupModalBackdrops, 10);
            });

            // When a modal is shown, defensively remove duplicate/older backdrops so they don't stack
            document.addEventListener('shown.bs.modal', function () {
                try {
                    var backdrops = document.querySelectorAll('.modal-backdrop, .dtr-modal-background');
                    if (backdrops && backdrops.length > 1) {
                        // Keep only the last backdrop element
                        for (var i = 0; i < backdrops.length - 1; i++) {
                            var b = backdrops[i];
                            if (b && b.parentNode) b.parentNode.removeChild(b);
                        }
                    }
                } catch (e) {
                    /* noop */
                }
            });

            // Defensive: cleanup on page show/navigation to catch backdrops left by earlier interactions
            window.addEventListener('pageshow', function () {
                setTimeout(window.cleanupModalBackdrops, 10);
            });
        }
    }
});
