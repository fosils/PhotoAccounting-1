<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
		<title>Email Test</title>
	</head>
    <body>
    <div style='border:1px solid black;'>
    	<div style='background-color:#0c0c0c;color:white;'>Save New Email</div>
    	<div>
			<form action='Email.php' method='POST' id='femail'>
    			<ul style='list-style:none;'>
    				<li>
    					<label for='deviceToken' style='display:inline-block;min-width:100px;'>Device Token:</label>
    					<input type='text' name='devicetoken' id='devicetoken' />
    				</li>
    				<li>
    					<label for='deviceToken' style='display:inline-block;min-width:100px;'>User Email:</label>
    					<input type='text' name='email' id='email' />
    				</li>
    				<li style='text-indent:100px;'>
    					<button type='submit' id='submitImage' name='submitImage'>Submit Information</button>
    				</li>
    			</ul>
    		</form>
    	</div>
    </body>
</html>
