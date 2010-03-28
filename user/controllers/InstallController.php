<?php

class InstallController extends Controller
{
	public function actionInstall() 
	{
		if($this->module->debug) 
		{
			if(Yii::app()->request->isPostRequest) 
			{
				if($db = Yii::app()->db) {
					$transaction = $db->beginTransaction();

					$usersTable = $_POST['usersTable'];
					$profileFieldsTable = $_POST['profileFieldsTable'];
					$profileTable = $_POST['profileTable'];
					$messagesTable = $_POST['messagesTable'];
					$rolesTable = $_POST['rolesTable'];
					$userRoleTable = $_POST['userRoleTable'];

					// Clean up existing Installation
					$db->createCommand(sprintf('drop table if exists %s, %s, %s, %s, %s, %s',
								$usersTable,
								$profileFieldsTable, 
								$profileTable,
								$messagesTable,
								$rolesTable,
								$userRoleTable
								)
							)->execute();

					// Create User Table
					$sql = "CREATE TABLE IF NOT EXISTS `" . $usersTable . "` (
						`id` int(11) NOT NULL auto_increment,
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
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";


					$db->createCommand($sql)->execute();

					
					if(isset($_POST['installProfiles']))  
{

					// Create Profile Fields Table
					$sql = "CREATE TABLE IF NOT EXISTS `" . $profileFieldsTable . "` (
						`id` int(10) NOT NULL auto_increment,
						`varname` varchar(50) NOT NULL,
						`title` varchar(255) NOT NULL,
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
						`profile_id` int(11) NOT NULL auto_increment,
						`user_id` int(11) NOT NULL,
						`lastname` varchar(50) NOT NULL default '',
						`firstname` varchar(50) NOT NULL default '',
						`email` varchar(255) NOT NULL default '',
						`about` text,
						`street` varchar(255),
						PRIMARY KEY  (`profile_id`),
						KEY `fk_profiles_users` (`user_id`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

					$db->createCommand($sql)->execute();
}

					if(isset($_POST['installRole']))  
					{
						// Create Roles Table
						$sql = "CREATE TABLE IF NOT EXISTS `".$rolesTable."` (
							`id` INT NOT NULL AUTO_INCREMENT ,
							`title` VARCHAR(255) NOT NULL ,
							`description` VARCHAR(255) NULL ,
							PRIMARY KEY (`id`)) 
								ENGINE = InnoDB; ";

						$db->createCommand($sql)->execute();

						// Create User_has_role Table

						$sql = "CREATE TABLE IF NOT EXISTS `".$userRoleTable."` (
							`id` int(11) NOT NULL auto_increment,
							`user_id` int(11) NOT NULL,
							`role_id` int(11) NOT NULL,
							PRIMARY KEY  (`id`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

						$db->createCommand($sql)->execute();
					}

					if(isset($_POST['installMessages'])) 
					{
						// Create Messages Table
						$sql = "
							CREATE TABLE IF NOT EXISTS `" . $messagesTable . "` (
									`id` int(11) NOT NULL auto_increment,
									`from_user_id` int(11) NOT NULL,
									`to_user_id` int(11) NOT NULL,
									`title` varchar(45) NOT NULL,
									`message` text,
									`message_read` tinyint(1) NOT NULL,
									`draft` tinyint(1) default NULL,
									PRIMARY KEY  (`id`),
									KEY `fk_messages_users` (`from_user_id`),
									KEY `fk_messages_users1` (`to_user_id`)
									) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"; 

							$db->createCommand($sql)->execute();
					}



					if($this->module->installDemoData) 
					{
						$sql = "INSERT INTO `".$usersTable."` (`id`, `username`, `password`, `activationKey`, `createtime`, `lastvisit`, `superuser`, `status`) VALUES
							(1, 'admin', '".User::encrypt('admin')."', '', 0, 1266571424, 1, 1),
							(2, 'demo', '".User::encrypt('demo')."', '', 0, 1266543330, 0, 1)";
						$db->createCommand($sql)->execute();

						if(isset($_POST['installProfiles']))
						{
							$sql = "INSERT INTO `".$profileTable."` (`profile_id`, `user_id`, `lastname`, `firstname`, `email`) VALUES
								(1, 1, 'admin','admin','webmaster@example.com'),
								(2, 2, 'demo','demo','demo@example.com')";
							$db->createCommand($sql)->execute();

							$sql = "INSERT INTO `".$profileFieldsTable."` (`varname`, `title`, `field_type`, `field_size`, `required`, `visible`) VALUES ('email', 'E-Mail', 'VARCHAR', 255, 1, 2), ('firstname', 'First name', 'VARCHAR', 255, 1, 2), ('lastname', 'Last name', 'VARCHAR', 255, 1, 2)";

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
					throw new CException(Yii::t('UserModule.user', 'Database Connection is not working'));	
				}
			}
			else {
				$this->render('start');
			}
		} else {
			throw new CException(Yii::t('UserModule.user', 'User management Module is not in Debug Mode'));	
		}
	}

	public function actionIndex()
	{
		$this->actionInstall();
	}
}
