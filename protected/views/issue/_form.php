<?php
/* @var $this IssueController */
/* @var $model Issue */
/* @var $form CActiveForm */
?>

<div class="form">

	<?php $form = $this->beginWidget('CActiveForm', array(
		'id' => 'issue-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation' => false,
	)
	); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model, 'name'); ?>
		<?php echo $form->textField($model, 'name', array('size' => 60, 'maxlength' => 255)); ?>
		<?php echo $form->error($model, 'name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'description'); ?>
		<?php echo $form->textArea($model, 'description', array('rows' => 6, 'cols' => 50)); ?>
		<?php echo $form->error($model, 'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'type_id'); ?>
		<?php echo $form->dropDownList($model, 'type_id', $model->getTypeOptions()); ?>
		<?php echo $form->error($model, 'type_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'status_id'); ?>
		<?php echo $form->dropDownList($model, 'status_id', $model->getStatusOptions()); ?>
		<?php echo $form->error($model, 'status_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'owner_id'); ?>
		<?php echo $form->dropDownList($model, 'owner_id', $model->project->getUserOptions()); ?>
		<?php echo $form->error($model, 'owner_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'requester_id'); ?>
		<?php echo $form->dropDownList($model, 'requester_id', $model->project->getUserOptions()); ?>
		<?php echo $form->error($model, 'requester_id'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

	<?php $this->endWidget(); ?>

</div><!-- form -->