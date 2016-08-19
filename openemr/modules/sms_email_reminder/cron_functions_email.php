<?php 
////////////////////////////////////////////////////////////////////
// CRON FUNCTIONS - to use with cron_sms and cron_email backend
// scripts to notify events
////////////////////////////////////////////////////////////////////

require_once(basename(__DIR__)."/../../interface/globals.php");

////////////////////////////////////////////////////////////////////
// Function:	cron_SendMail
// Purpose:	send mail
// Input:	to, subject, email body and from 
// Output:	status - if sent or not
////////////////////////////////////////////////////////////////////
function cron_SendMail( $to, $subject, $vBody, $from, $attachments, $path, $inline_files, $inline_path )
{
	// check if smtp globals set 
	if( $GLOBALS['SMTP_HOST'] == '' )
	{
		// larry :: debug
		//echo "\nDEBUG :: use mail method\n";	
	
		// larry :: add cc/bcc - bot used ?
		$cc = "";
		$bcc = "";
		$format = 0;
	
		/* //echo "function called";exit;
		if( strlen( $format )==0 )	$format="text/html";
		$headers  = "MIME-Version: 1.0\r\n"; 
		$headers .= "Content-type: ". $format ."; charset=iso-8859-1\r\n"; 
		
		// additional headers 
		$headers .= "From: $from\r\n"; 
		if( strlen($cc)>5 ) $headers .= "Cc: $cc\r\n"; 
		if( strlen($bcc)>5 ) $headers .= "Bcc: $bcc\r\n"; 
		$cnt = "";
		$cnt .= "\nHeaders : ".$headers;
		$cnt .= "\nDate Time :". date("d M, Y  h:i:s");
		$cnt .= "\nTo : ".$to;
		$cnt .= "\nSubject : ".$subject;
		$cnt .= "\nBody : \n".$vBody."\n"; */
		
		/* $headers  = "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\n"; */
		/* $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; */ 
		/* $headers  = "MIME-Version: 1.0";
        $headers .= "Content-type: text/html; charset=iso-8859-1"; */
		$headers ="";
		$headers  .='MIME-Version: 1.0' . PHP_EOL;
        $headers .= 'Content-Type: text/html; charset=ISO-8859-1' . PHP_EOL;
		//$headers .= "Content-Type: multipart/alternative; boundary = $vBody\r\n"; 
	   // $headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
		/* $headers = "MIME-Version: 1.0rn"; 
        $headers .= "Content-type: text/html; charset=iso-8859-1rn";  */
		if(1)
		{
			//WriteLog($cnt);
		}
		$mstatus = true;
		$mstatus = @mail( $to, $subject, $vBody, $headers );
		// larry :: debug
		//echo "\nDEBUG :email: send email from=".$from." to=".$to." sbj=".$subject." body=".$vBody." head=".$headers."\n";
		//echo "\nDEBUG :email: send status=".$mstatus."\n";
	} else
	{
		// larry :: debug
		//echo "\nDEBUG :: use smtp method\n";	
		
		if( !class_exists( "smtp_class" ) )
		{
			require_once(basename(__DIR__)."/../../library/classes/smtp/smtp.php");
			require_once(basename(__DIR__)."/../../library/classes/smtp/sasl.php");
		}
		
		$strFrom = $from;
		$sender_line=__LINE__;
		$strTo = $to;
		$recipient_line=__LINE__;
		if( strlen( $strFrom ) == 0 ) return( false );
		if( strlen( $strTo ) == 0 ) return( false );
		
		//if( !$smtp ) 
		$smtp=new smtp_class;
		
		$smtp->host_name = $GLOBALS['SMTP_HOST'];
		$smtp->host_port = $GLOBALS['SMTP_PORT'];
		$smtp->ssl = 1;
		$smtp->localhost = $GLOBALS['smtp_localhost'];
		$smtp->direct_delivery = 0;
		$smtp->timeout = 10;
		$smtp->data_timeout = 0;
		
		$smtp->debug = 1;
		$smtp->html_debug = 0;
		$smtp->pop3_auth_host = "";
		
		$smtp->user = $GLOBALS['SMTP_USER'];
		$smtp->password = $GLOBALS['SMTP_PASS'];
		
		$smtp->realm = "";
		// Workstation name for NTLM authentication
		$smtp->workstation = "";
		// Specify a SASL authentication method like LOGIN, PLAIN, CRAM-MD5, NTLM, etc..
		// Leave it empty to make the class negotiate if necessary 
		$smtp->authentication_mechanism = "";
		
		// If you need to use the direct delivery mode and this is running under
		// Windows or any other platform
		if($smtp->direct_delivery)
		{
			if(!function_exists("GetMXRR"))
			{
				$_NAMESERVERS=array();
				include("getmxrr.php");
			}
		}
		    $uid = md5(uniqid(time()));
   //Removedthe Header from body string and added seperatey in headerss string as the attachment and content not proper in AOL,Hotmail.
//    $vBodys .= "MIME-Version: 1.0\r\n";
 //   $vBodys .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
  //  $vBodys .= "This is a multi-part message in MIME format.\r\n";
//End.
    $vBodys .= "--".$uid."\r\n";
	$fileBaseName=Array();
				foreach($inline_files as $file_name)
		{
		if($file_name!="")
		{
			$file = $inline_path.$file_name;
	(in_array(basename($file),$fileBaseName)) ? $contentId=date("H_i_s").basename($file) : $contentId=basename($file);

		$fileBaseName[]=$contentId;
    $content = file_get_contents($file);
	$filetype = pathinfo($file, PATHINFO_EXTENSION);
    $content = chunk_split(base64_encode($content));
    $vBodys .= "Content-Type: image/".$filetype."; name=\"".basename($file)."\"\r\n"; // use different content types here
    $vBodys .= "Content-Transfer-Encoding: base64\r\n";
    $vBodys .= "Content-ID: <".$contentId.">\r\n";
    $vBodys .= "Content-Disposition: inline; filename=\"".basename($file)."\"\r\n\r\n";
    $vBodys .= $content."\r\n\r\n";
			    $vBodys .= "--".$uid."\r\n";
				$vBody=str_replace("$file_name","cid:".$contentId,$vBody);
				//echo $vBody;
		}
		}
    $vBodys .= "Content-Type:text/html; charset=iso-8859-1\r\n";
    $vBodys .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $vBodys .= $vBody."\r\n\r\n";
		foreach($attachments as $file_name)
		{
		if($file_name!="")
		{
		    $vBodys .= "--".$uid."\r\n";
			$file = $path.$file_name;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
	(strpos($file_name,"/")) ? list($pathext,$fname)=explode("/",$file_name) : $fname = $file_name;
    $content = chunk_split(base64_encode($content));
    $vBodys .= "Content-Type: application/octet-stream; name=\"".$fname."\"\r\n"; // use different content types here
    $vBodys .= "Content-Transfer-Encoding: base64\r\n";
    $vBodys .= "Content-Disposition: attachment; filename=\"".$fname."\"\r\n\r\n";
    $vBodys .= $content."\r\n\r\n";
		}
		}
	
		$vBodys .= "--".$uid."--";
//Seperated Header as, the content and attachment was not proper in HotMail, AOL.
$headerss = "From:". $strFrom;
$headerss .= "MIME-Version:1.0\r\n";
$headerss .= "Content-Type:multipart/mixed; boundary=\"{$uid}\"\r\n\r\n";
//End.	
		if( $smtp->SendMessage(
			$strFrom,
			array( $strTo ),
			array(
				"From: $strFrom",
				"Reply-To: $strFrom",
				"Return-Path: $strFrom",
				"To: $strTo",
				"Subject: $subject",
				"header: $headerss",
				"Date Time :". date("d M, Y  h:i:s"),
				"$vBodys"
				),
			"" ) )	
		{
			//echo "Message sent to $to OK.\n";
			$mstatus = true;
		} else
		{
			 //echo "Cound not send the message to $to.\nError: ".$smtp->error."\n";
			 $mstatus = false;
		}
		
		unset( $smtp );	
	}		
	
	return $mstatus;
}

