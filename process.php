<?php 
$session_expiration = time() + 3600 * 2; // +2 hours
session_set_cookie_params($session_expiration);
session_start();

// Wait a second to simulate a some latency
usleep(500000);

function m($m, $t=NULL){
	return "<p class='".($t=="s"?"sending_success":"sending_error")."'>".$m."</p>";
}

if(isset($_SESSION["captcha"]) and isset($_POST["captcha"]) and ($_SESSION["captcha"] == $_POST["captcha"])) :

global $email_send_to, $email_recived_from, $success_msj, $error_msj;

// edit email where contact form is sent
$email_send_to = "enquiry@systemdigits.com";
// edit your domain
$email_recived_from = "Systemdigits LLC";

// edit message for a successfully sent contact form
$success_msj = "Your message has been sent. Thank you for your feedback. We will contact you shortly if needed";
// edit error message
$error_msj = "Ups, There has been an error while sending. Please try again in a few minutes or panic appropriately!";
// edit subject here. If user enters no subject, we will use this one
$standard_subject = "Someone contacted you on ".$email_recived_from;

//------------------------------------------------------------------------------------------------//
function secure($post) {
	if (is_array($post)):
		foreach($post as $item=>$value):
			$post[$item] = term( $value ); //This helps not to break code if single or doble cuotes are sent to process
			$post[$item] = trim( $value ); // Strip whitespace (or other characters) from the beginning and end of a string
		endforeach;
	else:
		$post = term( $post ); //This helps not to break code if single or doble cuotes are sent to process
		$post = trim( $post ); // Strip whitespace (or other characters) from the beginning and end of a string
	endif;
	$post = mynl2br($post);
	
	// insert here aditional security functions
	
	return $post;
}
//------------------------------------------------------------------------------------------------//
// if someone sends you cods or scripts via contact form, this will prevent it from being interpreted
function mynl2br($post) { 
   return strtr($post, array("\t"=>"&nbsp;&nbsp;&nbsp;&nbsp;", "<"=>"&lt;", ">"=>"&gt;", "\r\n" => "<br />", "\r" => "<br />", "\n" => "<br />"));
} 

//------------------------------------------------------------------------------------------------//
function term($post){
//  DO NOT INDENT THIS. adicional spaces are interpreted. This helps not to break code if single or doble cuotes are sent to process
$post = <<<term
$post
term;
return $post; 
}
//------------------------------------------------------------------------------------------------//


// Pull out data from contact form
$fname = (isset($_POST['fname'])?secure($_POST['fname']):'');
$lname = (isset($_POST['lname'])?secure($_POST['lname']):'');
$email = (isset($_POST['email'])?secure($_POST['email']):'');
$phone = (isset($_POST['phone'])?secure($_POST['phone']):'');
$subject = (isset($_POST['subject'])?secure($_POST['subject']):'');
$message = (isset($_POST['message'])?secure($_POST['message']):'');

//------------------------------------------------------------------------------------------------//
$email_recived_from  = str_replace ("http://", "", $email_recived_from );
$email_recived_from  = str_replace ("www.", "", $email_recived_from );

$from = $email_recived_from." <contact@".$email_recived_from.">";

$mail_subject = (empty($subject)?$standard_subject:$subject); 
//------------------------------------------------------------------------------------------------//

//------------------------------------------------------------------------------------------------//
// setting HTML email function
function sendHTMLemail($body,$from, $to, $subject) {
	
	global $success_msj, $error_msj;
	$boundary = uniqid("HTMLEMAIL"); 
	
	$headers  = "From: $from\r\n";
	$headers .= "MIME-Version: 1.0\r\n"; 
	
	$headers .= "Content-Type: multipart/alternative;".
				"boundary = $boundary\r\n\r\n"; 
	$headers .= "This is a MIME encoded message.\r\n\r\n"; 
	
	$headers .= "--$boundary\r\n".
				"Content-Type: text/html; charset=utf-8\r\n".
				"Content-Transfer-Encoding: base64\r\n\r\n"; 

	$headers .= chunk_split(base64_encode($body));

	return ( mail($to,$subject,"",$headers,"-f".$from) ? m($success_msj,"s") : m($error_msj) );
} 
//------------------------------------------------------------------------------------------------//


ob_start();?>
<html>
<head>
	<!--
		Lots of email clients strip all head information like CSS style classes. 
		That is why we included all style directly into the body using span tags.
		Table was used for the layout to increase outlook compatibility and other old email clients.	
	-->
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" bgcolor="#8ED4F4" >
 
 
<table width="100%"  cellspacing="0" bgcolor="#8ED4F4" >
	<tr>
		<td valign="top" align="center">
	 
 
			<table width="80%" style=" width:80%;min-width:326px;margin-top:10px;" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF">
				<tr>
					<td style="background-color:#337AB7; text-align:center; padding:10px;" align="center">
						<span style="color:#fff;font-family:arial; font-size:140%;">A new person <span style="color:#FF9366">contacted</span> you:</span>
					</td>
 
				</tr>
				
				<tr>
 
					<td bgcolor="#FFFFFF">
 
						<p>
							<span style="font-size:12px;font-weight:normal;color:#666;font-family:arial;line-height:150%;">
																<br /><strong>First Name:</strong> <?=$fname?>
								<br /><strong>Last Name:</strong> <?=$lname?>
								<br /><strong>Email:</strong> <?=$email?>
								<br /><strong>Phone No:</strong> <?=$phone?>
								<br /><strong>Subject:</strong> <?=$subject?>
								<br /><strong>Message:</strong> <?=$message?>
								
							</span>
						</p>
					</td>
				</tr>
				<tr>
					<td style="background-color:#337AB7; text-align:center;" align="center" valign="top" colspan="2">
						<span style="font-size:11px;color:#fff;line-height:200%;font-family:arial;text-decoration:none;">Contact service provided by <br />
							<a style="color:#fff; font-weight:bold; font-size:110%; text-decoration:none;" href="http://scriptgenerator.net" title="scriptgenerator.net">
								<span style="color:#FF9366">Script</span>Generator
							</a>
						</span>
					</td>
				</tr>
			</table>
			
		</td>
	</tr>
</table>
</body>
</html>
<? $HTML = ob_get_clean();


// display succes or error message depending is email has been sent or not
echo sendHTMLemail($HTML,$from,$email_send_to,$mail_subject);

else:

	// display some error message for smart people that cheat or if the session is not setted
	echo m("Hmmmm, doing tricks eh!!!");
	
endif;
?>