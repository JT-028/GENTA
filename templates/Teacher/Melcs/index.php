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
                <div class="table-responsive">
                    <table class="table defaultDataTable">
                        <thead>
                            <tr>
                                <th>Upload Date</th>
                                <th>Description</th>
                                <th>Subject</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($melcs as $m): ?>
                                <tr>
                                    <td><?= h($m->upload_date) ?></td>
                                    <td><?= h($m->description) ?></td>
                                    <td><?= h($m->subject->name ?? $m->subject_id) ?></td>
                                    <td class="text-center">
                                        <?php $editUrl = $this->Url->build(['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'edit', $this->Encrypt->hex($m->id)]); ?>
                                        <?= $this->Html->link('Edit', '#', ['class' => 'btn btn-sm btn-outline-primary btn-edit-melc', 'data-no-ajax' => 'true', 'data-href' => $editUrl]) ?>
                                        <?= $this->Form->postLink('Delete', ['prefix' => 'Teacher', 'controller' => 'Melcs', 'action' => 'delete', $this->Encrypt->hex($m->id)], ['confirm' => 'Delete this MELC?', 'class' => 'btn btn-sm btn-danger ms-2']) ?>
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