function campaign_SendMail( $to, $subject, $vBody, $from, $attachments, $path, $inline_files, $inline_path )
{
	$twilioCredential = sqlStatement("SELECT * FROM `globals`");
	$twilioArray = array();
	while($rowTwilio = sqlFetchArray($twilioCredential)){
		$twilioArray[$rowTwilio['gl_name']] = $rowTwilio['gl_value'];
	}

	// check if smtp globals set
	if( $twilioArray['SMTP_HOST'] == '' )
	{
		// larry :: debug
		//echo "\nDEBUG :: use mail method\n";

		// larry :: add cc/bcc - bot used ?
		$cc = "";
		$bcc = "";
		$format = 0;

		/* //echo "function called";exit;
		 if( strlen( $format )==0 )	$format="text/html";
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: ". $format ."; charset=iso-8859-1\r\n";

		// additional headers
		$headers .= "From: $from\r\n";
		if( strlen($cc)>5 ) $headers .= "Cc: $cc\r\n";
		if( strlen($bcc)>5 ) $headers .= "Bcc: $bcc\r\n";
		$cnt = "";
		$cnt .= "\nHeaders : ".$headers;
		$cnt .= "\nDate Time :". date("d M, Y  h:i:s");
		$cnt .= "\nTo : ".$to;
		$cnt .= "\nSubject : ".$subject;
		$cnt .= "\nBody : \n".$vBody."\n"; */

		/* $headers  = "MIME-Version: 1.0\n";
		 $headers .= "Content-Type: text/html; charset=ISO-8859-1\n"; */
		/* $headers  = 'MIME-Version: 1.0' . "\r\n";
		 $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; */
		/* $headers  = "MIME-Version: 1.0";
		 $headers .= "Content-type: text/html; charset=iso-8859-1"; */
		$headers ="";
		$headers  .='MIME-Version: 1.0' . PHP_EOL;
		$headers .= 'Content-Type: text/html; charset=ISO-8859-1' . PHP_EOL;
		//$headers .= "Content-Type: multipart/alternative; boundary = $vBody\r\n";
		// $headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
		/* $headers = "MIME-Version: 1.0rn";
		 $headers .= "Content-type: text/html; charset=iso-8859-1rn";  */
		if(1)
		{
			//WriteLog($cnt);
		}
		$mstatus = true;
		$mstatus = @mail( $to, $subject, $vBody, $headers );
		// larry :: debug
		//echo "\nDEBUG :email: send email from=".$from." to=".$to." sbj=".$subject." body=".$vBody." head=".$headers."\n";
		//echo "\nDEBUG :email: send status=".$mstatus."\n";
	} else
	{
		// larry :: debug
		//echo "\nDEBUG :: use smtp method\n";

		if( !class_exists( "smtp_class" ) )
		{
			require_once(basename(__DIR__)."/../../library/classes/smtp/smtp.php");
			require_once(basename(__DIR__)."/../../library/classes/smtp/sasl.php");
		}

		$strFrom = $from;
		$sender_line=__LINE__;
		$strTo = $to;
		$recipient_line=__LINE__;
		if( strlen( $strFrom ) == 0 ) return( false );
		if( strlen( $strTo ) == 0 ) return( false );

		//if( !$smtp )
		$smtp=new smtp_class;

		$smtp->host_name = $twilioArray['SMTP_HOST'];
		$smtp->host_port = $twilioArray['SMTP_PORT'];
		$smtp->ssl = 1;
		$smtp->localhost = $twilioArray['smtp_localhost'];
		$smtp->direct_delivery = 0;
		$smtp->timeout = 10;
		$smtp->data_timeout = 0;

		$smtp->debug = 1;
		$smtp->html_debug = 0;
		$smtp->pop3_auth_host = "";

		$smtp->user = $twilioArray['SMTP_USER'];
		$smtp->password = $twilioArray['SMTP_PASS'];

		$smtp->realm = "";
		// Workstation name for NTLM authentication
		$smtp->workstation = "";
		// Specify a SASL authentication method like LOGIN, PLAIN, CRAM-MD5, NTLM, etc..
		// Leave it empty to make the class negotiate if necessary
		$smtp->authentication_mechanism = "";

		// If you need to use the direct delivery mode and this is running under
		// Windows or any other platform
		if($smtp->direct_delivery)
		{
			if(!function_exists("GetMXRR"))
			{
				$_NAMESERVERS=array();
				include("getmxrr.php");
			}
		}
		$uid = md5(uniqid(time()));
		//Removedthe Header from body string and added seperatey in headerss string as the attachment and content not proper in AOL,Hotmail.
		//    $vBodys .= "MIME-Version: 1.0\r\n";
		//   $vBodys .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		//  $vBodys .= "This is a multi-part message in MIME format.\r\n";
		//End.
		$vBodys .= "--".$uid."\r\n";
		$fileBaseName=Array();
		foreach($inline_files as $file_name)
		{
			if($file_name!="")
			{
				$file = $inline_path.$file_name;
				(in_array(basename($file),$fileBaseName)) ? $contentId=date("H_i_s").basename($file) : $contentId=basename($file);

				$fileBaseName[]=$contentId;
				$content = file_get_contents($file);
				$filetype = pathinfo($file, PATHINFO_EXTENSION);
				$content = chunk_split(base64_encode($content));
				$vBodys .= "Content-Type: image/".$filetype."; name=\"".basename($file)."\"\r\n"; // use different content types here
				$vBodys .= "Content-Transfer-Encoding: base64\r\n";
				$vBodys .= "Content-ID: <".$contentId.">\r\n";
				$vBodys .= "Content-Disposition: inline; filename=\"".basename($file)."\"\r\n\r\n";
				$vBodys .= $content."\r\n\r\n";
				$vBodys .= "--".$uid."\r\n";
				$vBody=str_replace("$file_name","cid:".$contentId,$vBody);
				//echo $vBody;
			}
		}
		$vBodys .= "Content-Type:text/html; charset=iso-8859-1\r\n";
		$vBodys .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
		$vBodys .= $vBody."\r\n\r\n";
		foreach($attachments as $file_name)
		{
			if($file_name!="")
			{
				$vBodys .= "--".$uid."\r\n";
				$file = $path.$file_name;
				$file_size = filesize($file);
				$handle = fopen($file, "r");
				$content = fread($handle, $file_size);
				fclose($handle);
				(strpos($file_name,"/")) ? list($pathext,$fname)=explode("/",$file_name) : $fname = $file_name;
				$content = chunk_split(base64_encode($content));
				$vBodys .= "Content-Type: application/octet-stream; name=\"".$fname."\"\r\n"; // use different content types here
				$vBodys .= "Content-Transfer-Encoding: base64\r\n";
				$vBodys .= "Content-Disposition: attachment; filename=\"".$fname."\"\r\n\r\n";
				$vBodys .= $content."\r\n\r\n";
			}
		}

		$vBodys .= "--".$uid."--";
		//Seperated Header as, the content and attachment was not proper in HotMail, AOL.
		$headerss = "From:". $strFrom;
		$headerss .= "MIME-Version:1.0\r\n";
		$headerss .= "Content-Type:multipart/mixed; boundary=\"{$uid}\"\r\n\r\n";
		//End.
		if( $smtp->SendMessage(
				$strFrom,
				array( $strTo ),
				array(
						"From: $strFrom",
						"Reply-To: $strFrom",
						"Return-Path: $strFrom",
						"To: $strTo",
						"Subject: $subject",
						"header: $headerss",
						"Date Time :". date("d M, Y  h:i:s"),
						"$vBodys"
				),
				"" ) )
		{
			//echo "Message sent to $to OK.\n";
			$mstatus = true;
		} else
		{
			//echo "Cound not send the message to $to.\nError: ".$smtp->error."\n";
			$mstatus = false;
		}

		unset( $smtp );
	}

	return $mstatus;
}



?>
