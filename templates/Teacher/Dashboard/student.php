<!-- HEADER WITH BACK BUTTON -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="page-title mb-0">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-circle"></i>
        </span> Student Details
    </h3>
    <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'students', 'prefix' => 'Teacher']) ?>" class="btn btn-gradient-primary btn-rounded">
        <i class="mdi mdi-arrow-left me-1"></i>Back to Students
    </a>
</div>

<!-- TABLE -->
<div class="row">
    <!-- STUDENT DETAILS -->
    <div class="col-xl-5 col-lg-6 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Student Details</h4>
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="fw-bold">Name</td>
                            <td><?= $student->name ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">LRN (Learner Reference Number)</td>
                            <td><?= h($student->lrn) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Grade - Section</td>
                            <td><?= $student->grade_section ?></td>
                        </tr>
                    </tbody>
                </table>
                <p class="fw-bold my-3">Remarks:</p>
                <?= $this->Form->create(NULL) ?>
                    <?= $this->Form->textarea('remarks', ['class' => 'form-control', 'id' => 'remarks', 'value' => $student->remarks, 'disabled' => true]) ?>

                    <div class="row mt-3 px-0">
                        <div class="col-12">
                            <div class="student-actions d-flex flex-wrap gap-2">
                                <a href="https://nonbasic-bob-inimical.ngrok-free.dev/tailored_module_<?= str_replace(' ', '_', $student->name) ?>_<?= $student->lrn ?>.docx" class="btn btn-warning btn-sm btn-icon-label d-inline-flex align-items-center doc-link" data-type="tailored" data-student="<?= $this->Encrypt->hex($student->id) ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="mdi mdi-file-document-box-outline me-2" style="font-size:1.15rem;"></i>
                                    <span class="d-none d-sm-inline">Tailored Module</span>
                                </a>

                                <a href="https://nonbasic-bob-inimical.ngrok-free.dev/analysis_result_<?= str_replace(' ', '_', $student->name) ?>_<?= $student->lrn ?>.docx" class="btn btn-teal btn-sm btn-icon-label d-inline-flex align-items-center doc-link" data-type="analysis" data-student="<?= $this->Encrypt->hex($student->id) ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="mdi mdi-chart-areaspline me-2" style="font-size:1.15rem;"></i>
                                    <span class="d-none d-sm-inline">Analysis</span>
                                </a>

                                <?= $this->Form->button('<i class="mdi mdi-send me-2" style="font-size:1.1rem;"></i> Submit', ['class' => 'btn btn-success btn-sm d-inline-flex align-items-center d-none', 'id' => 'submitRemarksBtn', 'type' => 'submit', 'escapeTitle' => false]) ?>
                            </div>
                        </div>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>   
    <!-- END -->

    <!-- ASSESSMENTS -->
    <div class="col-xl-7 col-lg-6 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Student Quizzes</h4>
                <table class="table defaultDataTable">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Version</th>
                            <th>Created</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($student->student_quiz as $studentQuiz) { ?>
                            <tr>
                                <td><?= $studentQuiz->subject->name ?></td>
                                <td><?= $studentQuiz->score['overallScore'] ?></td>
                                <td><?= isset($studentQuiz->quiz_version) && $studentQuiz->quiz_version ? h($studentQuiz->quiz_version->version_number) : '—' ?></td>
                                <td><?= $studentQuiz->created->format('Y/m/d') ?></td>
                                <td class="text-center">
                                    <?= $this->Html->link('<i class="mdi mdi-eye"></i>', ['controller' => 'Dashboard', 'action' => 'studentQuiz', 'prefix' => 'Teacher', $this->Encrypt->hex($studentQuiz->id)], ['escape' => false, 'class' => 'btn btn-sm btn-info text-white', 'title' => 'View Details']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>