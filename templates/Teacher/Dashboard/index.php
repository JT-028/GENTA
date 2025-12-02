<!-- STATISTICS CARDS -->
<div class="row">
    <!-- Total Students Card -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card card-gradient-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="card-icon-wrapper">
                        <i class="mdi mdi-account-group display-4"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 font-weight-bold"><?= $totalStudents ?></h2>
                        <p class="mb-0 text-white-50">Students</p>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-2 mb-0 text-white-50"><i class="mdi mdi-account-multiple-plus me-1"></i>Total Enrolled</p>
            </div>
        </div>
    </div>

    <!-- Total Assessments Card -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card card-gradient-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="card-icon-wrapper">
                        <i class="mdi mdi-clipboard-text display-4"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 font-weight-bold"><?= $totalAssessments ?></h2>
                        <p class="mb-0 text-white-50">Assessments</p>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-2 mb-0 text-white-50"><i class="mdi mdi-chart-line-variant me-1"></i>Completed Tests</p>
            </div>
        </div>
    </div>

    <!-- Question Bank Card -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card card-gradient-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="card-icon-wrapper">
                        <i class="mdi mdi-lightbulb-on display-4"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 font-weight-bold"><?= $totalQuestions ?></h2>
                        <p class="mb-0 text-white-50">Questions</p>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-2 mb-0 text-white-50"><i class="mdi mdi-database me-1"></i>Question Bank</p>
            </div>
        </div>
    </div>

    <!-- Average Score Card -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card card-gradient-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="card-icon-wrapper">
                        <i class="mdi mdi-chart-arc display-4"></i>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 font-weight-bold"><?= $averageScore ?>%</h2>
                        <p class="mb-0 text-white-50">Avg Score</p>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-white" role="progressbar" style="width: <?= $averageScore ?>%" aria-valuenow="<?= $averageScore ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-2 mb-0 text-white-50"><i class="mdi mdi-trending-<?= $averageScore >= 75 ? 'up' : 'down' ?> me-1"></i>Class Performance</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Card Styles */
.card-gradient-primary {
    background: linear-gradient(135deg, var(--brand-primary) 0%, var(--vivid-sky) 100%);
    color: white;
    box-shadow: 0 4px 20px 0 rgba(var(--brand-primary-rgba-18), 0.4);
    border: none;
}

.card-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    box-shadow: 0 4px 20px 0 rgba(17, 153, 142, 0.4);
}

.card-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    box-shadow: 0 4px 20px 0 rgba(240, 147, 251, 0.4);
    border: none;
}

.card-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    box-shadow: 0 4px 20px 0 rgba(79, 172, 254, 0.4);
    border: none;
}

.card-icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
}

.card-icon-wrapper i {
    font-size: 2.5rem;
    color: white;
}

.card-gradient-primary .card-body,
.card-gradient-success .card-body,
.card-gradient-warning .card-body,
.card-gradient-info .card-body {
    padding: 1.5rem;
}

.card-gradient-primary:hover,
.card-gradient-success:hover,
.card-gradient-warning:hover,
.card-gradient-info:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}
</style>

