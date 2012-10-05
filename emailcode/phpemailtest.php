<?php
//$results = shell_exec('echo "Test" |mail -s "Testbilde fra commandline2" -r mrdavidandersen@gmail.com -a Andet_1348461982.jpeg receipts@shoeboxed.com');
//$results = shell_exec('echo "Test" |mail -s "Testbilde fra commandline2" -r mrdavidandersen@gmail.com -a Andet_1348461982.jpeg mrdavidandersen@gmail.com');
//echo $results;
//echo "Ferdig"
$to      = 'mrdavidandersen@gmail.com';
$subject = 'Test mail for #38';
$message = 'THis is a test mail';
$headers = 'From: webmaster@ebogholderen.dk'. "\r\n" .
    'Reply-To: webmaster@gmail.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
