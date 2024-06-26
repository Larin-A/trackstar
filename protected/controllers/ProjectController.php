<?php

class ProjectController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout = '//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->verificationPermission($id, 'readProject');

		$issueDataProvider = new CActiveDataProvider(
			'Issue',
			array(
				'criteria' => array(
					'condition' => 'project_id=:projectId',
					'params' => array(':projectId' => $this->loadModel($id)->id),
				),
				'pagination' => array(
					'pageSize' => 10,
				),
			)
		);

		Yii::app()->clientScript->registerLinkTag(
			'alternate',
			'application/rss+xml',
			$this->createUrl(
				'comment/feed',
				array('pid' => $this->loadModel($id)->id)
			)
		);

		$this->render(
			'view',
			array(
				'model' => $this->loadModel($id),
				'issueDataProvider' => $issueDataProvider,
			)
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new Project;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Project'])) {
			$model->attributes = $_POST['Project'];
			if ($model->save()) {
				$model->assignUser(Yii::app()->user->getId(), 'owner');
				$this->redirect(array('view', 'id' => $model->id));
			}
		}

		$this->render(
			'create',
			array(
				'model' => $model,
			)
		);
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$this->verificationPermission($id, 'updateProject');

		$model = $this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Project'])) {
			$model->attributes = $_POST['Project'];
			if ($model->save())
				$this->redirect(array('view', 'id' => $model->id));
		}

		$this->render(
			'update',
			array(
				'model' => $model,
			)
		);
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->verificationPermission($id, 'deleteProject');

		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if (!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider = new CActiveDataProvider('Project');

		Yii::app()->clientScript->registerLinkTag(
			'alternate',
			'application/rss+xml',
			$this->createUrl('comment/feed')
		);

		//get the latest system message to display based on the update_time column
		$sysMessage = SysMessage::getLatest();

		if ($sysMessage !== null)
			$message = $sysMessage->message;
		else
			$message = null;


		$this->render(
			'index',
			array(
				'dataProvider' => $dataProvider,
				'sysMessage' => $message,
			)
		);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model = new Project('search');
		$model->unsetAttributes();  // clear any default values
		if (isset($_GET['Project']))
			$model->attributes = $_GET['Project'];

		$this->render(
			'admin',
			array(
				'model' => $model,
			)
		);
	}

	/**
	 * Provides a form so that project administrators can
	 * associate other users to the project
	 */
	public function actionAdduser($id)
	{
		$this->verificationPermission($id, 'createUser');

		$project = $this->loadModel($id);
		$form = new ProjectUserForm;
		// collect user input data
		if (isset($_POST['ProjectUserForm'])) {
			$form->attributes = $_POST['ProjectUserForm'];
			$form->project = $project;
			// validate user input
			if ($form->validate()) {
				if ($form->assign()) {
					Yii::app()->user->setFlash(
						'success',
						$form->username . " has been added to the project."
					);
					//reset the form for another user to be associated if desired
					$form->unsetAttributes();
					$form->clearErrors();
				}
			}
		}
		$form->project = $project;
		$this->render('adduser', array('model' => $form));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Project the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = Project::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Project $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'project-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
