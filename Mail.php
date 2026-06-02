<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mail{

	protected $CI;
	
	public function __construct()
	{

	}

	function sendSmtpMail($toEmail, $htmlContent, $subject = "Notification") {

		// ========================
		// CONFIG SMTP FIXE
		// ========================
		$smtpHost = "10.58.202.61";
		$smtpPort = 25;

		$mailFrom = "noreply@equitybcdc.cd";
		$admin    = "Admin Name";

		// ========================
		// CONSTRUCTION EMAIL
		// ========================
		$email =
			"From: $mailFrom\r\n" .
			"To: $toEmail\r\n" .
			"Subject: $subject\r\n" .
			"MIME-Version: 1.0\r\n" .
			"Content-Type: text/html; charset=UTF-8\r\n\r\n" .
			$htmlContent;

		// ========================
		// FICHIER TEMPORAIRE
		// ========================
		$tmpFile = tempnam(sys_get_temp_dir(), "mail");
		file_put_contents($tmpFile, $email);

		// ========================
		// CURL SMTP
		// ========================
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "smtp://$smtpHost:$smtpPort");
		curl_setopt($ch, CURLOPT_MAIL_FROM, $mailFrom);
		curl_setopt($ch, CURLOPT_MAIL_RCPT, [$toEmail]);
		curl_setopt($ch, CURLOPT_UPLOAD, true);

		$fp = fopen($tmpFile, "r");
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmpFile));

		curl_setopt($ch, CURLOPT_VERBOSE, false);

		$result = curl_exec($ch);
		$error  = curl_error($ch);

		curl_close($ch);
		fclose($fp);
		unlink($tmpFile);

		// ========================
		// RESULT
		// ========================
		if ($result) {
			return [
				"success" => true,
				"message" => "Email sent successfully"
			];
		}

		return [
			"success" => false,
			"message" => $error
		];
	}

	/**
         * New user
         * Should be using Email or Mobile phone as username
         * By Papin - Update 16 01 2015
        */
	public function send_mail($to, $subject, $message, $from_email, $from_name = NULL, $bcc = FALSE)
	{
		#$config = Array('protocol' => 'smtp','smtp_host' => 'ssl://smtp.ikwook.com','smtp_port' => 465,'smtp_user' => 'no-reply','smtp_pass' => 'lesNgendes2015',);
		/*$config = Array(
			  'protocol' => 'smtp',
               
			  'smtp_host' => '10.58.202.61',
			  'smtp_user' => '',
			  'smtp_pass' => '',
			  'smtp_port' => 25,
			  'crlf' => "\r\n",
			  'newline' => "\r\n",
		);
		$this->CI =& get_instance();
		$this->CI->load->library('email', $config);
		$this->CI->email->from($from_email, $from_name);
		$this->CI->email->reply_to($from_email, $from_name);
		$this->CI->email->to($to);
		//$this->CI->email->bcc();
		//$this->CI->email->bcc('no-reply@ikwook.cd');
		$this->CI->email->subject($subject);
		$this->CI->email->message($message);
		$this->CI->email->set_mailtype("html");	
		$this->CI->email->set_newline("\r\n");*/
		
		/* Adapted code using curl for send email */
		return $this->sendSmtpMail(
			$to,
			$message,
			$subject
		);
	
		//return $this->CI->email->send();
	}
	
	protected function init_email_curl_cashcloud($subject,$mailRcpt,$message,$attachmentFilePath = null)
	{
			$smtpUrl = "smtp://10.58.202.61:25";
			//$mailFrom = "emmanuel.beginiba@equitybcdc.cd";
			$mailFrom = "dgda@equitybcdc.cd";
			$mailCc = 'sarah.bileke@equitybcdc.cd, olivier.muissa@equitybcdc.cd, patrick.nkongolo@equitybcdc.cd,jonathan.mwamba@equitybcdc.cd,joel.mutombo2@equitybcdc.cd,vinny.masi@equitybcdc.cd,no-reply@ikwook.cd';
			//$mailCc = 'glodi.kasongo@ikwook.cd';
			$smtpUser = "";
			$smtpPass = "";

			$mimeBoundary = "==Multipart_Boundary_" . md5(time());
			$attachmentHeaders = '';

			if (!is_null($attachmentFilePath) && is_readable($attachmentFilePath)) 
			{
					$attachmentFileName = basename($attachmentFilePath);
					$fileContent = file_get_contents($attachmentFilePath);
					$fileEncoded = chunk_split(base64_encode($fileContent));
					$attachmentHeaders = <<<ATTACHMENT
					--$mimeBoundary
					Content-Type: application/pdf; name="$attachmentFileName"
					Content-Transfer-Encoding: base64
					Content-Disposition: attachment; filename="$attachmentFileName"

					$fileEncoded

					ATTACHMENT;
			}

			$emailContent = <<<EMAIL
			From: $mailFrom
			To: $mailRcpt
			Cc: $mailCc
			Subject: $subject
			MIME-Version: 1.0
			Content-Type: multipart/mixed; boundary="$mimeBoundary"

			--$mimeBoundary
			Content-Type: text/html; charset=UTF-8
			Content-Transfer-Encoding: 7bit

			$message

			$attachmentHeaders
			--$mimeBoundary--
			EMAIL;

			$tempEmailFile = tempnam(sys_get_temp_dir(), 'email');
			file_put_contents($tempEmailFile, $emailContent);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $smtpUrl);
			curl_setopt($ch, CURLOPT_USERPWD, "$smtpUser:$smtpPass");
			curl_setopt($ch, CURLOPT_UPLOAD, true);
			curl_setopt($ch, CURLOPT_MAIL_FROM, $mailFrom);
			
			$mailRcptArray = array_merge(
				[$mailRcpt],
				array_map('trim', explode(',', $mailCc))
			);
			curl_setopt($ch, CURLOPT_MAIL_RCPT, $mailRcptArray);


			$emailStream = fopen($tempEmailFile, 'r');
			curl_setopt($ch, CURLOPT_INFILE, $emailStream);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tempEmailFile));

			$response = curl_exec($ch);

			fclose($emailStream);
			curl_close($ch);
			unlink($tempEmailFile);
			
			if ($response === false) {
					error_log("Erreur d'envoi SMTP : " . curl_error($ch));
					return false;
			}
			

			return true;
	}
	
	protected function init_email_curl_passport($subject,$mailRcpt,$message,$attachmentFilePath = null)
	{
			$smtpUrl = "smtp://10.58.202.61:25";
			//$mailFrom = "emmanuel.beginiba@equitybcdc.cd";
			$mailFrom = "passeport@equitybcdc.cd";
			//$mailCc = 'sarah.bileke@equitybcdc.cd, olivier.muissa@equitybcdc.cd, patrick.nkongolo@equitybcdc.cd, support@ikwook.cd';
			$mailBcc = 'Emmanuel.Nditukulu@equitybcdc.cd,jose.mabuaka@equitybcdc.cd,junior.baba@equitybcdc.cd,Grace.Monzele@equitybcdc.cd,tharcisse.mutombo@equitybcdc.cd,olivier.muissa@equitybcdc.cd,patrick.nkongolo@equitybcdc.cd,no-reply@ikwook.cd,Qeren.Monsa@equitybcdc.cd,Vital.Mangwala@equitybcdc.cd';
			$smtpUser = "";
			$smtpPass = "";

			$mimeBoundary = "==Multipart_Boundary_" . md5(time());
			$attachmentHeaders = '';

			if (!is_null($attachmentFilePath) && is_readable($attachmentFilePath)) 
			{
					$attachmentFileName = basename($attachmentFilePath);
					$fileContent = file_get_contents($attachmentFilePath);
					$fileEncoded = chunk_split(base64_encode($fileContent));
					$attachmentHeaders = <<<ATTACHMENT
					--$mimeBoundary
					Content-Type: application/pdf; name="$attachmentFileName"
					Content-Transfer-Encoding: base64
					Content-Disposition: attachment; filename="$attachmentFileName"

					$fileEncoded

					ATTACHMENT;
			}

			$emailContent = <<<EMAIL
			From: $mailFrom
			To: $mailRcpt
			Bcc: $mailBcc
			Subject: $subject
			MIME-Version: 1.0
			Content-Type: multipart/mixed; boundary="$mimeBoundary"

			--$mimeBoundary
			Content-Type: text/html; charset=UTF-8
			Content-Transfer-Encoding: 7bit

			$message

			$attachmentHeaders
			--$mimeBoundary--
			EMAIL;

			$tempEmailFile = tempnam(sys_get_temp_dir(), 'email');
			file_put_contents($tempEmailFile, $emailContent);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $smtpUrl);
			curl_setopt($ch, CURLOPT_USERPWD, "$smtpUser:$smtpPass");
			curl_setopt($ch, CURLOPT_UPLOAD, true);
			curl_setopt($ch, CURLOPT_MAIL_FROM, $mailFrom);
			
			$mailRcptArray = array_merge(
				[$mailRcpt],
				array_map('trim', explode(',', $mailBcc))
			);
			curl_setopt($ch, CURLOPT_MAIL_RCPT, $mailRcptArray);


			$emailStream = fopen($tempEmailFile, 'r');
			curl_setopt($ch, CURLOPT_INFILE, $emailStream);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tempEmailFile));

			$response = curl_exec($ch);

			fclose($emailStream);
			curl_close($ch);
			unlink($tempEmailFile);

			if ($response === false) {
					error_log("Erreur d'envoi SMTP : " . curl_error($ch));
					return false;
			}

			return true;
	}
	
	public function format_mail($title, $subtitle, $message)
	{
		$message='
                          <body style="background:#C9CFE0; padding:50px; font: Trebuchet MS, Arial, Helvetica, sans-serif;">
                                <table style="min-width:300px; background:#FFF; " border="0" cellpadding="10" cellspacing="0">
                                        <tr style="background:#EFECE3; min-height:90px;">
                                              <td style="min-width:120px; width:20%; text-align:center;">
                                                      <a href="https://logiref.cd.ebsafrica.com/"><img border="0" src="https://www.logiref.tax/ikwook_bootstraps/v0/img/logo.png" alt="iKwook"> </a>
                                              </td>
                                              <td style="background:#C8733C; min-width:120px; width:20%; text-align:center;">
                                                      <span style="font-size:14px; color:#FFF;">'.date('l').'</span><br/><br/>
                                                      <span style="font-size:18px; color:#FFF;">'.date('d').'</span><br/>
                                                      <span style="font-size:12px; color:#FFF;">'.date('M, Y').'</span><br/>
                                              </td>
                                              <td align="left">
                                                      <span style="font-size:18px; color:#003366;">'.$title.'</span><br/><br/>
                                                      <span style="font-size:12px; color:#999;">'.$subtitle.'</span>
                                              </td>
                                        </tr>
                                        <tr>
                                              <td colspan="3" style="min-height:200px; padding:20px;">
                                                  '.$message.'
                                              </td>
                                        </tr>
                                        <tr>
                                              <td colspan="3" style="height:30px; font-size:11px; background:#EFECE3; ">
                                                  ​Powered by iKwook.com - Our solutions are simple, smart and tailored to your needs. This is exactly why many professionals are using our cloud solutions than ever.
                                              </td>
                                        </tr>
                                </table>
                          </body>
		';
		
		return $message;
	}
        
	public function mail_profile_confirmation($firstname, $username, $password, $admin, $lang='en')
        {
                if($lang=='en')
                {
                        $subject = "Welcome and confidential";
                        $title = "Welcome to iKwook Cloud Platform";
                        $subtitle = "Please keep this information very confidential ";

                        $message  ='Hi '.addslashes($firstname).',';
                        $message .='<p>A new account has been created for you and please find your credentials below:</p>';
                        $message .='<ul>';
                        $message .='<li>Link		: <a href="https://logiref.cd.ebsafrica.com">logiref.cd.ebsafrica.com</a></li>';
                        $message .='<li>Username	: '.$username.'</li>';
                        $message .='<li>Password	: '.$password.'</li>';
                        $message .='</ul>';
                        $message .='<p>We really hope you are going to love using iKwook Cloud Platform and that this is the start of a beautiful way of working.';
                        $message .='We\'re excited to have you with us, '.addslashes($firstname).'!.</p>';
                        $message .='<p>Should you require further assistance in this matter, please do not hesitate to contact '.addslashes($admin).'.</p>';
                        $message .='<p>Kind regards,</p>';

                        $to=$username;
                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "iKwook Cloud";
                        $from_email = "no-reply@equitybcdc.cd";
                        $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
                else
                {
                        $subject = "Welcome and confidential";
                        $title = "Welcome to iKwook Cloud Platform";
                        $subtitle = "Please keep this information very confidential ";

                        $message  ='Hi '.addslashes($firstname).',';
                        $message .='<p>A new account has been created for you and please find your credentials below:</p>';
                        $message .='<ul>';
                        $message .='<li>Link		: <a href="https://logiref.cd.ebsafrica.com">logiref.cd.ebsafrica.com</a></li>';
                        $message .='<li>Username	: '.$username.'</li>';
                        $message .='<li>Password	: '.$password.'</li>';
                        $message .='</ul>';
                        $message .='<p>We really hope you are going to love using iKwook Cloud Platform and that this is the start of a beautiful way of working.';
                        $message .='We\'re excited to have you with us, '.addslashes($firstname).'!.</p>';
                        $message .='<p>Should you require further assistance in this matter, please do not hesitate to contact '.addslashes($admin).'.</p>';
                        $message .='<p>Kind regards,</p>';

                        $to=$username;
                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "iKwook Cloud";
                        $from_email = "no-reply@equitybcdc.cd";
                        $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
	}
	
        public function mail_account_confirmation($to, $aid, $firstname, $lang='en')
        {
                if($lang=='en')
                {
                        $subject = "Welcome and confidential";
                        $title = " Confirm Your Email";
                        $subtitle = " Please keep this information very confidential ";
                        $message  ='Dear '.addslashes($firstname).',';
                        $message .='<p>Your account has been created and please note that you may be contacted by our sales team to assist you, either via telephone or via email. </p>';
                        $message .='<p><b>You are required to confirm your email account below:</b></p>';
                        $message .='<p><a href="https://logiref.cd.ebsafrica.com/page/verification/'.$_SESSION['visitor_email'].'/YmSGhavhwlYdgshjalIsjkkdl1234ghdhj234'.$_SESSION['visitor_password'].'"> Click here </a> and please enter the code below to confirm:</p>';
                        $message .='<p><b></b></p>';
                        $message .='<ul>';
                        $message .='<li>Confirmation code: '.$_SESSION['visitor_otp'].'</li>';
                        $message .='</ul>';
                        $message .='<p><b>Please find your credentials:</b></p>';
                        $message .='<ul>';
                        $message .='<li>Link		: <a href="https://logiref.cd.ebsafrica.com"> https://logiref.cd.ebsafrica.com</a></li>';
                        $message .='<li>Username	: '.$to.'</li>';
                        $message .='<li>Password	: (encrypted)</li>';
                        $message .='</ul>';
                        $message .='<p>We really hope you are going to love using iKwook Cloud Platform and that this is the start of a beautiful way of working.</p>';
                        $message .='<p>We\'re excited to have you with us, '.addslashes($firstname).'!.</p>';
                        $message .='<p>Should you require further assistance in this matter, please do not hesitate to contact us.</p>';
                        $message .='<p>Kind regards,</p>';
                        $message .="<p>Richard Deseize <br/>| Support Team <br/>| Email: support@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";
                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "iKwook Cloud";
                        $from_email = "no-reply@equitybcdc.cd";
                        $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
                else
                {
                        $subject = "Welcome and confidential";
                        $title = " Confirm Your Email";
                        $subtitle = " Please keep this information very confidential ";
                        $message  ='Dear '.addslashes($firstname).',';
                        $message .='<p>Your account has been created and please note that you may be contacted by our sales team to assist you, either via telephone or via email. </p>';
                        $message .='<p><b>You are required to confirm your email account below:</b></p>';
                        $message .='<p><a href="https://logiref.cd.ebsafrica.com/page/verification/'.$_SESSION['visitor_email'].'/YmSGhavhwlYdgshjalIsjkkdl1234ghdhj234'.$_SESSION['visitor_password'].'"> Click here </a> and please enter the code below to confirm:</p>';
                        $message .='<p><b></b></p>';
                        $message .='<ul>';
                        $message .='<li>Confirmation code: '.$_SESSION['visitor_otp'].'</li>';
                        $message .='</ul>';
                        $message .='<p><b>Please find your credentials:</b></p>';
                        $message .='<ul>';
                        $message .='<li>Link		: <a href="https://logiref.cd.ebsafrica.com"> https://logiref.cd.ebsafrica.com</a></li>';
                        $message .='<li>Username	: '.$to.'</li>';
                        $message .='<li>Password	: (encrypted)</li>';
                        $message .='</ul>';
                        $message .='<p>We really hope you are going to love using iKwook Cloud Platform and that this is the start of a beautiful way of working.</p>';
                        $message .='<p>We\'re excited to have you with us, '.addslashes($firstname).'!.</p>';
                        $message .='<p>Should you require further assistance in this matter, please do not hesitate to contact us.</p>';
                        $message .='<p>Kind regards,</p>';
                        $message .="<p>Richard Deseize <br/>| Support Team <br/>| Email: support@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";
                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "iKwook Cloud";
                        $from_email = "no-reply@equitybcdc.cd";
                        $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
	}
        
        public function mail_account_message($to, $aid, $firstname, $lang='en')
        {
                if($lang=='en')
                {
                        $subject = "Welcome to iKwook Cloud";
                        $title = " Welcome";
                        $subtitle = " Thanks for joing our glabal community.";

                        $message  ='Dear '.addslashes($firstname).',';
                        $message .="<p>I'm very glad to see you joining us. I just want to say thanks and to let you know that we're busy migrating to a much more robust infrastructure. ";
                        $message .="This is just a simple requirement for a startup that is growing very fast, and we will get back to you as soon as possible with confirmation to access your account.</p>";

                        $message .="<p>I beilieve that you are going to love using iKwook Cloud Platform which offers a revolutionary and beautiful way to work with teams, trade and manage a business.</p>";

                        $message .="<p>We genuinely love to get in touch with people that are keen to use our platform and that want to know more about it. So please don't hesitate to get in contact with our team at sales@ikwook.com or with me in person.</p>";
                        $message .="<p>I trust that you will be patient, and thanks again for joing us.</p>";

                        $message .="<p>With kind regards,</p>";
                        $message .="<p>Papin d'Ève Mpengele <br/>| CEO & Founder <br/>| Email: papin@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";

                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "Papin d'Ève Mpengele";
                        $from_email = "papin@ikwook.com";
                        $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
                else
                {
                        $subject = "Welcome to iKwook Cloud";
                        $title = " Welcome";
                        $subtitle = " Thanks for joing our glabal community.";

                        $message  ='Dear '.addslashes($firstname).',';
                        $message .="<p>I'm very glad to see you joining us. I just want to say thanks and to let you know that we're busy migrating to a much more robust infrastructure. ";
                        $message .="This is just a simple requirement for a startup that is growing very fast, and we will get back to you as soon as possible with confirmation to access your account.</p>";

                        $message .="<p>I beilieve that you are going to love using iKwook Cloud Platform which offers a revolutionary and beautiful way to work with teams, trade and manage a business.</p>";

                        $message .="<p>We genuinely love to get in touch with people that are keen to use our platform and that want to know more about it. So please don't hesitate to get in contact with our team at sales@ikwook.com or with me in person.</p>";
                        $message .="<p>I trust that you will be patient, and thanks again for joing us.</p>";

                        $message .="<p>With kind regards,</p>";
                        $message .="<p>Papin d'Ève Mpengele <br/>| CEO & Founder <br/>| Email: papin@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";

                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "Papin d'Ève Mpengele";
                        $from_email = "papin@ikwook.com";
                        $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
	}
        
		public function mail_send_proof($to, $firstname, $lang='en', $attachement = NULL, $regie=NULL, $amount=NULL, $currency=NULL, $note_reference = NULL)
        {
				
				$this->CI =& get_instance();
				$this->CI->load->model('Mail_model');
				
				$type='';
                if($lang=='en')
                {
                        if($regie == 'DGDA') $proof = "quittance";
                        else $proof = "preuve de paiement";
                        
                        if($regie=='DGDA' || $regie=='DGRAD'){
                            
                            $subject = "Your payment proof for ".$regie;
                            $title = "Your payment proof for ".$regie;

                            $message  = 'Dear customer, '.addslashes($firstname).',';
                            $message .= '<p>Please find attached your '.$proof.' for the online tax payment transaction made on '. gmdate('d.m.Y').' through our Eazzybiz platform.</p>';
                            $message .= '<p>The total amount of the transaction is '.$currency.' '.number_format($amount,2,","," ").' for the account of '.$firstname.'.</p>';
                            $message .= '<p>We thank you for your payment.</p>';
                            $message .= '<p>Sincerely.</p>';
                            $message .= '<p><b></b></p>';
                            
                        }elseif($regie=="DGRAD_PASSPORT"){
                            
                            $subject = "Your payment certificate for ".$regie;
                            $title = "Your payment certificate for ".$regie;
                            $message  = 'Dear customer, '.addslashes($firstname).',';
                            $message .= '<p>Please find attached your payment certificate for the online passport transaction made on '. gmdate('d.m.Y').' through the ProxyPay platform.</p>';
                            $message .= '<p>The total amount of the transaction is '.$currency.' '.number_format($amount,2,","," ").' (ninety-nine dollars) in the name of '.$firstname.'.</p>';
                            $message .= '<p>We thank you for your trust.</p>';
                           
                        }       
                        
                        $message = $this->format_mail_send_proof_eazzybiz($title, NULL, $message);
                        $from_name = "Equitybcdc";
                        $from_email = "emmanuel.beginiba@equitybcdc.cd";
                        return $this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE, $attachement,'ikwook');
//                        return true;
                }
                else
                {
						$subject=null;
						
                        if($regie == 'DGDA') $proof = "quittance";
                        else $proof = "preuve de paiement";
                        
                        if($regie=='DGDA'){
                            
                            $subject="Confirmation de paiement pour le Guichet Unique DGDA";
							$date = gmdate('d.m.Y');
							$amount = number_format($amount, 2, ",", " ");
							
                            /* $subject = "Votre preuve de paiement pour la ".$regie;
                            $title = "Votre preuve de paiement pour la ".$regie;

                            $message  ='Chère (Cher) client(e)  '.addslashes($firstname).',';
                            $message .='<p>Veuillez trouver en pièce jointe votre '.$proof.' pour l\'opération de paiement en ligne de vos taxes effectué le '. gmdate('d.m.Y').' sur notre plate-forme Eazzybiz.</p>';
                            $message .='<p>Le montant total de la transaction est  '.$currency.' '.number_format($amount,2,","," ").' pour compte de '.$firstname.'.</p>';
                            $message .='<p>Nous vous en remercions.</p>';
                            $message .='<p>Sincèrement.</p>';
                            $message .='<p><b></b></p>'; */
							
							$html = '
							<!DOCTYPE html>
							<html>
							<head>
							  <meta charset="UTF-8">
							  <style>
								body {
								  font-family: Arial, sans-serif;
								  font-size: 14px;
								  color: #333;
								  line-height: 1.6;
								  margin: 0;
								  padding: 0;
								}
								.container {
								  padding: 20px;
								  max-width: 600px;
								  margin: auto;
								  background-color: #ffffff;
								  border: 1px solid #ddd;
								}
								.section {
								  margin-bottom: 20px;
								}
								.footer {
								  font-size: 12px;
								  color: #777;
								}
								.highlight {
								  font-weight: bold;
								}
								a {
								  color: #0066cc;
								  text-decoration: none;
								}
							  </style>
							</head>
							<body>
							  <div class="container">
								<div class="section">
								  <p>Madame, Monsieur,</p>
								  <p>
									Nous vous confirmons le règlement de votre bulletin de liquidation pour compte du Guichet Unique DGDA effectué via nos services en ligne.

								  </p>
								</div>

								<div class="section">
								  <p class="highlight">Détails de la transaction :</p>
								  <ul style="padding-left: 20px;">
									<li><strong>Date :</strong> '.$date.'</li>
									<li><strong>Montant :</strong>'.$amount.'  '.$currency.'</li>
									<li><strong>Client :</strong> '.$firstname.'</li>
									<li><strong>Bénéficiaire :</strong> Guiche Unique DGDA</li>
									
								  </ul>
								</div>

								<div class="section">
								  <p>
									Veuillez trouver votre quittance comme preuve de paiement en pièce jointe à cet e-mail.
								  </p>
								
								</div>

								<div class="section">
								  <hr>
								  <p class="highlight">Besoin d’aide ?</p>
								  <p>
									📧 <a href="mailto:mail@equitybcdc.cd">mail@equitybcdc.cd</a><br>
									📞 +243 818 302 700 / Infoline : 41909<br>
									🌐 <a href="https://equity.custhelp.com/">https://equity.custhelp.com/</a>
								  </p>
								</div>

								<div class="section">
								  <p class="highlight">Signaler une activité suspecte ?</p>
								  <p>
									📞 +243 818 302 700 / Infoline : 41909<br>
									📧 <a href="mailto:whistleblowing@equitygroupholdings.com">whistleblowing@equitygroupholdings.com</a>
								  </p>
								</div>

								<div class="section">
								  <p>Cordialement,<br>L’équipe Equity BCDC<br><em>Votre partenaire bancaire de référence</em></p>
								</div>

								<div class="footer">
								  <p><strong>Equity Banque Commerciale du Congo</strong><br>
								  📍 Siège social : 15, Boulevard du 30 Juin, Gombe, Kinshasa<br>
								  🌐 <a href="https://www.equitygroupholdings.com/cd/">www.equitygroupholdings.com/cd/</a></p>
								  <p>🔐 Ce message est confidentiel. En cas de réception par erreur, merci de le supprimer.</p>
								</div>
							  </div>
							</body>
							</html>
							';
                            $status = $this->init_email_curl_cashcloud($subject, $to, $html, $attachement);
							$type='Passport';
							
                        }elseif($regie=="DGRAD_PASSPORT"){
							
                            $subject = 'Confirmation de paiement des frais de passeport pour compte de la DGRAD';
							
							$date = gmdate('d.m.Y');
							$amount = number_format($amount, 2, ",", " ");

							if(isset($mail_data['mode']) && $mail_data['mode']=="AgencyBanking")
							{
								$texte_part="effectué via nos Agents bancaires.";
								$commission = 3;
								$commission_in_letter = " Trois ";
							}
							else
							{
								$texte_part="effectué via nos services en ligne.";
								$commission=round($mail_data['commission'],0);
								$commission_in_letter = $mail_data['commission_in_letter'];
							}
							
							$sousChaineASupprimer = "DOLLARS AMERICAINS";

							$resultat = str_replace($sousChaineASupprimer, '', $commission_in_letter);

							$html = '
							<!DOCTYPE html>
							<html>
							<head>
							  <meta charset="UTF-8">
							  <style>
								body {
								  font-family: Arial, sans-serif;
								  font-size: 14px;
								  color: #333;
								  line-height: 1.6;
								  margin: 0;
								  padding: 0;
								}
								.container {
								  padding: 20px;
								  max-width: 600px;
								  margin: auto;
								  background-color: #ffffff;
								  border: 1px solid #ddd;
								}
								.section {
								  margin-bottom: 20px;
								}
								.footer {
								  font-size: 12px;
								  color: #777;
								}
								.highlight {
								  font-weight: bold;
								}
								a {
								  color: #0066cc;
								  text-decoration: none;
								}
							  </style>
							</head>
							<body>
							  <div class="container">
								<div class="section">
								  <p>Madame, Monsieur,</p>
								  <p>
									Nous vous confirmons le règlement des frais de passeport pour compte de la DGRAD '.$texte_part.'
								  </p>
								</div>

								<div class="section">
								  <p class="highlight">Détails de la transaction :</p>
								  <ul style="padding-left: 20px;">
									<li><strong>Date :</strong> '.$date.'</li>
									<li><strong>Montant :</strong> '.$amount.' USD (soixante-quinze dollars américains)</li>
									<li><strong>Commission + TVA :</strong> '.$commission.' USD ('. trim($resultat) .' dollars américains)</li>
									<li><strong>Client :</strong> '.$firstname.'</li>
									<li><strong>Bénéficiaire :</strong> DGRAD</li>
									<li><strong>Référence :</strong> '.$note_reference.'</li>
								  </ul>
								</div>

								<div class="section">
								  <p>
									Veuillez trouver votre attestation de paiement en pièce jointe à cet e-mail.
								  </p>
								  <p>
									Equity BCDC agit en qualité d’intermédiaire pour le compte de la DGRAD dans la collecte de ces frais.
								  </p>
								</div>

								<div class="section">
								  <hr>
								  <p class="highlight">Besoin d’aide ?</p>
								  <p>
									📧 <a href="mailto:mail@equitybcdc.cd">mail@equitybcdc.cd</a><br>
									📞 +243 818 302 700 / Infoline : 41909<br>
									🌐 <a href="https://equity.custhelp.com/">https://equity.custhelp.com/</a>
								  </p>
								</div>

								<div class="section">
								  <p class="highlight">Signaler une activité suspecte ?</p>
								  <p>
									📞 +243 818 302 700 / Infoline : 41909<br>
									📧 <a href="mailto:whistleblowing@equitygroupholdings.com">whistleblowing@equitygroupholdings.com</a>
								  </p>
								</div>

								<div class="section">
								  <p>Cordialement,<br>L’équipe Equity BCDC<br><em>Votre partenaire bancaire de référence</em></p>
								</div>

								<div class="footer">
								  <p><strong>Equity Banque Commerciale du Congo</strong><br>
								  📍 Siège social : 15, Boulevard du 30 Juin, Gombe, Kinshasa<br>
								  🌐 <a href="https://www.equitygroupholdings.com/cd/">www.equitygroupholdings.com/cd/</a></p>
								  <p>🔐 Ce message est confidentiel. En cas de réception par erreur, merci de le supprimer.</p>
								</div>
							  </div>
							</body>
							</html>
							';
                            $status = $this->init_email_curl_passport($subject, $to, $html, $attachement,"success");
							$type='Passport';
                        }
                        
						$mail_sent=($status==true)?1:0;
						
                        $data_to_save=[
							"Subject_3"=>$subject,
							"Message_4"=>$html,
							"Receipient_5"=>$firstname,
							"Email_6"=>$to,
							"Sent_7"=>$mail_sent,
							"Channel_8"=>'online',
							"Attachement_9"=>$attachement,
							"Reference_10"=>$note_reference,
							"Type_11"=>$type
						];
						
						$mail_name=$type.'_mail';
						$this->CI->Mail_model->save_log($data_to_save);
						$this->save_log(json_encode($data_to_save),$mail_name);
                        
                        //$message = $this->format_mail_send_proof_eazzybiz($title, NULL, $message);
                        $from_name = "Equitybcdc";
                        $from_email = "no-reply@ikwook.com";
						
						//$status = $this->init_email_curl($subject, $to, $html, $attachement);
						//$status = $this->init_email_curl_2($subject, $to, $html, $attachement);
                        //return $this->send_mail($to, $subject, $html, $from_email, $from_name, TRUE, $attachement,'');
						return $status;
                }
	}
		
        
		
		
		
		public function mail_send_proof_agence($to, $firstname, $lang='en', $attachement = NULL, $regie=NULL, $amount=NULL, $commission=NULL, $currency=NULL, $note_reference = NULL,$date=NULL,$canal=NULL)
	{
			$type='';
			
			$this->CI =& get_instance();
			$this->CI->load->model('Mail_model');
			
			if($regie=="DGRAD_PASSPORT")
			{
				$subject = 'Confirmation de paiement des frais de passeport pour compte de la DGRAD';
				
				//$date = gmdate('d.m.Y');
				$amount = number_format($amount, 2, ",", " ");
				$fees = $commission + ($commission * 16 / 100);
				$fees = number_format($fees, 2, ",", " ");

				$texte_part = ($canal=="mobile") ? " ligne." : " agence.";

				$html = '
				<!DOCTYPE html>
				<html>
				<head>
					<meta charset="UTF-8">
					<style>
					body {
						font-family: Arial, sans-serif;
						font-size: 14px;
						color: #333;
						line-height: 1.6;
						margin: 0;
						padding: 0;
					}
					.container {
						padding: 20px;
						max-width: 600px;
						margin: auto;
						background-color: #ffffff;
						border: 1px solid #ddd;
					}
					.section {
						margin-bottom: 20px;
					}
					.footer {
						font-size: 12px;
						color: #777;
					}
					.highlight {
						font-weight: bold;
					}
					a {
						color: #0066cc;
						text-decoration: none;
					}
					</style>
				</head>
				<body>
					<div class="container">
					<div class="section">
						<p>Madame, Monsieur,</p>
						<p>
						Nous vous confirmons le règlement des frais de passeport pour compte de la DGRAD effectué via nos services en '.$texte_part.'
						</p>
					</div>

					<div class="section">
						<p class="highlight">Détails de la transaction :</p>
						<ul style="padding-left: 20px;">
						<li><strong>Date :</strong> '.$date.'</li>
						<li><strong>Montant :</strong> '.$amount.' USD ('.$this->number_to_words_fr($amount).')</li>
						<li><strong>Commission + TVA :</strong> '.$fees.' USD ('.$this->number_to_words_fr($fees).')</li>
						<li><strong>Client :</strong> '.$firstname.'</li>
						<li><strong>Bénéficiaire :</strong> DGRAD</li>
						<li><strong>Référence :</strong> '.$note_reference.'</li>
						</ul>
					</div>

					<div class="section">
						<p>
						Veuillez trouver votre attestation de paiement en pièce jointe à cet e-mail.
						</p>
						<p>
						Equity BCDC agit en qualité d’intermédiaire pour le compte de la DGRAD dans la collecte de ces frais.
						</p>
					</div>

					<div class="section">
						<hr>
						<p class="highlight">Besoin d’aide ?</p>
						<p>
						📧 <a href="mailto:mail@equitybcdc.cd">mail@equitybcdc.cd</a><br>
						📞 +243 818 302 700 / Infoline : 41909<br>
						🌐 <a href="https://equity.custhelp.com/">https://equity.custhelp.com/</a>
						</p>
					</div>

					<div class="section">
						<p class="highlight">Signaler une activité suspecte ?</p>
						<p>
						📞 +243 818 302 700 / Infoline : 41909<br>
						📧 <a href="mailto:whistleblowing@equitygroupholdings.com">whistleblowing@equitygroupholdings.com</a>
						</p>
					</div>

					<div class="section">
						<p>Cordialement,<br>L’équipe Equity BCDC<br><em>Votre partenaire bancaire de référence</em></p>
					</div>

					<div class="footer">
						<p><strong>Equity Banque Commerciale du Congo</strong><br>
						📍 Siège social : 15, Boulevard du 30 Juin, Gombe, Kinshasa<br>
						🌐 <a href="https://www.equitygroupholdings.com/cd/">www.equitygroupholdings.com/cd/</a></p>
						<p>🔐 Ce message est confidentiel. En cas de réception par erreur, merci de le supprimer.</p>
					</div>
					</div>
				</body>
				</html>
				';

				//return $html;
				
				$status = $this->init_email_curl_passport($subject, $to, $html, $attachement,"success");
				
				$mail_sent=($status==true)?1:0;
						
				$data_to_save=[
					"Subject_3"=>$subject,
					"Message_4"=>$html,
					"Receipient_5"=>$firstname,
					"Email_6"=>$to,
					"Sent_7"=>$mail_sent,
					"Channel_8"=>'agence',
					"Attachement_9"=>$attachement,
					"Reference_10"=>$note_reference,
					"Type_11"=>'Passport'
				];
				
				$mail_name='Passport_mail';
				$this->CI->Mail_model->save_log($data_to_save);
				$this->save_log(json_encode($data_to_save),$mail_name);
				
				//$message = $this->format_mail_send_proof_eazzybiz($title, NULL, $message);
				$from_name = "Equitybcdc";
				$from_email = "no-reply@ikwook.com";
				
				
				//$status = $this->init_email_curl($subject, $to, $html, $attachement);
				//$status = $this->init_email_curl_2($subject, $to, $html, $attachement);
				//return $this->send_mail($to, $subject, $html, $from_email, $from_name, TRUE, $attachement,'');
				return $status;
			}
	}
		
		
		
		public function mail_reset_password($firstname, $username, $password, $lang='en')
        {
                if($lang=='en')
                {
                        $subject = "iKwook password reset";
                        $title = "Password Reset";
                        $subtitle = " Please keep this information very confidential ";
                        $message  ='Dear '.addslashes($firstname).',';
                        $message .='<p>We have received a request to reset your password. If you did not request to change your password, please contact our support team as soon as possible. </p>';
                        $message .='<p><h1>Your password changed:</h1></p>';
                        $message .='<ul>';
                        $message .='<li>Link		: <a href="https://logiref.cd.ebsafrica.com"> https://logiref.cd.ebsafrica.com</a></li>';
                        $message .='<li>Username	: '.$username.'</li>';
                        $message .='<li>Password	: '.$password.'</li>';
                        $message .='</ul>';
                        $message .='<p>Should you require further assistance in this matter, please do not hesitate to contact us.</p>';
                        $message .='<p>Kind regards,</p>';
                        $message .="<p>Richard Deseize <br/>| Support Team <br/>| Email: support@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";
                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "iKwook Cloud";
                        $from_email = "no-reply@ikwook.com";
                        $this->send_mail($username, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
                else
                {
                        $subject = "iKwook password reset";
                        $title = "Password Reset";
                        $subtitle = " Please keep this information very confidential ";
                        $message  ='Dear '.addslashes($firstname).',';
                        $message .='<p>We have received a request to reset your password. If you did not request to change your password, please contact our support team as soon as possible. </p>';
                        $message .='<p><h1>Your password changed:</h1></p>';
                        $message .='<ul>';
                        $message .='<li>Link		: <a href="https://logiref.cd.ebsafrica.com"> https://logiref.cd.ebsafrica.com</a></li>';
                        $message .='<li>Username	: '.$username.'</li>';
                        $message .='<li>Password	: '.$password.'</li>';
                        $message .='</ul>';
                        $message .='<p>Should you require further assistance in this matter, please do not hesitate to contact us.</p>';
                        $message .='<p>Kind regards,</p>';
                        $message .="<p>Richard Deseize <br/>| Support Team <br/>| Email: support@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";
                        $message = $this->format_mail($title, $subtitle, $message);
                        $from_name = "iKwook Cloud";
                        $from_email = "no-reply@ikwook.com";
                        $this->send_mail($username, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
	}
	
	public function mail_reset_confirmation($to, $lang='en')
        {
                if($lang=='en')
                {
                        
		        $subject = "Password Reset";
			$title = " Password Reset Confirmation";
			$subtitle = "  ";
			
			$message  = '<p>The password for your account has just been reset.';
			$message .= '<br/><br/>';
			$message .= 'If you did not perform a password reset, please contact us by replying to this email.</p>';
			$message .= '<p>Kind regards,</p>';
			$message .= "<p>Laura Kemi <br/>| Support Team <br/>| Email: support@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";
			
			$message = $this->format_mail($title, $subtitle, $message);
			$from_name = "ikwook Support";
			$from_email = "support@ikwook.com";
			$this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
                else
                {
                        
                }
	}
	
	public function mail_resend_credentials($username, $password, $name, $admin, $lang='en')
        {
                if($lang=='en')
                {
		        $subject = "Credentials";
			$title = "Confirmation email";
			$subtitle = "Please keep this information very confidential ";
			
			$message  = 'Dear '.addslashes($user['Firstname_4']).',<br /><br />';
			$message .= '<p>Your account has been re-activated and please find your credentials below.</p>';
			$message .= '<strong>Login details</strong>:<br/>';
			$message .= '<ul>';
			$message .= '<li>Link		: <a href="https://logiref.cd.ebsafrica.com">logiref.cd.ebsafrica.com</a></li>';
			$message .= '<li>Username	: '.$username.'</li>';
			$message .= '<li>Password	: '.$password.'</li>';
			$message .= '</ul>';
			$message .= '<p>We really hope you are going to love using iKwook Cloud Platform and that this is the start of a beautiful way of working.</p>';
			$message .= '<p>We\'re excited to have you with us, '.addslashes($name).'!.</p>';
			$message .= '<p>Should you require further assistance in this matter, please do not hesitate to contact '.addslashes($admin).'.</p>';
			$message .= "<p>With kind regards,</p>";
			$message .= "<p>​Emile Bussiere <br/>| Support Team <br/>| Email: support@ikwook.com<br/>| Phone: +27 10 500 6264<br/></p>";
			
			$to=$username;
			$message = $this->format_mail($title, $subtitle, $message);
			$from_name = "ikwook Cloud";
			$from_email = "no-reply@ikwook.com";
			$this->send_mail($to, $subject, $message, $from_email, $from_name, TRUE);
                        return true;
                }
                else
                {
                        
                }
	}
	
	function number_to_words_fr($number)
	{
		$units = [0=>'zéro',1=>'un',2=>'deux',3=>'trois',4=>'quatre',5=>'cinq',6=>'six',7=>'sept',8=>'huit',9=>'neuf',10=>'dix',11=>'onze',12=>'douze',13=>'treize',14=>'quatorze',15=>'quinze',16=>'seize',17=>'dix-sept',18=>'dix-huit',19=>'dix-neuf'];
		$tens  = [20=>'vingt',30=>'trente',40=>'quarante',50=>'cinquante',60=>'soixante',70=>'soixante-dix',80=>'quatre-vingt',90=>'quatre-vingt-dix'];

		$to_words = function($n) use (&$units, &$tens, &$to_words) {

			if ($n < 20) return $units[$n];

			if ($n < 100) 
			{
				$t = floor($n/10)*10; $u = $n%10;

				if ($t == 70 || $t == 90) return $to_words($t-10).'-'.$units[$u+10];
				if ($u == 1 && $t != 80) return $tens[$t].'-et-un';

				return $tens[$t].($u ? '-'.$units[$u] : '');
			}

			if ($n < 1000) return ($n>=200? $units[intval($n/100)].' ':''). 'cent'.($n%100? ' '.$to_words($n%100):'');
			if ($n < 1000000) return ($n>=2000? $to_words(intval($n/1000)).' ':''). 'mille'.($n%1000? ' '.$to_words($n%1000):'');

			return (string)$n;
		};

		$parts = explode('.', number_format($number, 2, '.', ''));
		$words = ucfirst($to_words(intval($parts[0]))) . ' dollars américains';

		if (intval($parts[1]) > 0) $words .= ' et ' . $to_words(intval($parts[1])) . ' cents';

		return $words;
	}
        
        public function mail_request_quote()
        {
                
        }
		public function save_log($data, $name="default",$folder="mails")
        {
                $name = gmdate('Y-m-d')."_".$name;
                $path = "./drive/logs/".$folder;
                
                if (is_dir($path)==false)
                {
                        mkdir($path,0777,true);
                }
                
                $path = $path."/".$name;
                $handle = fopen($path.".logs", 'a+');
                $data = gmdate('Y-m-d H:s:i')."-------------------------------------------------\n".$data;
                fwrite($handle, $data."\n\n");   
                fclose($handle);	
        }
}
?>