<!-- TABLE -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Students' Assessments Summary</h4>
                <p class="text-muted mb-4">
                    <i class="mdi mdi-information-outline me-1"></i>
                    This table groups assessments by student and subject. If a student took the same subject multiple times, 
                    you'll see their latest score, best score, and total attempts.
                </p>
                <table class="table defaultDataTable">
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Name</th>
                            <th>Grade-Section</th>
                            <th>Subject</th>
                            <th>Version</th>
                            <th>Attempts</th>
                            <th>Latest Score</th>
                            <th>Best Score</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($groupedAssessments as $assessment) { 
                            $student = $assessment['student'];
                            $subject = $assessment['subject'];
                            $attempts = $assessment['attempts'];
                            $latestQuiz = $assessment['latest_quiz'];
                            
                            $latestScoreDisplay = $assessment['latest_score_total'] > 0 
                                ? $assessment['latest_score_raw'] . '/' . $assessment['latest_score_total']
                                : 'N/A';
                            
                            $bestScoreDisplay = $assessment['best_score_total'] > 0 
                                ? $assessment['best_score_raw'] . '/' . $assessment['best_score_total']
                                : 'N/A';
                            
                            // Calculate percentage for color coding
                            $latestPercent = $assessment['latest_score_total'] > 0 
                                ? round(($assessment['latest_score_raw'] / $assessment['latest_score_total']) * 100, 1)
                                : 0;
                            $bestPercent = $assessment['best_score_total'] > 0 
                                ? round(($assessment['best_score_raw'] / $assessment['best_score_total']) * 100, 1)
                                : 0;
                            
                            // Color classes based on score
                            $latestScoreClass = $latestPercent >= 75 ? 'text-success fw-bold' : ($latestPercent >= 50 ? 'text-warning fw-bold' : 'text-danger fw-bold');
                            $bestScoreClass = $bestPercent >= 75 ? 'text-success fw-bold' : ($bestPercent >= 50 ? 'text-warning fw-bold' : 'text-danger fw-bold');
                        ?>
                            <tr>
                                <td class="fw-bold"><?= h($student->lrn ?? 'N/A') ?></td>
                                <td><?= h($student->name) ?></td>
                                <td><?= h($student->grade . ' - ' . $student->section) ?></td>
                                <td><?= h($subject->name ?? 'N/A') ?></td>
                                <td><?= isset($latestQuiz->quiz_version) && $latestQuiz->quiz_version ? h($latestQuiz->quiz_version->version_number) : '—' ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= $attempts ?> <?= $attempts > 1 ? 'attempts' : 'attempt' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $latestScoreClass ?>">
                                        <?= $latestScoreDisplay ?>
                                    </span>
                                    <?php if ($latestPercent > 0): ?>
                                        <small class="text-muted">(<?= $latestPercent ?>%)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?= $bestScoreClass ?>">
                                        <?= $bestScoreDisplay ?>
                                    </span>
                                    <?php if ($bestPercent > 0): ?>
                                        <small class="text-muted">(<?= $bestPercent ?>%)</small>
                                    <?php endif; ?>
                                    <?php if ($attempts > 1 && $bestPercent > $latestPercent): ?>
                                        <i class="mdi mdi-trophy text-warning ms-1" title="Best score achieved"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($attempts == 1): ?>
                                        <?= $this->Html->link(
                                            '<i class="mdi mdi-file-document-outline me-1"></i>View',
                                            ['controller' => 'Dashboard', 'action' => 'studentQuiz', 'prefix' => 'Teacher', $this->Encrypt->hex($latestQuiz->id)],
                                            ['escape' => false, 'class' => 'btn btn-sm btn-info text-white']
                                        ) ?>
                                    <?php else: ?>
                                        <?php
                                            // Build a lightweight array of attempts for the modal
                                            $quizItems = [];
                                            // Number attempts correctly: most recent gets highest number
                                            $totalAttempts = count($assessment['all_quizzes']);
                                            $attemptNum = $totalAttempts;
                                            foreach($assessment['all_quizzes'] as $quiz) {
                                                // Prefer numeric fields provided by the entity: studentScore and totalScore
                                                $studentScore = isset($quiz->score['studentScore']) ? (int)$quiz->score['studentScore'] : 0;
                                                $totalScore = isset($quiz->score['totalScore']) ? (int)$quiz->score['totalScore'] : 0;

                                                if ($totalScore > 0) {
                                                    $quizScore = $studentScore . '/' . $totalScore;
                                                    $quizPercent = round(($studentScore / $totalScore) * 100, 1);
                                                } else {
                                                    // If total is unknown, show numerator/0 when numerator exists, otherwise N/A
                                                    $quizScore = $studentScore > 0 ? ($studentScore . '/0') : 'N/A';
                                                    $quizPercent = 0;
                                                }

                                                $isBest = ($assessment['best_score_raw'] == $studentScore && $assessment['best_score_total'] == $totalScore);

                                                // include quiz version info if available
                                                $versionNumber = null;
                                                if (!empty($quiz->quiz_version) && !empty($quiz->quiz_version->version_number)) {
                                                    $versionNumber = (int)$quiz->quiz_version->version_number;
                                                }
                                                $versionSuffix = $versionNumber ? ' - v' . $versionNumber : '';

                                                // extract metadata from quiz_version if available
                                                $snapshotAt = null;
                                                $creator = null;
                                                if (!empty($quiz->quiz_version)) {
                                                    $qv = $quiz->quiz_version;
                                                    // prefer metadata JSON
                                                    if (!empty($qv->metadata)) {
                                                        $metaArr = @json_decode($qv->metadata, true);
                                                        if (is_array($metaArr)) {
                                                            if (!empty($metaArr['snapshot_at'])) $snapshotAt = $metaArr['snapshot_at'];
                                                            if (!empty($metaArr['teacher_id'])) $creator = $metaArr['teacher_id'];
                                                            if (!empty($metaArr['created_by'])) $creator = $metaArr['created_by'];
                                                        }
                                                    }
                                                    if (empty($creator) && !empty($qv->created_by)) $creator = $qv->created_by;
                                                }

                                                // resolve creator name if available
                                                $creatorName = null;
                                                if (!empty($creator) && !empty($creatorNames) && isset($creatorNames[$creator])) {
                                                    $creatorName = $creatorNames[$creator];
                                                }

                                                $quizItems[] = [
                                                    'id' => $this->Encrypt->hex($quiz->id),
                                                    'label' => 'Attempt #' . $attemptNum . ' - ' . $quizScore . ' (' . $quizPercent . '%)' . $versionSuffix,
                                                    'isBest' => $isBest,
                                                    'version' => $versionNumber,
                                                    'snapshot_at' => $snapshotAt,
                                                    'created_by' => $creator,
                                                    'created_by_name' => $creatorName
                                                ];

                                                $attemptNum--;
                                            }
                                            $jsonQuizzes = h(json_encode($quizItems));
                                        ?>

                                        <button class="btn btn-sm btn-info btn-view-all" type="button" data-quizzes='<?= $jsonQuizzes ?>'>
                                            <i class="mdi mdi-file-document-outline me-1"></i>View All
                                        </button>
                                        <!-- Delete all attempts for this student+subject -->
                                        <?php $studentHash = $this->Encrypt->hex($student->id); $subjectHash = $this->Encrypt->hex($subject->id); ?>
                                        <button class="btn btn-sm btn-danger ms-2 deleteAssessmentsBtn" type="button" data-student="<?= h($studentHash) ?>" data-subject="<?= h($subjectHash) ?>">
                                            <i class="mdi mdi-trash-can-outline me-1"></i>Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Ensure Bootstrap dropdowns work properly inside DataTables
(function() {
    function initDashboardDropdowns() {
        // Wait for jQuery and Bootstrap to be available
        if (typeof jQuery === 'undefined' || typeof bootstrap === 'undefined') {
            setTimeout(initDashboardDropdowns, 100);
            return;
        }
        
        var $ = jQuery;
        
        // Re-initialize dropdowns after DataTables pagination/sort
        $('.defaultDataTable').on('draw.dt', function() {
            setupDropdowns();
        });
        
        // Initial dropdown setup
        setupDropdowns();
        
        function setupDropdowns() {
            // Initialize Bootstrap dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            dropdownElementList.forEach(function(dropdownToggleEl) {
                // Dispose existing instance if any
                var existingInstance = bootstrap.Dropdown.getInstance(dropdownToggleEl);
                if (existingInstance) {
                    existingInstance.dispose();
                }
                // Create new instance
                new bootstrap.Dropdown(dropdownToggleEl);
            });
            
            // Prevent DataTable row click when clicking dropdown
            $('.dropdown-toggle, .dropdown-menu').off('click.dropdownstop').on('click.dropdownstop', function(e) {
                e.stopPropagation();
            });
        }
        
        // Handle dropdown item clicks (navigate via AJAX)
        $(document).on('click', '.dropdown-item', function(e) {
            e.stopPropagation();
            var href = $(this).attr('href');
            if (href && typeof loadPage === 'function') {
                e.preventDefault();
                loadPage(href);
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardDropdowns);
    } else {
        initDashboardDropdowns();
    }
})();
</script>

<!-- Attempts Modal (used by View All) -->
<div class="modal fade" id="attemptsModal" tabindex="-1" aria-labelledby="attemptsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attemptsModalLabel">Attempts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="attemptsModalList"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Handler to show attempts modal when View All is clicked
(function() {
    function initAttemptsModal() {
        if (typeof jQuery === 'undefined' || typeof bootstrap === 'undefined') {
            setTimeout(initAttemptsModal, 100);
            return;
        }
        var $ = jQuery;

    $(document).on('click', '.btn-view-all', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var el = this;
            var data = el.getAttribute('data-quizzes') || $(el).attr('data-quizzes');
            console.log('[AttemptsModal] button clicked, raw data attr:', data);
            if (!data) {
                console.warn('[AttemptsModal] no data-quizzes attribute found');
                return;
            }

            var quizzes = null;
            try {
                quizzes = JSON.parse(data);
            } catch (err) {
                // Try to decode HTML entities and parse again
                try {
                    var ta = document.createElement('textarea');
                    ta.innerHTML = data;
                    var decoded = ta.value;
                    console.log('[AttemptsModal] decoded data attr:', decoded);
                    quizzes = JSON.parse(decoded);
                } catch (err2) {
                    console.error('[AttemptsModal] Failed to parse quizzes JSON', err, err2);
                    // Show a user-friendly message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({icon: 'error', title: 'Error', text: 'Unable to show attempts. Please try again.'});
                    } else {
                        alert('Unable to show attempts. See console for details.');
                    }
                    return;
                }
            }

            var $list = $('#attemptsModalList');
            $list.empty();
                quizzes.forEach(function(q){
                var href = (typeof window.APP_BASE !== 'undefined' ? window.APP_BASE : (window.location.origin || (window.location.protocol + '//' + window.location.host) ) + '/') + 'teacher/dashboard/studentQuiz/' + q.id;
                var $li = $('<li class="list-group-item d-flex justify-content-between align-items-start"></li>');
                var $wrap = $('<div></div>');
                var $link = $('<a class="me-3 d-block" href="' + href + '"></a>').text(q.label);
                $link.on('click', function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    try {
                        // Prefer to hide an existing Bootstrap modal instance safely
                        var modalEl = document.getElementById('attemptsModal');
                        if (window.bootstrap && bootstrap.Modal && modalEl) {
                            var inst = bootstrap.Modal.getInstance(modalEl);
                            if (inst && typeof inst.hide === 'function') {
                                inst.hide();
                            } else {
                                // If no instance, ensure backdrop removed as fallback and do a defensive cleanup
                                modalEl.classList.remove('show');
                                document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
                                // also remove DataTables responsive modal elements and restore body state
                                document.querySelectorAll('.dtr-modal-background, .dtr-modal').forEach(function(b){ b.remove(); });
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }
                        } else {
                            // Fallback: remove any native overlay created by our fallback UI
                            var existingOverlay = document.getElementById('simpleAttemptsOverlay');
                            if (existingOverlay && existingOverlay.parentNode) existingOverlay.parentNode.removeChild(existingOverlay);
                        }
                    } catch (e) {
                        console.warn('Could not hide modal before navigation', e);
                    }
                    if (typeof loadPage === 'function') { loadPage(href); } else { window.location.href = href; }
                });
                $wrap.append($link);
                // add metadata small text if available
                    if (q.snapshot_at || q.created_by_name || q.created_by) {
                    var metaText = [];
                    if (q.snapshot_at) {
                        // show only date/time portion
                        var dt = q.snapshot_at;
                        try { dt = new Date(dt).toLocaleString(); } catch(e) { /* leave as-is */ }
                        metaText.push('Snapshot: ' + dt);
                    }
                    if (q.created_by_name) {
                        metaText.push('By ' + q.created_by_name);
                    } else if (q.created_by) {
                        metaText.push('By teacher #' + q.created_by);
                    }
                    $wrap.append('<div class="small text-muted mt-1">' + metaText.join(' • ') + '</div>');
                }

                $li.append($wrap);
                if (q.isBest) {
                    $li.append('<span class="badge bg-warning text-dark">Best</span>');
                }
                $list.append($li);
            });

            var modalEl = document.getElementById('attemptsModal');
            // Cleanup helper to defensively remove any leftover overlays and restore body state
            function _cleanupModalBackdrops() {
                try {
                    // Remove Bootstrap backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
                    // Remove DataTables Responsive modal background if any
                    document.querySelectorAll('.dtr-modal-background, .dtr-modal').forEach(function(b){ b.remove(); });
                    // Ensure modal-open class removed from body and restore scrolling
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                } catch (e) {
                    console.warn('[AttemptsModal] cleanup error', e);
                }
            }

            var modal = new bootstrap.Modal(modalEl);
            // Attach hidden handler to do a defensive cleanup after bootstrap completes hide
            modalEl.addEventListener('hidden.bs.modal', function(){ setTimeout(_cleanupModalBackdrops, 10); }, { once: true });
            modal.show();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAttemptsModal);
    } else {
        initAttemptsModal();
    }
})();
</script>

