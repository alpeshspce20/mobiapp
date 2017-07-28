<?php

class ApisController extends Controller {

    // Members
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers 
     */
    Const APPLICATION_ID = 'ASCCPE';

    /**
     * Default response format
     * either 'json' or 'xml'
     */
    private $format = 'json';

    /**
     * @return array action filters
     */
    public function filters() {
        return array();
    }

    // Actions
    public function actionList() {
        // Get the respective model instance
        switch ($_GET['model']) {
            case 'Users':
                $models = Users::model()->findAll();
                break;
            case 'Product':
                $models = Product::model()->findAll();
                break;
            case 'Vendor':
                $models = Vendor::model()->findAll();
                break;
            default:
                $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
                Yii::app()->end();
        }
        // Did we get some results?
        if (empty($models)) {
            // No
            $this->_sendResponse(200, sprintf('No items where found for model <b>%s</b>', $_GET['model']));
        } else {
            // Prepare response
            $rows = array();
            foreach ($models as $model)
                $rows[] = $model->attributes;
            // Send the response
            $this->_sendResponse(200, CJSON::encode($rows));
        }
    }

    public function actionView() {
        // Check if id was submitted via GET
        if (!isset($_GET['id']))
            $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing');

        switch ($_GET['model']) {
            // Find respective model    
            case 'posts':
                $model = Post::model()->findByPk($_GET['id']);
                break;
            default:
                $this->_sendResponse(501, sprintf(
                                'Mode <b>view</b> is not implemented for model <b>%s</b>', $_GET['model']));
                Yii::app()->end();
        }
        // Did we find the requested model? If not, raise an error
        if (is_null($model))
            $this->_sendResponse(404, 'No Item found with id ' . $_GET['id']);
        else
            $this->_sendResponse(200, CJSON::encode($model));
    }

