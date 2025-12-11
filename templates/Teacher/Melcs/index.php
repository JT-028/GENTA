<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Most Essential Competencies</h3>
                <p class="text-muted mb-0">These entries are used as training data for the GenAI pipeline.</p>
            </div>
            <div>
                <?php $addUrl = $this->Url->build(['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'add']); ?>
                <?= $this->Html->link('Add MELC', '#', ['class' => 'btn btn-gradient-primary btn-add-melc', 'data-no-ajax' => 'true', 'data-href' => $addUrl]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Export/Import Section -->
<div class="row mb-4">
    <div class="col-lg-6 grid-margin">
        <div class="card h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-wrapper bg-primary-subtle rounded p-2 me-3">
                        <i class="mdi mdi-download text-primary" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Export Data</h5>
                        <p class="text-muted mb-0 small">Download MELCs in various formats</p>
                    </div>
                </div>
                
                <div class="mb-3 p-2 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">Total Records:</span>
                        <span class="badge bg-primary"><?= count($melcs) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Export Formats:</span>
                        <span class="small text-muted">CSV, JSON</span>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <?= $this->Html->link(
                        '<i class="mdi mdi-file-delimited me-1"></i> Export CSV', 
                        ['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'exportCsv'], 
                        [
                            'id' => 'exportCsvBtn',                 // The JavaScript needs this ID!
                            'data-count' => count($melcs),          // The JavaScript reads this number!
                            'class' => 'btn btn-outline-primary btn-sm flex-fill', 
                            'escape' => false
                        ]
                    ) ?>
                    <?= $this->Html->link(
                        '<i class="mdi mdi-code-braces me-1"></i> Export JSON', 
                        ['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'exportJson'], 
                        [
                            'id' => 'exportJsonBtn',       // JavaScript needs this ID
                            'data-count' => count($melcs), // JavaScript checks this number
                            'class' => 'btn btn-outline-secondary btn-sm flex-fill', 
                            'escape' => false,
                            'target' => '_blank'           // Keep this for new tab support
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 grid-margin">
        <div class="card h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="icon-wrapper bg-success-subtle rounded p-2 me-3">
                        <i class="mdi mdi-upload text-success" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Import Data</h5>
                        <p class="text-muted mb-0 small">Restore MELCs from CSV file</p>
                    </div>
                </div>
                <?= $this->Form->create(null, [
                            'url' => ['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'importCsv'],
                            'type' => 'file', // This automatically sets enctype="multipart/form-data"
                            'id' => 'melcImportForm'
                        ]) ?>
                    <div class="file-drop-area" id="melcDropArea" style="border: 2px dashed #dee2e6; border-radius: 8px; padding: 40px 20px; text-align: center; background-color: #fafafa; cursor: pointer; transition: all 0.3s;">
                        <i class="mdi mdi-upload text-muted" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                        <p class="mb-2 text-muted">Drag and drop backup file here or click to browse</p>
                        <input type="file" name="csv_file" id="melcFileInput" accept=".csv" style="display: none;">
                        <button type="button" class="btn btn-success" id="melcChooseFileBtn">
                            Choose File
                        </button>
                        <p class="text-muted small mt-3 mb-0">Supports CSV format</p>
                        <p class="text-success small mt-1 mb-0" id="melcFileNameDisplay" style="display: none;">
                            <i class="mdi mdi-file-check"></i> <span id="melcFileName"></span>
                        </p>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-3" id="melcImportBtn">
                        <i class="mdi mdi-upload me-1"></i> Import CSV
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MELCs Table -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">MELC Records</h4>
                
                <!-- Bulk Actions Bar for MELCs -->
                <div class="bulk-actions-bar-melcs mb-3" style="display: none;">
                    <div class="d-flex flex-wrap align-items-center gap-3 p-3 bg-light rounded border">
                        <span class="selected-count-melcs fw-bold text-primary" style="min-width: 80px;">0 selected</span>
                        <div class="vr d-none d-sm-block"></div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger bulk-delete-melcs">
                                <i class="mdi mdi-delete"></i> Delete
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary bulk-deselect-melcs">
                                <i class="mdi mdi-close"></i> Clear
                            </button>
                        </div>
                        <div class="vr d-none d-sm-block"></div>
                        <div class="d-flex flex-wrap gap-2 ms-sm-auto">
                            <button type="button" class="btn btn-sm btn-success" id="printMelcs">
                                <i class="mdi mdi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table defaultDataTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="no-sort">
                                    <input type="checkbox" class="form-check-input" id="selectAllMelcs">
                                </th>
                                <th>Upload Date</th>
                                <th>Description</th>
                                <th>Subject</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($melcs as $m): ?>
                                <tr data-melc-id="<?= h($this->Encrypt->hex($m->id)) ?>">
                                    <td>
                                        <input type="checkbox" class="form-check-input melc-checkbox" value="<?= h($this->Encrypt->hex($m->id)) ?>">
                                    </td>
                                    <td><?= h($m->upload_date) ?></td>
                                    <td><?= h($m->description) ?></td>
                                    <td><?= h($m->subject->name ?? $m->subject_id) ?></td>
                                    <td class="text-center">
                                        <?php $editUrl = $this->Url->build(['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'edit', $this->Encrypt->hex($m->id)]); ?>
                                        <?= $this->Html->link('Edit', '#', ['class' => 'btn btn-sm btn-outline-primary btn-edit-melc', 'data-no-ajax' => 'true', 'data-href' => $editUrl]) ?>
                                        <button type="button" class="btn btn-sm btn-danger ms-2 btn-delete-melc-single" 
                                            data-id="<?= h($this->Encrypt->hex($m->id)) ?>"
                                            data-url="<?= $this->Url->build(['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'delete', $this->Encrypt->hex($m->id)]) ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->start('script'); ?>
<script>
(function() {
    // --- 0. CHECK FOR IMPORT SUCCESS ---
    if (localStorage.getItem('genta_is_importing') === '1') {
        localStorage.removeItem('genta_is_importing');
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Import Successful!',
                text: 'Your MELC records have been added.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    }

    // --- 0.5 NEW: PRE-EMPTIVELY DISABLE BOTH EXPORT LINKS IF EMPTY ---
    // This runs immediately to kill the links visually if count is 0
    ['exportCsvBtn', 'exportJsonBtn'].forEach(function(btnId) {
        var btn = document.getElementById(btnId);
        if (btn) {
            var count = btn.getAttribute('data-count');
            if (count && parseInt(count) === 0) {
                btn.setAttribute('href', 'javascript:void(0);');
                btn.removeAttribute('target'); // Stop new tab behavior
                btn.style.opacity = '0.7'; 
                btn.style.cursor = 'not-allowed';
            }
        }
    });

    // --- 1. Bulletproof "Choose File" Button Click ---
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'melcChooseFileBtn') {
            e.preventDefault();
            var fileInput = document.getElementById('melcFileInput');
            if (fileInput) {
                fileInput.value = ''; 
                fileInput.click();    
            }
        }
        
        var dropArea = e.target.closest('#melcDropArea');
        if (dropArea && e.target.id !== 'melcChooseFileBtn' && e.target.type !== 'file') {
            var fileInput = document.getElementById('melcFileInput');
            if (fileInput) fileInput.click();
        }
    });

    // --- 2. Handle File Selection ---
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'melcFileInput') {
            if (e.target.files && e.target.files.length > 0) {
                var fileName = document.getElementById('melcFileName');
                var fileNameDisplay = document.getElementById('melcFileNameDisplay');
                if (fileName && fileNameDisplay) {
                    fileName.textContent = e.target.files[0].name;
                    fileNameDisplay.style.display = 'block';
                }
            }
        }
    });

    // --- 3. Drag and Drop Logic ---
    var events = ['dragenter', 'dragover', 'dragleave', 'drop'];
    events.forEach(function(eventName) {
        document.addEventListener(eventName, function(e) {
            var dropArea = document.getElementById('melcDropArea');
            if (!dropArea) return;

            if (eventName === 'drop' || eventName === 'dragenter' || eventName === 'dragover') {
                e.preventDefault();
                e.stopPropagation();
            }
            
            if (e.target.closest && e.target.closest('#melcDropArea')) {
                if (eventName === 'dragenter' || eventName === 'dragover') {
                    dropArea.style.borderColor = '#198754';
                    dropArea.style.backgroundColor = '#f0fdf4';
                }
            } else {
                 if (eventName === 'dragenter') {
                    dropArea.style.borderColor = '#dee2e6';
                    dropArea.style.backgroundColor = '#fafafa';
                 }
            }
            
            if (eventName === 'drop' && e.target.closest('#melcDropArea')) {
                dropArea.style.borderColor = '#dee2e6';
                dropArea.style.backgroundColor = '#fafafa';
                
                var dt = e.dataTransfer;
                var files = dt.files;
                if (files && files.length > 0) {
                    var fileInput = document.getElementById('melcFileInput');
                    fileInput.files = files;
                    var event = new Event('change', { bubbles: true });
                    fileInput.dispatchEvent(event);
                }
            }
        });
    });

    // --- 4. Import Validation & Loading Indicator ---
    var importForm = document.getElementById('melcImportForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            var fileInput = document.getElementById('melcFileInput');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                e.preventDefault(); 
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Please choose a file first!' });
                } else {
                    alert('Please choose a file first!');
                }
                return;
            }
            localStorage.setItem('genta_is_importing', '1');
            if (window.Swal) {
                Swal.fire({
                    title: 'Importing Data...',
                    html: 'Please wait while we process your CSV file.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            }
        });
    }

    // --- 5. Export Validation (CSV AND JSON) ---
    document.addEventListener('click', function(e) {
        // Check if the clicked element is EITHER export button
        var exportBtn = e.target.closest('#exportCsvBtn, #exportJsonBtn');
        
        if (exportBtn) {
            var count = exportBtn.getAttribute('data-count');
            var isCSV = exportBtn.id === 'exportCsvBtn';
            if (parseInt(count) === 0) {
                // Completely stop the new tab or download
                e.preventDefault(); 
                e.stopPropagation(); 
                
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No MELCs to Export',
                        text: isCSV ? 'There are no MELC records to export as CSV.' : 'There are no MELC records to export as JSON.'
                    });
                } else {
                    alert('There are no MELC records to export.');
                }
            }
        }
    });
    
    // --- 6. Bulk Actions ---
    window.initBulkActionsMelcs = function() {
        if (!window.jQuery) return;
        var $ = window.jQuery;

        $('#selectAllMelcs').prop('checked', false);
        $('.melc-checkbox').prop('checked', false);
        $('.bulk-actions-bar-melcs').hide();

        function updateBulkBar() {
            var count = $('.melc-checkbox:checked').length;
            if (count > 0) {
                $('.bulk-actions-bar-melcs').fadeIn();
                $('.selected-count-melcs').text(count + ' selected');
            } else {
                $('.bulk-actions-bar-melcs').fadeOut();
            }
        }

        $(document).off('change', '#selectAllMelcs').on('change', '#selectAllMelcs', function() {
            $('.melc-checkbox').prop('checked', $(this).prop('checked'));
            updateBulkBar();
        });

        $(document).off('change', '.melc-checkbox').on('change', '.melc-checkbox', function() {
            var all = $('.melc-checkbox').length;
            var checked = $('.melc-checkbox:checked').length;
            $('#selectAllMelcs').prop('checked', all > 0 && all === checked);
            updateBulkBar();
        });

        $(document).off('click', '.bulk-delete-melcs').on('click', '.bulk-delete-melcs', function() {
            var selectedIds = $('.melc-checkbox:checked').map(function() { return $(this).val(); }).get();
            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete " + selectedIds.length + " MELC records.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        html: 'Removing records from database.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    setTimeout(function() {
                        var csrf = $('meta[name=csrfToken]').attr('content') || '';
                        var deletePromises = selectedIds.map(function(id) {
                            var deleteUrl = '<?= $this->Url->build(['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'delete', '__ID__']) ?>'.replace('__ID__', id);
                            return $.ajax({
                                url: deleteUrl,
                                method: 'POST',
                                headers: { 'X-CSRF-Token': csrf },
                                dataType: 'json'
                            });
                        });
                        Promise.all(deletePromises).then(function() {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Your files have been deleted.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => { window.location.reload(); });
                        }).catch(function() {
                            Swal.fire('Error', 'Something went wrong deleting the records.', 'error');
                        });
                    }, 500); 
                }
            });
        });
    };

    if (window.initBulkActionsMelcs) window.initBulkActionsMelcs();

    // --- 7. Force DataTables to Restart ---
    if (window.jQuery) {
        var $ = window.jQuery;
        var table = $('.defaultDataTable');
        if (table.length > 0 && !$.fn.DataTable.isDataTable(table)) {
            table.DataTable({
                "pageLength": 10,
                "columnDefs": [ { "orderable": false, "targets": 0 } ],
                "language": { "search": "Search:", "lengthMenu": "Show _MENU_ entries" }
            });
        }
    }
})();
</script>
<?php $this->end(); ?>

<!-- Modal placeholder for Add/Edit MELC -->
<div class="modal fade" id="melcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">MELC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>