<!-- Native fallback: ensure clicks on .btn-view-all are handled even if jQuery/bootstrap init hasn't run -->
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.btn-view-all');
    if (!btn) return;
    try {
        console.log('[AttemptsModal][native] btn clicked');
        e.preventDefault();
        e.stopPropagation();
        var data = btn.getAttribute('data-quizzes');
        if (!data) {
            console.warn('[AttemptsModal][native] no data-quizzes');
            return;
        }
        // decode HTML entities if present
        try {
            var quizzes = JSON.parse(data);
        } catch (err) {
            var ta = document.createElement('textarea');
            ta.innerHTML = data;
            var decoded = ta.value;
            try {
                quizzes = JSON.parse(decoded);
            } catch (err2) {
                console.error('[AttemptsModal][native] parse failed', err, err2);
                return;
            }
        }

        // Build list HTML
        var listEl = document.getElementById('attemptsModalList');
        if (listEl) {
            listEl.innerHTML = '';
                quizzes.forEach(function(q){
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-start';
                var left = document.createElement('div');
                left.style.flex = '1 1 auto';
                var a = document.createElement('a');
                a.className = 'me-3 d-block';
                a.href = (typeof window.APP_BASE !== 'undefined' ? window.APP_BASE : (window.location.origin || (window.location.protocol + '//' + window.location.host) ) + '/') + 'teacher/dashboard/studentQuiz/' + q.id;
                a.textContent = q.label;
                a.addEventListener('click', function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    try {
                        // Hide bootstrap modal if available, otherwise remove any native overlay
                        var modalEl = document.getElementById('attemptsModal');
                        if (window.bootstrap && bootstrap.Modal && modalEl) {
                            var inst = bootstrap.Modal.getInstance(modalEl);
                            if (inst && typeof inst.hide === 'function') {
                                    inst.hide();
                                } else {
                                    modalEl.classList.remove('show');
                                    document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
                                    document.querySelectorAll('.dtr-modal-background, .dtr-modal').forEach(function(b){ b.remove(); });
                                    document.body.classList.remove('modal-open');
                                    document.body.style.overflow = '';
                                    document.body.style.paddingRight = '';
                                }
                        } else {
                            var existingOverlay = document.getElementById('simpleAttemptsOverlay');
                            if (existingOverlay && existingOverlay.parentNode) existingOverlay.parentNode.removeChild(existingOverlay);
                        }
                    } catch (ex) {
                        console.warn('[AttemptsModal][native] error hiding modal/overlay before navigate', ex);
                    }
                    if (typeof loadPage === 'function') { loadPage(a.href); } else { window.location.href = a.href; }
                });
                left.appendChild(a);
                // metadata
                if (q.snapshot_at || q.created_by_name || q.created_by) {
                    var metaDiv = document.createElement('div');
                    metaDiv.className = 'small text-muted mt-1';
                    var parts = [];
                    if (q.snapshot_at) {
                        try {
                            var d = new Date(q.snapshot_at);
                            parts.push('Snapshot: ' + d.toLocaleString());
                        } catch(e) { parts.push('Snapshot: ' + q.snapshot_at); }
                    }
                    if (q.created_by_name) parts.push('By ' + q.created_by_name);
                    else if (q.created_by) parts.push('By teacher #' + q.created_by);
                    metaDiv.textContent = parts.join(' • ');
                    left.appendChild(metaDiv);
                }

                li.appendChild(left);
                if (q.isBest) {
                    var span = document.createElement('span');
                    span.className = 'badge bg-warning text-dark';
                    span.textContent = 'Best';
                    li.appendChild(span);
                }
                listEl.appendChild(li);
            });
        }

        // Show modal if bootstrap available
        if (window.bootstrap && bootstrap.Modal) {
            var modalEl = document.getElementById('attemptsModal');
            // defensive cleanup helper
            function _cleanupModalBackdrops_native() {
                try {
                    document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
                    document.querySelectorAll('.dtr-modal-background, .dtr-modal').forEach(function(b){ b.remove(); });
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                } catch (e) { console.warn('[AttemptsModal][native] cleanup error', e); }
            }
            var modal = new bootstrap.Modal(modalEl);
            modalEl.addEventListener('hidden.bs.modal', function(){ setTimeout(_cleanupModalBackdrops_native, 10); }, { once: true });
            modal.show();
            return;
        }

        // If bootstrap is not available, show a simple native modal overlay instead of navigating away
        if (quizzes && quizzes.length) {
            showSimpleModal(quizzes);
            return;
        }
        
        function showSimpleModal(quizzes) {
            // Remove existing overlay if present
            var existing = document.getElementById('simpleAttemptsOverlay');
            if (existing) existing.remove();

            var overlay = document.createElement('div');
            overlay.id = 'simpleAttemptsOverlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.background = 'rgba(0,0,0,0.6)';
            overlay.style.zIndex = '20000';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';

            var box = document.createElement('div');
            box.style.background = '#fff';
            box.style.borderRadius = '6px';
            box.style.width = '720px';
            box.style.maxWidth = '95%';
            box.style.maxHeight = '80%';
            box.style.overflow = 'auto';
            box.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
            box.style.padding = '16px';

            var title = document.createElement('h5');
            title.textContent = 'Attempts';
            title.style.marginTop = '0';
            box.appendChild(title);

            var list = document.createElement('ul');
            list.className = 'list-group';
            list.id = 'simpleAttemptsList';
            quizzes.forEach(function(q){
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                var a = document.createElement('a');
                a.className = 'me-3';
                a.href = (typeof window.APP_BASE !== 'undefined' ? window.APP_BASE : (window.location.origin || (window.location.protocol + '//' + window.location.host) ) + '/') + 'teacher/dashboard/studentQuiz/' + q.id;
                a.textContent = q.label;
                a.addEventListener('click', function(ev){
                    ev.preventDefault(); ev.stopPropagation();
                    try {
                        var existingOverlay = document.getElementById('simpleAttemptsOverlay');
                        if (existingOverlay && existingOverlay.parentNode) existingOverlay.parentNode.removeChild(existingOverlay);
                    } catch (ex) { /* ignore */ }
                    if (typeof loadPage === 'function') { loadPage(a.href); } else { window.location.href = a.href; }
                });
                li.appendChild(a);
                if (q.isBest) {
                    var span = document.createElement('span');
                    span.className = 'badge bg-warning text-dark';
                    span.textContent = 'Best';
                    li.appendChild(span);
                }
                list.appendChild(li);
            });
            box.appendChild(list);

            var footer = document.createElement('div');
            footer.style.textAlign = 'right';
            footer.style.marginTop = '12px';
            var closeBtn = document.createElement('button');
            closeBtn.className = 'btn btn-secondary';
            closeBtn.textContent = 'Close';
            closeBtn.addEventListener('click', function(){ overlay.remove(); });
            footer.appendChild(closeBtn);
            box.appendChild(footer);

            overlay.appendChild(box);
            document.body.appendChild(overlay);
        }
    } catch (ex) {
        console.error('[AttemptsModal][native] error', ex);
    }
});
</script>