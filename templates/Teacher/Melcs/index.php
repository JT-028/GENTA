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
                    <?= $this->Html->link('<i class="mdi mdi-file-delimited me-1"></i> Export CSV', ['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'exportCsv'], ['class' => 'btn btn-outline-primary btn-sm flex-fill', 'escape' => false]) ?>
                    <?= $this->Html->link('<i class="mdi mdi-code-braces me-1"></i> Export JSON', ['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'exportJson'], ['class' => 'btn btn-outline-secondary btn-sm flex-fill', 'escape' => false]) ?>
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
                <form action="<?= $this->Url->build(['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'importCsv']) ?>" method="post" enctype="multipart/form-data" id="melcImportForm">
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
(function(){
    // Drag and drop functionality for MELC CSV import
    var dropArea = document.getElementById('melcDropArea');
    var fileInput = document.getElementById('melcFileInput');
    var chooseBtn = document.getElementById('melcChooseFileBtn');
    var fileNameDisplay = document.getElementById('melcFileNameDisplay');
    var fileName = document.getElementById('melcFileName');

    if (!dropArea || !fileInput || !chooseBtn) return;

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
        dropArea.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
        document.body.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropArea.addEventListener(eventName, function() {
            dropArea.style.borderColor = '#198754';
            dropArea.style.backgroundColor = '#f0fdf4';
        }, false);
    });

    ['dragleave', 'drop'].forEach(function(eventName) {
        dropArea.addEventListener(eventName, function() {
            dropArea.style.borderColor = '#dee2e6';
            dropArea.style.backgroundColor = '#fafafa';
        }, false);
    });

    // Handle dropped files
    dropArea.addEventListener('drop', function(e) {
        var dt = e.dataTransfer;
        var files = dt.files;
        if (files && files.length > 0) {
            fileInput.files = files;
            updateFileName(files[0].name);
        }
    });

    // Handle click on drop area
    dropArea.addEventListener('click', function(e) {
        if (e.target.id !== 'melcChooseFileBtn') {
            fileInput.click();
        }
    });

    // Handle choose file button
    chooseBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.click();
    });

    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            updateFileName(this.files[0].name);
        }
    });

    function updateFileName(name) {
        if (fileName && fileNameDisplay) {
            fileName.textContent = name;
            fileNameDisplay.style.display = 'block';
        }
    }

    // Bulk Actions Functionality for MELCs
    window.initBulkActionsMelcs = function() {
        if (!window.jQuery) {
            return;
        }
        var $ = window.jQuery;
        // Only run on MELCs page
        if (!$('.melc-checkbox').length && !$('#selectAllMelcs').length) {
            return;
        }
        console.info('[MELCs] initBulkActionsMelcs called');            function tableApi() {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('.defaultDataTable')) {
                    return $('.defaultDataTable').DataTable();
                }
                return null;
            }

            function visibleCheckboxes() {
                var dt = tableApi();
                if (dt) {
                    return $(dt.rows({ page: 'current' }).nodes()).find('.melc-checkbox');
                }
                return $('.melc-checkbox');
            }

            function updateBulkActionsBarMelcs() {
                var selectedCount = $('.melc-checkbox:checked').length;
                if (selectedCount > 0) {
                    $('.bulk-actions-bar-melcs').show();
                    $('.selected-count-melcs').text(selectedCount + ' selected');
                } else {
                    $('.bulk-actions-bar-melcs').hide();
                }
            }

        // Prevent DataTables sort when toggling checkboxes
        $(document).off('click.bulkactionsstop', '#selectAllMelcs, .melc-checkbox').on('click.bulkactionsstop', '#selectAllMelcs, .melc-checkbox', function(e) {
            e.stopPropagation();
        });

        // Select All checkbox for MELCs - use event delegation
        $(document).off('change.bulkactions', '#selectAllMelcs').on('change.bulkactions', '#selectAllMelcs', function() {
            var isChecked = $(this).prop('checked');
            visibleCheckboxes().prop('checked', isChecked);
            updateBulkActionsBarMelcs();
        });

        // Individual checkbox for MELCs
        $(document).off('change.bulkactions', '.melc-checkbox').on('change.bulkactions', '.melc-checkbox', function() {
            var $visible = visibleCheckboxes();
            var totalCheckboxes = $visible.length;
            var checkedCheckboxes = $visible.filter(':checked').length;
            $('#selectAllMelcs').prop('checked', totalCheckboxes === checkedCheckboxes);
            updateBulkActionsBarMelcs();
        });

        // Clear selection for MELCs - use event delegation
        $(document).off('click.bulkactions', '.bulk-deselect-melcs').on('click.bulkactions', '.bulk-deselect-melcs', function() {
            $('.melc-checkbox, #selectAllMelcs').prop('checked', false);
            updateBulkActionsBarMelcs();
        });

        function ensureDataTableSync(attempts) {
            attempts = attempts || 0;
            var dtSync = tableApi();
            if (dtSync) {
                dtSync.off('draw.bulkactions').on('draw.bulkactions', function() {
                    var $visible = visibleCheckboxes();
                    var totalCheckboxes = $visible.length;
                    var checkedCheckboxes = $visible.filter(':checked').length;
                    $('#selectAllMelcs').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
                });
                var $visible = visibleCheckboxes();
                var totalCheckboxes = $visible.length;
                var checkedCheckboxes = $visible.filter(':checked').length;
                $('#selectAllMelcs').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
                updateBulkActionsBarMelcs();
                return;
            }
            if (attempts < 20) {
                setTimeout(function(){ ensureDataTableSync(attempts+1); }, 100);
            }
        }

        ensureDataTableSync();

        // Re-sync after AJAX page loads
        $(document).off('genta:page-ready.melcs').on('genta:page-ready.melcs', function(){
            ensureDataTableSync(0);
        });

        // Bulk Delete MELCs
        $('.bulk-delete-melcs').on('click', function() {
            var selectedIds = $('.melc-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            var confirmText = 'Are you sure you want to delete ' + selectedIds.length + ' MELC(s)?';
            
            if (window.Swal && typeof Swal.fire === 'function') {
                Swal.fire({
                    title: 'Delete MELCs?',
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete them',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        performBulkDeleteMelcs(selectedIds);
                    }
                });
            } else {
                if (confirm(confirmText)) {
                    performBulkDeleteMelcs(selectedIds);
                }
            }
        });

        function performBulkDeleteMelcs(selectedIds) {
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

            Promise.all(deletePromises).then(function(responses) {
                var successCount = responses.filter(function(r) { return r && r.success; }).length;
                
                if (window.Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        title: 'Deleted!',
                        text: successCount + ' MELC(s) have been deleted.',
                        icon: 'success'
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    alert(successCount + ' MELC(s) have been deleted.');
                    window.location.reload();
                }
            }).catch(function(error) {
                console.error('Bulk delete error:', error);
                if (window.Swal && typeof Swal.fire === 'function') {
                    Swal.fire('Error', 'Failed to delete some MELCs.', 'error');
                } else {
                    alert('Failed to delete some MELCs.');
                }
            });
        }
    };

    // Run on initial page load
    if (window.jQuery) {
        window.initBulkActionsMelcs();
    } else {
        setTimeout(function() { if (window.jQuery) window.initBulkActionsMelcs(); }, 200);
    }

    // Print/Export functions (outside init, triggered by delegated handlers)
    if (window.jQuery) {
        var $ = window.jQuery;
        
        // Print Functionality for MELCs - use event delegation
        $(document).on('click', '#printMelcs', function() {
            var printContent = generateMelcsPrintContent();
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 250);
        });

        // Export CSV - use event delegation
        $(document).on('click', '#exportMelcsCSV', function() {
            exportMelcsToCSV();
        });

        // Export Excel - use event delegation
        $(document).on('click', '#exportMelcsExcel', function() {
            exportMelcsToExcel();
        });

        function getMelcsData() {
            var data = [];
            var checkedOnly = $('.melc-checkbox:checked').length > 0;
            var selector = checkedOnly ? '.melc-checkbox:checked' : '.melc-checkbox';
            
            $(selector).each(function() {
                var $row = $(this).closest('tr');
                data.push({
                    uploadDate: $row.find('td:eq(1)').text().trim(),
                    description: $row.find('td:eq(2)').text().trim(),
                    subject: $row.find('td:eq(3)').text().trim()
                });
            });
            return data;
        }

        function exportMelcsToCSV() {
            var data = getMelcsData();
            var csv = 'Upload Date,Description,Subject\n';
            data.forEach(function(row) {
                csv += '"' + row.uploadDate.replace(/"/g, '""') + '","' + 
                       row.description.replace(/"/g, '""') + '","' + 
                       row.subject.replace(/"/g, '""') + '"\n';
            });
            
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'melcs_report_' + new Date().getTime() + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportMelcsToExcel() {
            var data = getMelcsData();
            var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            html += '<head><meta charset="utf-8"><style>table { border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 8px; } th { background-color: #4B49AC; color: white; }</style></head>';
            html += '<body><h2>MELCs Report</h2><p>Generated: ' + new Date().toLocaleString() + '</p>';
            html += '<table><thead><tr><th>Upload Date</th><th>Description</th><th>Subject</th></tr></thead><tbody>';
            data.forEach(function(row) {
                html += '<tr><td>' + escapeHtml(row.uploadDate) + '</td><td>' + escapeHtml(row.description) + '</td><td>' + escapeHtml(row.subject) + '</td></tr>';
            });
            html += '</tbody></table></body></html>';
            
            var blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'melcs_report_' + new Date().getTime() + '.xls');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function escapeHtml(str) {
            return String(str === undefined || str === null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function generateMelcsPrintContent() {
            var today = new Date().toLocaleDateString();
            var rows = '';
            var checkedOnly = $('.melc-checkbox:checked').length > 0;
            
            var selector = checkedOnly ? '.melc-checkbox:checked' : '.melc-checkbox';
            $(selector).each(function() {
                var $row = $(this).closest('tr');
                var uploadDate = $row.find('td:eq(1)').text();
                var description = $row.find('td:eq(2)').text();
                var subject = $row.find('td:eq(3)').text();
                rows += '<tr><td>' + escapeHtml(uploadDate) + '</td><td>' + escapeHtml(description) + '</td><td>' + escapeHtml(subject) + '</td></tr>';
            });

            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>MELCs Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1 { text-align: center; color: #333; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .date { text-align: right; margin-bottom: 10px; font-size: 12px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th { background-color: #4B49AC; color: white; padding: 10px; text-align: left; border: 1px solid #ddd; }
                        td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        .footer { margin-top: 30px; font-size: 12px; text-align: center; color: #666; }
                        @media print {
                            body { margin: 0; }
                            button { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Most Essential Learning Competencies Report</h1>
                        <p>GENTA Learning Management System</p>
                    </div>
                    <div class="date">Generated: ${today}</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Upload Date</th>
                                <th>Description</th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
                    <div class="footer">
                        <p>© ${new Date().getFullYear()} GENTA - Department of Education</p>
                    </div>
                </body>
                </html>
            `;
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
