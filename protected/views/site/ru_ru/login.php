<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle = Yii::app()->name . ' - Вход';
$this->breadcrumbs = array(
    'Вход',
);
?>

<h1>Вход</h1>

<p>Заполните следующую форму, указав свои учетные данные:</p>

<div class="form">
    <?php $form = $this->beginWidget(
        'CActiveForm',
        array(
            'id' => 'login-form',
            'enableClientValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            ),
        )
    ); ?>

    <p class="note">Поля с <span class="required">*</span> являются обязательными.</p>

    <div class="row">
        <?php echo $form->labelEx($model, 'username'); ?>
        <?php echo $form->textField($model, 'username'); ?>
        <?php echo $form->error($model, 'username'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'password'); ?>
        <?php echo $form->passwordField($model, 'password'); ?>
        <?php echo $form->error($model, 'password'); ?>
    </div>

    <div class="row rememberMe">
        <?php echo $form->checkBox($model, 'rememberMe'); ?>
        <?php echo $form->label($model, 'rememberMe'); ?>
        <?php echo $form->error($model, 'rememberMe'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton('Вход'); ?>
    </div>

    <a href="/register">Регистрация</a>

    <?php $this->endWidget(); ?>
</div><!-- form -->