    public function actionCreate() {
        switch ($_GET['model']) {
            // Get an instance of the respective model
            case 'Users':
                $model = new Users();
                break;
            case 'Product':
                $model = new Product();
                break;
            case 'Vendor':
                $model = new Vendor();
                break;
            default:
                $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
                Yii::app()->end();
        }
        // Try to assign POST values to attributes
        foreach ($_POST as $var => $value) {
            // Does the model have this attribute? If not raise an error
            if ($model->hasAttribute($var))
                $model->$var = $value;
            else
                $this->_sendResponse(500, sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var, $_GET['model']));
        }
        // Try to save the model
        $model->created_by = 1;
        $model->updated_by = 1;
        if ($model->save()) {
            switch ($_GET['model']) {
                // Get an instance of the respective model
                case 'Users':
                    $model->status = Users::IN_ACTIVE;
                    $model->is_verified = Users::NOT_VERIFIED;
                    if (isset($_FILES['profile_pic'])) {
                        $uploaddir = Yii::app()->params->paths['usersPath'] . $model->id . "/";
                        $uploadfile = $uploaddir . basename($_FILES['profile_pic']['name']);
                        if (!is_dir($uploaddir)) {
                            mkdir($uploaddir);
                        }
                        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadfile)) {
                            $model->profile_pic = $_FILES['profile_pic']['name'];
                            $model->update();
                            $this->_sendResponse(200, CJSON::encode($model));
                        }
                    }
                case 'Product':
                    if (isset($_FILES['photo'])) {
                        $uploaddir = Yii::app()->params->paths['productPath'] . $model->id . "/";
                        $uploadfile = $uploaddir . basename($_FILES['photo']['name']);
                        if (!is_dir($uploaddir)) {
                            mkdir($uploaddir);
                        }
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadfile)) {
                            $model->photo = $_FILES['photo']['name'];
                            $model->update();
                            $this->_sendResponse(200, CJSON::encode($model));
                        }
                    }
                case 'Vendor':
                    if (isset($_FILES['photo'])) {
                        $uploaddir = Yii::app()->params->paths['vendorPath'] . $model->id . "/";
                        $uploadfile = $uploaddir . basename($_FILES['photo']['name']);
                        if (!is_dir($uploaddir)) {
                            mkdir($uploaddir);
                        }
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadfile)) {
                            $model->photo = $_FILES['photo']['name'];
                            $model->update();
                            $this->_sendResponse(200, CJSON::encode($model));
                        }
                    }
            }
            $this->_sendResponse(200, CJSON::encode($model));
        } else {
            // Errors occurred
//                $i = 0;
            $msg = array();
            foreach ($model->errors as $attribute => $attr_errors) {
                $msg[] = $attr_errors;
            }
            $this->_sendResponse(500, json_encode($msg));
        }
    }

    public function actionUpdate() {
        // Parse the PUT parameters. This didn't work: parse_str(file_get_contents('php://input'), $put_vars);
        $json = file_get_contents('php://input'); //$GLOBALS['HTTP_RAW_POST_DATA'] is not preferred: http://www.php.net/manual/en/ini.core.php#ini.always-populate-raw-post-data
        $put_vars = CJSON::decode($json, true);  //true means use associative array

        switch ($_GET['model']) {
            // Find respective model
            case 'posts':
                $model = Post::model()->findByPk($_GET['id']);
                break;
            default:
                $this->_sendResponse(501, sprintf('Error: Mode <b>update</b> is not implemented for model <b>%s</b>', $_GET['model']));
                Yii::app()->end();
        }
        // Did we find the requested model? If not, raise an error
        if ($model === null)
            $this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_GET['id']));

        // Try to assign PUT parameters to attributes
        foreach ($put_vars as $var => $value) {
            // Does model have this attribute? If not, raise an error
            if ($model->hasAttribute($var))
                $model->$var = $value;
            else {
                $this->_sendResponse(500, sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var, $_GET['model']));
            }
        }
        // Try to save the model
        if ($model->save())
            $this->_sendResponse(200, CJSON::encode($model));
        else
        // prepare the error $msg
        // see actionCreate
        // ...
            $this->_sendResponse(500, $msg);
    }

    public function actionDelete() {
        switch ($_GET['model']) {
            // Load the respective model
            case 'posts':
                $model = Post::model()->findByPk($_GET['id']);
                break;
            default:
                $this->_sendResponse(501, sprintf('Error: Mode <b>delete</b> is not implemented for model <b>%s</b>', $_GET['model']));
                Yii::app()->end();
        }
        // Was a model found? If not, raise an error
        if ($model === null)
            $this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_GET['id']));

        // Delete the model
        $num = $model->delete();
        if ($num > 0)
            $this->_sendResponse(200, $num);    //this is the only way to work with backbone
        else
            $this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_GET['id']));
    }

    public function hashPassword($password, $salt) {
        return md5($salt . $password);
    }

    public function generateSalt() {
        return uniqid('', true);
    }

    public function validatePassword($password) {
        return $this->hashPassword($password, $this->salt) === $this->password;
    }

    public function actionLogin() {

        $username = Yii::app()->request->getPost('username');
        $password = Yii::app()->request->getPost('password');
        if (Yii::app()->request->isPostRequest && $password && $username) {
            $model = new Users();
            $model->username = $username;
            $userData1 = $model->search()->getData();

            if (isset($userData1[0]->username)) {
                $mode2 = new Users();
                $mode2->username = $username;
                $mode2->password = md5($userData1[0]->salt . $password);
                $userData2 = $mode2->search()->getData();
                if (isset($userData2[0]->username)) {
                    $access_token = bin2hex(openssl_random_pseudo_bytes(16));
                    $modelSaveToken = Users::model()->findByPk($userData2[0]->id);
                    $modelSaveToken->access_token = $access_token;
                    if ($modelSaveToken->update(false)) {
                        $data = ["success" => 1, "message" => 'Login Success', 'token' => $access_token, 'name' => $modelSaveToken->first_name . ' ' . $modelSaveToken->last_name, "data" => $modelSaveToken->attributes];
                        echo json_encode($data);
                        Yii::app()->end();
                    } else {
                        $data = ["success" => 0, array("message" => 'Login Fail')];
                        echo json_encode($data);
                        Yii::app()->end();
                    }
                } else {
                    $data = ["success" => 0, "message" => 'Invalid Username and Password'];
                    echo json_encode($data);
                    Yii::app()->end();
                }
            } else {
                $data = ["success" => 0, "message" => 'Invalid Username and Password'];
                echo json_encode($data);
                Yii::app()->end();
            }
        } else {
            $data = ["success" => 0, "message" => 'Parameter missing'];
            echo json_encode($data);
            Yii::app()->end();
        }
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        $access_token = Yii::app()->request->getPost('access_token');
        if (Yii::app()->request->isPostRequest && isset($access_token)) {
            $Criteria = new CDbCriteria();
            $Criteria->compare('access_token', $access_token, true);
            $model = Users::model()->find($Criteria);
            if (isset($model->access_token) && $model->access_token == $access_token) {
                $model->access_token = '';
                $model->update(false);
                $data = ["status" => 1, array("message" => 'Logout Sucess..!')];
                echo json_encode($data);
                Yii::app()->end();
            } else {
                $data = ["status" => 0, "message" => 'Invalid access access token / You are not logged in...!'];
                echo json_encode($data);
                Yii::app()->end();
            }
        } else {
            $data = ["status" => 0, "message" => 'Invalid request/You are not logged in...!'];
            echo json_encode($data);
            Yii::app()->end();
        }
    }

    /**
     * Forget password API
     */
    public function actionForgotpassword() {

        $email = Yii::app()->request->getPost('email');
        $required = ["email"];
        $valid = RESTValidator::validate($required, $_POST);
        if (Yii::app()->request->isPostRequest && $valid['status'] == 1) {

            $criteria = new CDbCriteria;
            $criteria->condition = "LOWER(email_id)=:email";
            $criteria->params = array(':email' => $email);
            $user = User::model()->find($criteria);

            if (NULL == $user) {
                $data = ["success" => 0, "message" => 'Invalid email address'];
                echo json_encode($data);
                Yii::app()->end();
            } else {
                $userDetails = $user;
                //generate random code and add in user table for restricting link hit multiple time
                $code = $this->generateCode($userDetails->id);

                //send email process
                $emailTo = $userDetails->email_id;
                // Send email and display relative message to user

                $data = array(
                    'subject' => "Forgot password [" . Yii::app()->name . "]",
                    'username' => $userDetails->first_name . " " . $userDetails->last_name,
                    'email' => $userDetails->email_id,
                    'link' => 'http://localhost/life_inventory_angular/#/resetpassword/' . $code, //Yii::app()->createAbsoluteUrl('/reset/', array('dt' => common::encode5t($code))),
                    'view_file' => 'forgot_password'
                );
                $isMailSend = common::mailSendWeb($data, $viewPath = 'application.admin.views.email');

                if ($isMailSend !== false) {

                    $data = ["success" => 1, "message" => 'Reset password link sent successfully.'];
                    echo json_encode($data);
                    Yii::app()->end();
                } else {
                    $data = ["success" => 0, "message" => 'Error sending email'];
                    echo json_encode($data);
                    Yii::app()->end();
                }
            }
        } else {
            $data = ["success" => 0, "message" => $valid['error']];
            echo json_encode($data);
            Yii::app()->end();
        }
    }

    /**
     * Reset password API
     */
    public function actionResetpassword() {
        $password = Yii::app()->request->getPost('password');
        $hash = Yii::app()->request->getPost('hash');
        $required = ["password", "hash"];
        $valid = RESTValidator::validate($required, $_POST);
        if (Yii::app()->request->isPostRequest && $valid['status'] == 1) {

            $criteria = new CDbCriteria;
            $criteria->condition = "activation_code=:act_code";
            $criteria->params = array(':act_code' => $hash);
            $user_model = User::model();
            $data = $user_model->find($criteria);

            if (null === $data) {
                $data = ["success" => 0, "message" => 'Reset link is invalid'];
                echo json_encode($data);
                Yii::app()->end();
            } else {
                $criteria = new CDbCriteria;
                $criteria->condition = "activation_code=:act_code";
                $criteria->params = array(':act_code' => $hash);
                $user_model = User::model()->find($criteria);
                $user_model->activation_code = "";
                $user_model->password = common::passencrypt($password);
                $stat = $user_model->update();
                if ($stat) {
                    $data = ["success" => 1, "message" => 'Password Reset Successfully. click here to'];
                    echo json_encode($data);
                    Yii::app()->end();
                } else {
                    $data = ["success" => 0, "message" => 'Password Reset failed. Please try again.'];
                    echo json_encode($data);
                    Yii::app()->end();
                }
            }
        } else {
            $data = ["success" => 0, "message" => $valid['error']];
            echo json_encode($data);
            Yii::app()->end();
        }
    }

//    public function actionPayment() {
//        if (!isset(Yii::app()->user->userData)) {
//            $this->redirect(array("/login"));
//        }
//        if (isset(Yii::app()->user->lastPaymentId) || !empty(Yii::app()->user->userData->payment_id)) {
//            Yii::app()->user->setFlash("error", "Your payment has already done. Please login to continue.");
//            $this->redirect(array("/login"));
//        }
//        $this->render('payment');
//    }
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
            //$this->render('error', $error);
                echo $error['message'];
        }
    }

    private function generateCode($user_id, $length = 10) {
        $code = bin2hex(openssl_random_pseudo_bytes($length));
        $model = User::model()->findByPk($user_id);
        $model->activation_code = $code;
        $model->update();
        return $code;
    }

    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
</head>
<body>
    <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
    <p>' . $message . '</p>
    <hr />
    <address>' . $signature . '</address>
</body>
</html>';

            echo $body;
        }
        Yii::app()->end();
    }

    private function _getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

}

?>