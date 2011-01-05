<?php

/**
 * YumMailer just implements the send() method that handles (guess what)
 * the mailing process of messages.
 * the first parameter can either be an array containing the Information 
 * or a string containing the recipient. The subject and mesage goes
 * inside the other parameters in this case.
 * @return true if sends mail, false otherwise
 */
class YumMailer {
	static public function send($to, $subject = null, $body = null) {
		if($to instanceof YumUser)
			$to = $to->profile[0]->email;

		if(!is_array($to)) 
			$to = array(
					'to' => $to,
					'subject' => $subject,
					'body' => $body);

		if(Yum::module()->mailer == 'swift') {
			$sm = Yii::app()->swiftMailer;
			$mailer = $sm->mailer($sm->mailTransport());
			$message = $sm->newMessage($to['subject'])
				->setFrom($to['from'])
				->setTo($to['to'])
				->setBody($to['body']);
			return $mailer->send($message);
		} else if(Yum::module()->mailer == 'PHPMailer') {
			Yii::import('application.extensions.phpmailer.JPhpMailer');
			$mailer = new JPhpMailer(true);
			if (Yum::module()->phpmailer['transport'])
				switch (Yum::module()->phpmailer['transport']) {
					case 'smtp':
						$mailer->IsSMTP();
						break;
					case 'sendmail':
						$mailer->IsSendmail();
						break;
					case 'qmail':
						$mailer->IsQmail();
						break;
					case 'mail':
					default:
						$mailer->IsMail();
				}
			else
				$mailer->IsMail();

			if (Yum::module()->phpmailer['html'])
				$mailer->IsHTML(Yum::module()->phpmailer['html']);
			else
				$mailer->IsHTML(false);

			$mailerconf=Yum::module()->phpmailer['properties'];
			if(is_array($mailerconf))
				foreach($mailerconf as $key=>$value) {
					if(isset(JPhpMailer::${$key}))
						JPhpMailer::${$key} = $value;
					else
						$mailer->$key=$value;
				}
			$mailer->SetFrom($to['from'], Yum::module()->phpmailer['msgOptions']['fromName']); //FIXME
			$mailer->AddAddress($to['to'], Yum::module()->phpmailer['msgOptions']['toName']); //FIXME
			$mailer->Subject = $to['subject'];
			$mailer->Body = $to['body'];
			return $mailer->Send();
		} else {
			$headers = "From: " . Yum::module()->registrationEmail ."\r\nReply-To: ".Yii::app()->params['adminEmail'];
			return mail($to['to'], $to['subject'], $to['body'], $headers);
		}
	}
}
