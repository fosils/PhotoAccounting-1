<?php

/** COPYRIGHT Time at Task Aps*/


ini_set('max_execution_time',1000);
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$account = "test918171@gmail.com";
$password = "pleasechangethispassword";
$bot = new Retriever($hostname,$account,$password);
$bot->main();

class Retriever
{
	var $inbox, $emails, $account, $mister;
	function __construct($hostname, $acc, $password)
	{
		$this->account = $acc;
		$this->inbox = imap_open($hostname,$this->account,$password) or $this->report("ERROR WHILE CONNECTING TO MAILBOX","Access to the mailbox $this->account was denied.\n The reason: "
			. imap_last_error(),"Failed to connect to mailbox!<br>");//connecting to mailbox
		$this->emails = imap_search($this->inbox,'ALL');
		$this->mister = //"ramblerramblerramblerrambler@gmail.com";//
			"ramblerramblerramblerrambler@gmail.com";
		echo "$this->mister is your account.<br><br>";
	}
	function __destruct()
	{
		imap_close($this->inbox);//disconnecting from mailbox
	}
	function main()
	{
		if ($this->emails)//if emails were found
		{
			rsort($this->emails);
			$limit = 10000;
			foreach ($this->emails as $email)//WHAT IF LIMIT THE NUMBER OF EMAILS?
			{
				if ($email <= $limit)
				{
					$files = array();
					$mes_uid = dechex(mt_rand(99999,999999));//TODO: try using md5()
					$this->downloadAttachments($email,$files,$mes_uid);
					$this->forwardMail($email,$files);
					imap_mail_copy($this->inbox,$email,'[Gmail]/Sent Mail');
					imap_mail_move($this->inbox,$email,'[Gmail]/All Mail');
				}
				else
					break;
			}
			echo "Finished!!!";
		}
		else
			echo "The inbox is empty!";
	}
	function downloadAttachments($email,&$files, $mes_uid)
	{
		$structure = imap_fetchstructure($this->inbox,$email) or $this->report("ERROR WHILE READING EMAIL","There was an error while reading email: " . imap_last_error() . 
		"\nProbably, one of the emails was deleted while script was executing.","Failed to read email!<br>");//get email structure
		$attachments = array();
		if (isset($structure->parts) && count($structure->parts) > 0)//if at least one file was attached
		{
			for ($i = 0; $i < count($structure->parts); $i++)//searching for attached files
			{
				$attachments[$i] = array('is_attachment' => false, 'filename' => '', 'name' => '', 'attachment' => '');
				if ($structure->parts[$i]->ifdparameters)//if dparameters array exists
					foreach ($structure->parts[$i]->dparameters as $object)
						if (strtolower($object->attribute) == 'filename') 
						{
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['filename'] = $object->value;
						}
				if ($structure->parts[$i]->ifparameters)//if parameters array exists
					foreach ($structure->parts[$i]->parameters as $object)
						if(strtolower($object->attribute) == 'name') 
						{
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['name'] = $object->value;
						}
				if ($attachments[$i]['is_attachment'])//if attachment was found
				{
					$attachments[$i]['attachment'] = imap_fetchbody($this->inbox,$email,$i+1) or $this->report("ERROR WHILE READING EMAIL","There was an error while reading email: " 
						. imap_last_error() . "\nProbably, one of the emails was deleted while script was executing.","Failed to read email!<br>");
					switch ($structure->parts[$i]->encoding)//decoding data
					{
						case 3://base64 encoding
							$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
							break;
						case 4://quoted-printable encoding
							$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
							break;
					}
				}
			}
		}
		foreach ($attachments as $attachment)
			if ($attachment['is_attachment'])//if attachment was found
			{
				$filename = $attachment['name'];
				if (empty($filename))
					$filename = $attachment['filename'];
				$lastdot = strrpos($filename,'.');
				$ext = '';
				if ($lastdot !== false)//if file has an extension
					$ext = substr($filename,$lastdot);
				$header = imap_header($this->inbox,$email) or $this->report("ERROR WHILE READING EMAIL","There was an error while reading email: " . imap_last_error()
					. "\nProbably, one of the emails was deleted while script was executing.","Failed to read email!<br>");
				$to = $header->to[0]->mailbox;//email address of receiver without at sign and mail server
				$from = $header->from[0]->mailbox;//email address of sender without at sign and mail server
				$datetime = $header->date;//date and time the email was delivered
				$this->formatDateTime($datetime);
				$att_id = dechex(mt_rand(99999,999999));//WHAT IF uniqid()?
				$info = $to . '_' . $from . '_' . $datetime . '_' . $mes_uid . '_' . $att_id;
				$location = "./PhotoAccounting/images/";//"Z:\home\localserver\www\downloads";//"/var/www/html/PhotoAccounting/images/";
				$fname = $location . $info . $ext;
				array_push($files,$fname);
				$stream = fopen($fname,"w");
				fwrite($stream,$attachment['attachment']);
				fclose($stream);
			}
	}
	function formatDateTime(&$datetime)
	{
		$day = substr($datetime,5,2);
		if ($day[1] == ' ')
		{
			$day = $day[0];
			$datetime = substr($datetime,7);
		}
		else
			$datetime = substr($datetime,8);
		$month = substr($datetime,0,3);
		$months = array('Jan'=>'01', 'Feb'=>'02', 'Mar'=>'03', 'Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12');
		$month = $months[$month];
		$year = substr($datetime,4,4);
		$time = substr($datetime,9,8);
		for ($i = 0; $i < strlen($time); $i++)
			if ($time[$i] == ':')
				$time[$i] = '.';
		$datetime = $day . '-' . $month . '-' . $year . '_' . $time;
	}
	function report($subject,$message,$alert)
	{
		mail($this->mister,$subject,$message,"From: $this->account\r\n");
		die($alert . "AN EXPLANATORY EMAIL WAS SENT.");
	}
	function forwardMail($email, $files)
	{
		$mime_boundary = "==Multipart_Boundary_x" . md5(time()) . "x";//???
		$headers = "From: $this->account\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed;\r\n boundary=\"{$mime_boundary}\"";
		$subject = '';
		$message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"iso-8859-1\"\n" . 
				"Content-Transfer-Encoding: 7bit\n\n";
		if (!empty($files))//if attachments were found
		{
			$filename = substr($files[0],strrpos($files[0],'/')+1);//disposing of the path
			$subject = str_replace(substr($filename,strrpos($filename,'_')),'',$filename);//disposing of attachment id and extension
			$receiver = substr($subject,0,strpos($subject,'_')+1);//keeping the receiver's address in variable
			$subject = str_replace($receiver,'',$subject);//disposing of the receiver's address
			$subject = $receiver . str_replace(substr($subject,0,strpos($subject,'_')+1),'',$subject);//disposing of the sender's address and concatenating the receiver's one
			$message .= $e = imap_fetchbody($this->inbox,$email,"1.2") or $this->report("ERROR WHILE READING EMAIL","There was an error while reading email: " . imap_last_error() . 
				"\nProbably, one of the emails was deleted while script was executing.","Failed to read email!<br>");
			foreach ($files as $file)
			{
				$stream = fopen($file,'rb');
				$data = fread($stream,filesize($file));
				fclose($stream);
				$data = chunk_split(base64_encode($data));//???
				$fname = substr($file,strrpos($file,'/')+1);
				$message .= "--{$mime_boundary}\nContent-Disposition: attachment;\n filename=\"{$fname}\"\nContent-Transfer-Encoding: base64\n\n" . $data;
			}
		}
		else
		{
			$subject = imap_header($this->inbox,$email)->subject;//if there are no files then leave the same subject
			$message .= $e = imap_fetchbody($this->inbox,$email,"2") or $this->report("ERROR WHILE READING EMAIL","There was an error while reading email: " . imap_last_error() . 
				"\nProbably, one of the emails was deleted while script was executing.","Failed to read email!<br>");
		}
		if (mail($this->mister,$subject,$message,$headers/*,"-framblerramblerramblerrambler@gmail.com"*/))//sending email from the inbox to givrn address
			echo "Email with subject \"$subject\" was sent successfully!<br>";
		else
			$this->report("ERORR WHILE SENDING EMAIL","There was an error while sending email wit subject $subject.\nUnfortunately, it failed to reach the recipient.\nPlease, try to execute this script again.",
				"Failed to send email with subject \"$subject\"!<br>");
	}
}
?>