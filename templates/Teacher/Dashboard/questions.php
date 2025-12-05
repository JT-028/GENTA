<!-- TABLE -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <h4 class="card-title">Quiz</h4>
                    </div>

                    <div class="d-flex justify-content-end col-sm-8">
                        <a href="#" class="btn btn-success btn-fw mb-3 btn-add-question" data-no-ajax="true">
                            <i class="mdi mdi-plus"></i> Add Question
                        </a>
                    </div>

                    <div class="col-xl-4 col-lg-6 col-sm-8">
                        <?= $this->Form->create(NULL, ['url' => ['controller' => 'Dashboard', 'action' => 'questions', 'prefix' => 'Teacher'], 'method' => 'get', 'id' => 'questionsSubjectForm']) ?>
                            <?= $this->Form->select('questionsSubject', $subjectOptions, ['class' => 'form-select mb-3', 'id' => 'questionsSubject', 'value' => $quesSubjectSel]) ?>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
                <table class="table defaultDataTable">
                    <thead>
                        <tr>
                            <th width="14%">Subject</th>
                            <th width="32%">Question</th>
                            <th width="18%">Choices</th>
                            <th width="12%">Answer</th>
                            <th width="8%">Score</th>
                            <th width="8%">Status</th>
                            <th width="8%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($questions as $question) { ?>
                            <tr>
                                <td class="fw-bold"><?= $question->subject->name ?></td>
                                <td class="text-wrap"><?= $question->description ?></td>
                                <td class="text-center"><?= $question->choices_string ?></td>
                                <td class="text-center"><?= $question->answer ?></td>
                                <td class="text-center"><?= $question->score ?></td>
                                <td class="text-center">
                                    <?php // Status badge: 1=Active, 2=Suspended ?>
                                    <?php if ($question->status == \App\Model\Table\QuestionsTable::STATUS_ACTIVE): ?>
                                        <span class="badge bg-success status-badge">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary status-badge">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= $this->Html->link('<i class="mdi mdi-lead-pencil mx-2"></i>', ['controller' => 'Dashboard', 'action' => 'createEditQuestion', 'prefix' => 'Teacher', $this->Encrypt->hex($question->id)], ['escape' => false, 'class' => 'btn-edit-question']) ?>

                                    <a href="#" class="toggleQuestionStatusBtn" data-question-id="<?= h($this->Encrypt->hex($question->id)) ?>" title="Toggle status"><i class="mdi mdi-power-plug mx-2"></i></a>

                                    <a href="#" class="deleteQuestionBtn" data-question-id="<?= h($this->Encrypt->hex($question->id)) ?>"><i class="mdi mdi-close mx-2"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->element('modal/confirm_delete_question') ?>