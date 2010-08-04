<?php

class YumInstallController extends YumController
{
	public $layout = 'install';
	public $defaultAction='install';
	
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
					'index, start, installer, installation, install, index'),
				'users'=>array('*')),
		);
	}

	public function actionStart()
	{
		$this->actionInstall();
	}

	public function actionInstaller()
	{
		$this->actionInstall();
	}

	public function actionInstallation()
	{
		$this->actionInstall();
	}

	public function actionInstall() 
	{
		if($this->module->debug === true) {
			if(Yii::app()->request->isPostRequest) {
				if($db = Yii::app()->db) {
					$transaction = $db->beginTransaction();

					$usersTable = $_POST['usersTable'];
					$profileFieldsTable = $_POST['profileFieldsTable'];
					$profileFieldsGroupTable = $_POST['profileFieldsGroupTable'];
					$profileTable = $_POST['profileTable'];
					$messagesTable = $_POST['messagesTable'];
					$rolesTable = $_POST['rolesTable'];
					$userRoleTable = $_POST['userRoleTable'];
					$userUserTable = $_POST['userUserTable'];
					$settingsTable = $_POST['settingsTable'];
					$textSettingsTable = $_POST['textSettingsTable'];

					// Clean up existing Installation table-by-table
					$db->createCommand(sprintf('drop table if exists %s',
								$usersTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$profileFieldsTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$profileFieldsGroupTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$profileTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$messagesTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$rolesTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$userRoleTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$userUserTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$settingsTable))->execute();
					$db->createCommand(sprintf('drop table if exists %s',
								$textSettingsTable))->execute();

					// Create User Table
					$sql = "CREATE TABLE IF NOT EXISTS `" . $usersTable . "` (
						`id` int unsigned NOT NULL auto_increment,
						`username` varchar(20) NOT NULL,
						`password` varchar(128) NOT NULL,
						`activationKey` varchar(128) NOT NULL default '',
						`createtime` int(10) NOT NULL default '0',
						`lastvisit` int(10) NOT NULL default '0',
						`superuser` int(1) NOT NULL default '0',
						`status` int(1) NOT NULL default '0',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `username` (`username`),
						KEY `status` (`status`),
						KEY `superuser` (`superuser`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
					$db->createCommand($sql)->execute();

					// Create settings table
					$sql = "CREATE TABLE IF NOT EXISTS `" . $settingsTable . "` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`title` varchar(255) NOT NULL,
						`is_active` tinyint(1) NOT NULL DEFAULT '0',
						`preserveProfiles` tinyint(1) NOT NULL DEFAULT '1',
						`enableRegistration` tinyint(1) NOT NULL DEFAULT '1',
						`enableRecovery` tinyint(1) NOT NULL DEFAULT '1',
						`enableEmailActivation` tinyint(1) NOT NULL DEFAULT '1',
						`enableProfileHistory` tinyint(1) NOT NULL DEFAULT '1',
						`readOnlyProfiles` tinyint(1) NOT NULL DEFAULT '0',
						`loginType` enum('LOGIN_BY_USERNAME','LOGIN_BY_EMAIL','LOGIN_BY_USERNAME_OR_EMAIL') NOT NULL,
						`enableCaptcha` tinyint(1) NOT NULL DEFAULT '1',
						PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
					$db->createCommand($sql)->execute();

					$sql = "INSERT INTO `".$settingsTable."` (`id`, `title`, `is_active`, `preserveProfiles`, `enableRegistration`, `enableRecovery`, `enableEmailActivation`, `enableProfileHistory`, `readOnlyProfiles`, `loginType`, `enableCaptcha`) VALUES ('1', 'Yum factory Default', '1', '1', '1', '1', '1', '1', '0', 'LOGIN_BY_USERNAME_OR_EMAIL', '1');";
					$db->createCommand($sql)->execute();

					// Create Text settings table
					$sql = "CREATE TABLE IF NOT EXISTS `" . $textSettingsTable . "` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`language` enum('en','de','fr','pl','ru') NOT NULL DEFAULT 'en',
						`text_registration_header` text NOT NULL,
						`text_registration_footer` text NOT NULL,
						`text_login_header` text NOT NULL,
						`text_login_footer` text NOT NULL,
						`text_email_registration` text NOT NULL,
						`text_email_recovery` text NOT NULL,
						`text_email_activation` text NOT NULL,
						PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
					$db->createCommand($sql)->execute();

					$sql = "
						INSERT INTO `testdrive`.`yumtextsettings` (`id`, `language`, `text_registration_header`, `text_registration_footer`, `text_login_header`, `text_login_footer`, `text_email_registration`, `text_email_recovery`, `text_email_activation`) VALUES ('1', 'en', 'Welcome at the registration System', 'When registering at this System, you automatically accept our terms.', 'Welcome!', '', 'Thank you for your registration. Please check your email or login.', 'You have requested a new Password. To set your new Password, please go to {activation_url}', 'Your account has been activated. Thank you for your registration.'), ('2', 'de', 'Willkommen zum System.', 'Mit der Anmeldung bestätigen Sie unsere allgemeinen Bedingungen.', 'Willkommen!', '', 'Sie haben sich für unsere Appliation registriert.', 'Sie haben ein neues Passwort angefordert. Bitte klicken Sie diesen link: {activation_url}', 'Ihr Konto wurde freigeschaltet.');
					";

					$db->createCommand($sql)->execute();

					if(isset($_POST['installProfiles']))  
					{
						
						//Create Profile Fields Group Table
						$sql = "CREATE TABLE IF NOT EXISTS `" . $profileFieldsGroupTable . "` (
							`id` int unsigned not null auto_increment,
							`group_name` VARCHAR(50) NOT NULL ,
							`title` VARCHAR(255) NOT NULL ,
							`position` INT(3) NOT NULL DEFAULT 0 ,
							PRIMARY KEY (`id`) )
							ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

						$db->createCommand($sql)->execute();						
						
						// Create Profile Fields Table
						$sql = "CREATE TABLE IF NOT EXISTS `" . $profileFieldsTable . "` (
							`id` int unsigned NOT NULL auto_increment,
							`field_group_id` int unsigned NOT NULL default '0',
							`varname` varchar(50) NOT NULL,
							`title` varchar(255) NOT NULL,
							`hint` text NOT NULL,
							`field_type` varchar(50) NOT NULL,
							`field_size` int(3) NOT NULL default '0',
							`field_size_min` int(3) NOT NULL default '0',
							`required` int(1) NOT NULL default '0',
							`match` varchar(255) NOT NULL,
							`range` varchar(255) NOT NULL,
							`error_message` varchar(255) NOT NULL,
							`other_validator` varchar(255) NOT NULL,
							`default` varchar(255) NOT NULL,
							`position` int(3) NOT NULL default '0',
							`visible` int(1) NOT NULL default '0',
							PRIMARY KEY  (`id`),
							KEY `varname` (`varname`,`visible`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

						$db->createCommand($sql)->execute();

						// Create Profiles Table
						$sql = "CREATE TABLE IF NOT EXISTS `" . $profileTable . "` (
							`profile_id` int unsigned NOT NULL auto_increment,
							`user_id` int unsigned NOT NULL,
							`timestamp` timestamp NOT NULL,
							`privacy` ENUM('protected', 'private', 'public') NOT NULL,
							`lastname` varchar(50) NOT NULL default '',
							`firstname` varchar(50) NOT NULL default '',
							`email` varchar(255) NOT NULL default '',
							`street` varchar(255),
							`city` varchar(255),
							`about` text,
							PRIMARY KEY  (`profile_id`),
							KEY `fk_profiles_users` (`user_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

						$db->createCommand($sql)->execute();
					}

					if(isset($_POST['installRole']))  
					{
						// Create Roles Table
						$sql = "CREATE TABLE IF NOT EXISTS `".$rolesTable."` (
							`id` INT unsigned NOT NULL AUTO_INCREMENT ,
							`title` VARCHAR(255) NOT NULL ,
							`description` VARCHAR(255) NULL ,
							PRIMARY KEY (`id`)) 
								ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

						$db->createCommand($sql)->execute();

						// Create User_has_role Table

						$sql = "CREATE TABLE IF NOT EXISTS `".$userRoleTable."` (
							`id` int unsigned NOT NULL auto_increment,
							`user_id` int unsigned NOT NULL,
							`role_id` int unsigned NOT NULL,
							PRIMARY KEY  (`id`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

						$db->createCommand($sql)->execute();

						// Create User_has_user Table

						$sql = "CREATE TABLE IF NOT EXISTS `".$userUserTable."` (
							`id` int unsigned NOT NULL auto_increment,
							`owner_id` int unsigned NOT NULL,
							`child_id` int unsigned NOT NULL,
							PRIMARY KEY  (`id`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

						$db->createCommand($sql)->execute();

					}

					if(isset($_POST['installMessages'])) 
					{
						// Create Messages Table
						$sql = "
							CREATE TABLE IF NOT EXISTS `" . $messagesTable . "` (
									`id` int unsigned NOT NULL auto_increment,
									`from_user_id` int unsigned NOT NULL,
									`to_user_id` int unsigned NOT NULL,
									`title` varchar(45) NOT NULL,
									`message` text,
									`message_read` tinyint(1) NOT NULL,
									`draft` tinyint(1) default NULL,
									PRIMARY KEY  (`id`),
									KEY `fk_messages_users` (`from_user_id`),
									KEY `fk_messages_users1` (`to_user_id`)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"; 

							$db->createCommand($sql)->execute();
					}

					if(isset($_POST['installDemoData'])) 
					{
						$sql = "INSERT INTO `".$usersTable."` (`id`, `username`, `password`, `activationKey`, `createtime`, `lastvisit`, `superuser`, `status`) VALUES
							(1, 'admin', '".YumUser::encrypt('admin')."', '', ".time().", 0, 1, 1),
							(2, 'demo', '".YumUser::encrypt('demo')."', '', ".time().", 0, 0, 1)";
						$db->createCommand($sql)->execute();

						if(isset($_POST['installRole']))
						{
							$sql = "INSERT INTO `".$rolesTable."` (`title`,`description`) VALUES
								('UserCreator', 'This users can create new Users'),
								('UserRemover', 'This users can remove other Users')";
							$db->createCommand($sql)->execute();

						}
						if(isset($_POST['installProfiles']))
						{
							$sql = "INSERT INTO `".$profileTable."` (`profile_id`, `user_id`, `lastname`, `firstname`, `email`) VALUES
								(1, 1, 'admin','admin','webmaster@example.com'),
								(2, 2, 'demo','demo','demo@example.com')";
							$db->createCommand($sql)->execute();

							$sql = "INSERT INTO `".$profileFieldsTable."` (`varname`, `title`, `field_type`, `field_size`, `required`, `visible`, `other_validator`) VALUES ('email', 'E-Mail', 'VARCHAR', 255, 1, 2, 'CEmailValidator'), ('firstname', 'First name', 'VARCHAR', 255, 1, 2, ''), ('lastname', 'Last name', 'VARCHAR', 255, 1, 2, ''), ('street','Street', 'VARCHAR', 255, 0, 1, ''), ('city','City', 'VARCHAR', 255, 0, 1, ''), ('about', 'About', 'TEXT', 255, 0, 1, '')";
							$db->createCommand($sql)->execute();
							
						}

					}

					// Do it
					$transaction->commit();

					// Victory
					$this->render('success');
				} 
				else 
				{
					throw new CException(Yii::t('UserModule.user',
								'Database connection is not working'));	
				}
			}
			else {
				$this->render('start', array(
					'usersTable' => YumHelper::resolveTableName($this->module->usersTable,Yii::app()->db),
					'settingsTable' => YumHelper::resolveTableName($this->module->settingsTable, Yii::app()->db),
					'textSettingsTable' => YumHelper::resolveTableName($this->module->textSettingsTable,Yii::app()->db),
					'rolesTable' => YumHelper::resolveTableName($this->module->rolesTable,Yii::app()->db),
					'messagesTable' => YumHelper::resolveTableName($this->module->messagesTable,Yii::app()->db),
					'profileTable' => YumHelper::resolveTableName($this->module->profileTable,Yii::app()->db),
					'profileFieldsTable' => YumHelper::resolveTableName($this->module->profileFieldsTable,Yii::app()->db),
					'profileFieldsGroupTable' => YumHelper::resolveTableName($this->module->profileFieldsGroupTable,Yii::app()->db),
					'userRoleTable' => YumHelper::resolveTableName($this->module->userRoleTable,Yii::app()->db),
					'userUserTable' => YumHelper::resolveTableName($this->module->userUserTable,Yii::app()->db),
				));
			}
		} else {
			throw new CException(Yii::t('UserModule.user', 'User management module is not in Debug Mode'));	
		}
	}

	public function actionIndex()
	{
		$this->actionInstall();
	}
}
