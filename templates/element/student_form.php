<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Student $student */
?>

<div class="student-form-container">
    <?= $this->Form->create($student, ['id' => 'studentForm', 'novalidate' => true]) ?>

    <div class="row">
        <div class="col-md-6">
            <?= $this->Form->control('lrn', [
                'label' => 'LRN (Learner Reference Number)',
                'class' => 'form-control',
                'maxlength' => 12,
                'pattern' => '\\d{12}',
                'placeholder' => 'e.g. 123456789012'
            ]) ?>
            <div class="invalid-feedback d-none" data-field="lrn"></div>
        </div>
        <div class="col-md-6">
            <?= $this->Form->control('name', ['class' => 'form-control']) ?>
            <div class="invalid-feedback d-none" data-field="name"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $this->Form->control('grade', [
                'class' => 'form-control',
                'type' => 'number',
                'min' => 1,
                'max' => 6,
                'step' => 1
            ]) ?>
            <div class="invalid-feedback d-none" data-field="grade"></div>
        </div>
        <div class="col-md-6">
            <?= $this->Form->control('section', ['class' => 'form-control']) ?>
            <div class="invalid-feedback d-none" data-field="section"></div>
        </div>
    </div>

    <?= $this->Form->control('remarks', ['type' => 'textarea', 'class' => 'form-control']) ?>
    <div class="invalid-feedback d-none" data-field="remarks"></div>

    <div class="mt-3 d-flex gap-2 justify-content-end">
        <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Cancel') ?></button>
    </div>

    <?= $this->Form->end() ?>
</div>
