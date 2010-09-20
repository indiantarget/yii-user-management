<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'YumUserController'.
 */
class YumUserLogin extends YumFormModel
{
	public $username;
	public $password;
	public $rememberMe;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		$rules = array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);

		return $rules;
	}

	public function attributeLabels() {
	if(Yii::app()->getModule('user')->loginType == 'LOGIN_BY_USERNAME')
		$username = Yum::t("Username");
	else if(Yii::app()->getModule('user')->loginType == 'LOGIN_BY_EMAIL')
		$username = Yum::t("Email Address");
	else if(Yii::app()->getModule('user')->loginType == 'LOGIN_BY_USERNAME_OR_EMAIL')
		$username = Yum::t("Username or Email");

		return array(
			'username'=>$username,
			'password'=>Yum::t("Password"),
			'rememberMe'=>Yum::t("Remember me next time"),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params) {
		if(!$this->hasErrors())  // we only want to authenticate when no input errors
		{
			$identity=new YumUserIdentity($this->username,$this->password);
			$identity->authenticate();
			switch($identity->errorCode)
			{
				case YumUserIdentity::ERROR_NONE:
					$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
					Yii::app()->user->login($identity,$duration);
					break;
				case YumUserIdentity::ERROR_EMAIL_INVALID:
					$this->addError("username",Yii::t("UserModule.user", "Email is incorrect."));
					break;
				case YumUserIdentity::ERROR_USERNAME_INVALID:
					$this->addError("username",Yii::t("UserModule.user", "Username is incorrect."));
					break;
				case YumUserIdentity::ERROR_STATUS_NOTACTIVE:
					$this->addError("status",Yii::t("UserModule.user", "This account is not activated."));
					break;
				case YumUserIdentity::ERROR_STATUS_BANNED:
					$this->addError("status",Yii::t("UserModule.user", "This account is blocked."));
					break;
				case YumUserIdentity::ERROR_PASSWORD_INVALID:
					$this->addError("password",Yii::t("UserModule.user", "Password is incorrect."));
					break;
			}
		}
	}
}