<?php

class AdminModule extends CWebModule
{
	public function init()
	{
		// import the module-level models and components
		$this->setImport(
			array(
				'admin.models.*',
				'admin.components.*',
			)
		);

		$this->layout = 'main';
	}

	public function beforeControllerAction($controller, $action)
	{
		if (parent::beforeControllerAction($controller, $action)) {
			// this method is called before any module controller action is performed
			// you may place customized code here
			if (!Yii::app()->user->checkAccess("admin")) {
				throw new CHttpException(
					403,
					Yii::t(
						'application',
						'You are not authorized to perform this action.'
					)
				);
			}
			return true;
		} else
			return false;
	}
}
