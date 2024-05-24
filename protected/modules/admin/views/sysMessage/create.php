<?php
/* @var $this SysMessageController */
/* @var $model SysMessage */

$this->breadcrumbs=array(
	'Sys Messages'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List SysMessage', 'url'=>array('index')),
	array('label'=>'Manage SysMessage', 'url'=>array('admin')),
);
?>

<h1>Create SysMessage</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>