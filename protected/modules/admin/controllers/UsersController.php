<?php

class UsersController extends Controller {
    /* View lising page */

    public function actionIndex() {
        $model = new Users("search");
        if (isset($_GET['Users'])) {
            $model->attributes = $_GET['Users'];
        }
        $this->render('index', array("model" => $model));
    }
    public function actionDeliveryboy() {
        $model = new Users("search");
        $model->user_group = 5;
        if (isset($_GET['Users'])) {
            $model->attributes = $_GET['Users'];
        }
        $this->render('index', array("model" => $model));
    }

    /* add user group */

    public function actionAdd() {

        $model = new Users('add');
        // Uncomment the following line if AJAX validation is needed
        $this->performAjaxValidation($model, "form-user");

        if (isset($_POST['Users'])) {
            $model->attributes = $_POST['Users'];
            if ($model->validate()) {
                $model->save();
                $model->profile_pic = $model->uploadProfilePicture($model);
                $model->update();
                Yii::app()->user->setFlash("success", common::translateText("ADD_SUCCESS"));
                $this->redirect(array("/" . Yii::app()->controller->module->id . "/users"));
            }
        }
        $this->render('addUser', array('model' => $model));
    }

    /* update user group */

    public function actionUpdate($id) {
        $model = $this->loadModel($id, "Users");
        $old_profile_pic = $model->profile_pic;
        // Uncomment the following line if AJAX validation is needed
        $this->performAjaxValidation($model, "form-user");
        if (isset($_POST['Users'])) {
            $model->attributes = $_POST['Users'];
            if ($model->validate()) {
                $model->profile_pic = $model->uploadProfilePicture($model);
                $model->profile_pic = !empty($model->profile_pic) ? $model->profile_pic : $old_profile_pic;
                $model->update();
                Yii::app()->user->setFlash("success", common::translateText("UPDATE_SUCCESS"));
                $this->redirect(array("/" . Yii::app()->controller->module->id . "/users"));
            }
        }
        $this->render('updateUser', array('model' => $model));
    }

    /* delete user group */

    public function actionDelete($id) {
        if (Yii::app()->request->isAjaxRequest) {
            $model = $this->loadModel($id, "Users");
            $model->deleted = true;
            if ($model->update()) {
                echo common::getMessage("success", common::translateText("DELETE_SUCCESS"));
            } else {
                echo common::getMessage("danger", common::translateText("DELETE_FAIL"));
            }
            Yii::app()->end();
        } else {
            throw new CHttpException(400, common::translateText("400_ERROR"));
        }
    }

    /* view user profile */

    public function actionProfile() {
        $model = $this->loadModel(Yii::app()->user->id, "Users");
        if (isset($_POST['Users'])) {
            $model->attributes = $_POST['Users'];
            $this->performAjaxValidation($model, "form-profile");
            if ($model->validate()) {
                $model->update(false);
                Yii::app()->user->setFlash("success", "Your profie has been updated successfully.");
                $this->redirect(array("profile"));
            }
        }
        $this->render("profile", array("model" => $model));
    }

    public function actionAddress() {
        $model = new UserAddress();
        $criteria = new CDbCriteria();
        $criteria->condition = "user_id = " . Yii::app()->user->id;
        $modelData = UserAddress::model()->findAll($criteria);
        if (isset($_POST['UserAddress'])) {
            $model->attributes = $_POST['UserAddress'];
            $this->performAjaxValidation($model, "form-profile");
            $model->user_id = Yii::app()->user->id;
            if ($model->is_default) {
                UserAddress::model()->updateAll(array('is_default' => 0));
            }
            if ($model->validate()) {
                $model->save();
                Yii::app()->user->setFlash("success", "Your profie has been updated successfully.");
                $this->redirect(array("address"));
            }
        }
        $this->render("address", array("model" => $model, "data" => $modelData));
    }

    public function actionUpdateaddress($id) {
        $model = UserAddress::model()->findByPk($_REQUEST['id']);
        $criteria = new CDbCriteria();
        $criteria->condition = "user_id = " . Yii::app()->user->id;
        $modelData = UserAddress::model()->findAll($criteria);
        $this->performAjaxValidation($model, 'form-address');
        if (isset($_POST["UserAddress"])) {
            $model->attributes = $_POST["UserAddress"];
            $model->user_id = Yii::app()->user->id;
            if ($model->is_default) {
                UserAddress::model()->updateAll(array('is_default' => 0));
            }
            if ($model->validate()) {
                $model->update();
                Yii::app()->user->setFlash('success', 'You have successfully updated record.');
                $this->redirect(array("users/address"));
            } else {
                echo "<pre>";
                print_r($model->getErrors());
                die;
            }
        }
        $this->render("address", array("model" => $model, "data" => $modelData));
    }

    public function actionDeleteaddress($id) {
        if ($id) {
            $model = UserAddress::model()->findByPk($id);
            $model->is_deleted = 1;
            $model->update();
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('address'));
            echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>You have successfully deleted record.</div>";
            Yii::app()->end();
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }
       public function actionChangePassword() {
        $model = $this->loadModel(Yii::app()->user->id, "Users");
        $model->scenario = "change_password";
        $model->password = "";
        if (isset($_POST['Users'])) {
            $model->attributes = $_POST['Users'];
            $this->performAjaxValidation($model, "form-password");
            if ($model->validate()) {
                $model->salt = $model->generateSalt();
                $model->password = $model->hashPassword($model->password, $model->salt);
                $model->update(false);
                Yii::app()->user->setFlash("success", "Your password has been updated successfully.");
                $this->redirect(array("index"));
            }
        }
        $this->render('change_password', array('model' => $model));
    }

}
