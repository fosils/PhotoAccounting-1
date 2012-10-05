<?php 
date_default_timezone_set('Europe/London');

// appears as sender in emails sent
define('SENDER_EMAIL', 'webmaster@ebogholderen.dk');
define('SENDER_NAME', 'webmaster');
// bounce emails are sent here
define('RETURN_PATH', 'da@ebogholderen.dk');
// logs the failure happening in script during email sending
define('LOG_FILE', './mail.log');
// notifies about the email sending failed
define('FAILURE_NOTIFY_EMAIL', 'mrdavidandersen@gmail.com');


$subject = 'my subject';
$message = 'message in email';
$attachement = array('fileName' => 'myattach.txt',
		      'filePath' => 'myattach.txt',
                      'type'     => 'text/plain' );
$to = 'mrdavidandersen@gmail.com';

$mailParams = array('message' => $message,
		    'attachement' => $attachement,
		    'senderEmail' => SENDER_EMAIL,
		    'senderName'  => SENDER_NAME,
                    'return-path' => RETURN_PATH );
SendMail($to,$subject,$mailParams);


########################################################################
function SendMail($to, $subject, $mailParams = array())
{
	require_once 'Zend/Mail.php';
	
	// sender's email address
	if( isset($mailParams['senderEmail']) ) {
	 	$senderEmail = $mailParams['senderEmail'];
	} else {
	 	$senderEmail = 'webmaster@ebogholderen.dk';
	}
	
	// sender's name
	if( isset($mailParams['senderName']) ) {
		$senderName = $mailParams['senderName'];
	} else {
		$senderName = 'webmaster';
	}
	

        // return path
        if( isset($mailParams['return-path']) ) {
                $returnPath = $mailParams['return-path'];
        } else {
                $returnPath = 'da@ebogholderen.dk';
        }

	$message = $mailParams['message'];
	
	$mail = new Zend_Mail();
	$mail->setFrom($senderEmail, $senderName);
	$mail->setReplyTo($senderEmail, $senderName);
	$mail->setReturnPath($returnPath);

	if ( is_array($to) && count($to) == 2) {
		// Email and full name
		$mail->addTo($to[0], $to[1]);
		$sendMailTo = $to[0];
	} else {
		// Email only
		$mail->addTo($to);
		$sendMailTo = $to;
	}

	// set subject
	$mail->setSubject($subject);
	
	// set message
	if( isset($mailParams['messageType']) ) {
		$mail->setBodyText($message);
	} else {
		$mail->setBodyHtml($message);
	}
	
	// if mail has attachements
	if ( isset($mailParams['isMultipleAttach']) ) {
			if ( isset($mailParams['attachement']) && count($mailParams['attachement']) ) {

					foreach ( $mailParams['attachement'] as $attachementDetails ) {

							$attachement  = $mail->createAttachment(file_get_contents($attachementDetails['filePath']));
							$attachement->type        = $attachementDetails['type'];
							$attachement->filename    = $attachementDetails['fileName'];
					}
			}
	} elseif ( isset($mailParams['attachement']) && count($mailParams['attachement']) ) {
					$attachParams = $mailParams['attachement'];
			  		$attachement  = $mail->createAttachment(file_get_contents($attachParams['filePath']));
					$attachement->type        = $attachParams['type'];
					$attachement->filename    = $attachParams['fileName'];
	}
  
	$mail->Subject  = $subject;
	$mail->Body     = $message;
	$mail->AltBody  = strip_tags($message);
	$mail->addHeader('MIME-Version', '1.0');
	$mail->addHeader('Content-Transfer-Encoding', '8bit');
	$mail->addHeader('X-Mailer:', 'PHP/'.phpversion());

	try {
		$mail->Send();
	} catch (Exception $e) {
		$log  = file_get_contents(LOG_FILE);
		$logLine = "[".date('Y-m-d H:i:s')."] Sending mail failed for ".$sendMailTo." Subject:".$subject."\n";
		$log .= $logLine;
		file_put_contents(LOG_FILE, $log);
		mail(FAILURE_NOTIFY_EMAIL, 'Mail send failed', 'Details are as given below <BR>'.$logLine,
	                                                                               'From: Fail reports<failure@ebogholderen.dk>');
	}

    return true;
}
