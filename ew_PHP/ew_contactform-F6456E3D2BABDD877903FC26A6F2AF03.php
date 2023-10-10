<?php /* Copyright 2017-2023 RAGE Software Inc. All Rights Reserved */ 
if (!isset($_POST)){
header("HTTP/1.1 404 File Not Found", 404); exit;
}
if (count($_POST) < 2) { header("HTTP/1.1 404 File Not Found", 404); exit;
}
if (!function_exists('json_encode') || version_compare(phpversion(), '5.6', '<')) {  
 echo '{"success":false,"message":"PHP 5.6 or later is required to use the Contact Form. Please contact your web hosting provider to update your version of PHP"}';
 exit;
}
include('ew_mailer.php');
function json($data) {
if (!function_exists('json_encode')) {
echo '{"success":false,"message":"PHP 5.6 or later is required to use the Contact Form. Please contact your web hosting provider to update your version of PHP"}';
} else {
echo json_encode($data);
}
}
function formatBytes($bytes, $precision = 2) { 
 $units = array('Bytes', 'KB', 'MB', 'GB', 'TB'); 
 $bytes = max($bytes, 0); 
 $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
 $pow = min($pow, count($units) - 1); 
 $bytes /= pow(1024, $pow);
 return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
function return_bytes($val) {
 $val = trim($val);
 $last = strtolower($val[strlen($val)-1]);
 if ($last === "b" ) {
 $last = strtolower($val[strlen($val)-2]);
 }
 $val = preg_replace("/[^0-9.]/", "", $val);
 switch($last) {
  case 'g':
 case 'gb':
 $val *= 1024;
 case 'm':
 case 'mb':
 $val *= 1024;
 case 'k':
 case 'kb':
 $val *= 1024;
 }
 return $val;
}
function get_ip_address() {
 if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP']))
 return $_SERVER['HTTP_CLIENT_IP'];
 if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
 $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
 foreach ($iplist as $ip) {
 if (validate_ip($ip))
 return $ip;
 }
 }
  if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
 return $_SERVER['HTTP_X_FORWARDED'];
  if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
 return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
  if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
 return $_SERVER['HTTP_FORWARDED_FOR'];
  if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
 return $_SERVER['HTTP_FORWARDED'];
 return $_SERVER['REMOTE_ADDR'];
 }
function validate_ip($ip) {
 if (filter_var($ip, FILTER_VALIDATE_IP, 
 FILTER_FLAG_IPV4 | 
 FILTER_FLAG_IPV6 |
 FILTER_FLAG_NO_PRIV_RANGE | 
 FILTER_FLAG_NO_RES_RANGE) === false)
 return false;
 }
$newline="\r\n";
$emailContent = '';
$emailSender = '';
$emailSubject = $_POST['emailSubject'];
if (isset($_POST['email'])) {
$emailSender = $_POST['email'];
}
if ($emailSender == "") {
$emailSender = $_POST['emailSender'];
}
$check_required_size = isset($_POST['required_size']) ? $_POST['required_size'] : FALSE;
$file_url = "";
$customer_code = "";
$form_code = "AE5B295B3FA947AFA72D8F36687C4B28A9F44D89E9DF4683B0CDE6949CF5F399";
$form_name = "My Contact Form";
$emailRecipient = "CWPACollective@gmail.com";
$submission_id = "";
$strEmailAttachments = "";
$doSendConfirmation = false;
$confirmationEmail = "";
$strConfirmationSubject = ""; 

if ($customer_code!="") {
if (isset($_POST['required_size'])) {
$required_size = $_POST['required_size'];
$numFiles = $_POST['numfiles'];if ($numFiles > 0) {$intMemLimit = return_bytes(ini_get('memory_limit'));
$intPostMaxSize = return_bytes(ini_get('post_max_size'));
$intMaxFileLimit = return_bytes(ini_get('max_file_uploads'));
$intMaxEachFileSize = return_bytes(ini_get('upload_max_filesize'));$intMaxTotalFileSizeAllowed = min($intMemLimit,$intPostMaxSize);if (isset( $_SERVER['CONTENT_LENGTH'])) {
$intMaxTotalFileSizeAllowed = $intMaxTotalFileSizeAllowed - $_SERVER['CONTENT_LENGTH'];
} else {
$intMaxTotalFileSizeAllowed = $intMaxTotalFileSizeAllowed - 5000;
}if ($numFiles > $intMaxFileLimit ) {
json(['success' => FALSE, 'message' => "Too many files are being sent. The maximum number of files that can be sent is " . $intMaxFileLimit . "."]);
exit;}
if ($numFiles == 1 && $required_size > $intMaxEachFileSize) {
json(['success' => FALSE, 'message' => "The maximum file size you can send is " . formatBytes($intMaxEachFileSize) . " and you are trying to send " . formatBytes($required_size) . "." ]);
exit;}if ($required_size > $intMaxTotalFileSizeAllowed) {
json(['success' => FALSE, 'message' => "The size of the files being uploaded is too large. You are sending " . formatBytes($required_size) . " and the maximum total file size is " . formatBytes($intMaxTotalFileSizeAllowed) ." while each file must be less than " . formatBytes($intMaxEachFileSize)]);
exit;
}
}
}
}
if ($emailRecipient == '') {
json(['success' => FALSE, 'message' => "No email address specified to send mail to."]);
 exit;
}
if (!filter_var($emailRecipient, FILTER_VALIDATE_EMAIL)) {
 json(['success' => FALSE, 'message' => "Not a valid email address to send to."]);
 exit;
}
if ($emailSender == "") {
$emailSender = "CWPACollective@gmail.com"; }
$remove = ["*",";","\\","}","{","[","]","+","%"];
$replace_with = [];
$emailSubject = str_replace($remove, $replace_with, $emailSubject);
$emailSubject = trim($emailSubject);
if ($emailSubject == "") {
$emailSubject = "A message from your website"; }
if(strlen($emailSubject) > 300) {
$emailSubject = substr($emailSubject, 0, 300);
}
if (!filter_var($emailSender, FILTER_VALIDATE_EMAIL)) {
json(['success' => FALSE, 'message' => "Sender is not a valid email address."]);
exit;
}
if ($check_required_size !== FALSE) { if (isset($_POST['g-recaptcha-response'])) {
$recaptcha_key = ""; $recaptcha = new EverWeb_ReCaptcha($recaptcha_key);
$captchaField = ($_POST['g-recaptcha-response']);
if (!$recaptcha->verify($captchaField)) {
json(['success' => FALSE, 'message' => "The captcha is not valid. ". $captchaField]);
exit;
}
}
}
if (isset($_POST['reqFields'])) {
$required_fields = $_POST['reqFields'];
foreach ($_POST as $key=>$value) {
if (in_array($key, $required_fields) && trim($value) == '') {
json(['success' => FALSE, 'message' => "Please complete all required fields."]);
exit;
}
}
foreach ($_FILES as $key=>$file) {
if (in_array($key, $required_fields) && $file['error'] == UPLOAD_ERR_NO_FILE) {
json(['success' => FALSE, 'message' => "Please complete all required fields."]);
exit;
}
}
}
$ignoredFields = ['g-recaptcha-response', 'emailSubject', 'emailSender', 'reqFields', 'reqSpace', 'can_upload', 'can_submit', 'submissions_quota', 'submissions_used', 'space_used', 'space_quota', 'hasfile', 'numfiles'];
$emailContent .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . $newline;
$emailContent .= '<html xmlns="http://www.w3.org/1999/xhtml">' . $newline;
$emailContent .= '<head>' . $newline;
$emailContent .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $newline;
$emailContent .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>' . $newline;
$emailContent .= '<title>'.$emailSubject.'</title>' . $newline;
$emailContent .= '</head>'  . $newline;
$emailContent .= '<body style="font-family:\'Verdana\';margin:20px;">' . $newline;
$emailContent .= "<!--placeholder-warnings-->";$emailContent .= '<table style="width:100%;" cellpadding="10" cellspacing="0">' . $newline;
$emailContent .= "<tr>";
$emailContent .= "<td style='width:70%;vertical-align:middle;'>";
$emailContent .= "<h2>$form_name</h2>";
$emailContent .= "</td>";
$emailContent .= "<td>";
$emailContent .= "<div style='width:150px;text-align:center;float:right'>" . $newline;
$emailContent .= "<a style='text-decoration:none' href=\""."mailto:" . $emailSender . "?subject=RE: " . rawurlencode($emailSubject) . "&body=" . "<!--messagebody-->" . "\">";
$emailContent .= "<div style='padding:10px;background-color:#4EB958;color:white;border-radius:5px;text-decoration:none;'>" . $newline;
$emailContent .= "Reply";
$emailContent .= "</div>";
$emailContent .= "</a>";
$emailContent .= "</div>";
$emailContent .= "</td>";
$emailContent .= "</tr>";
$emailContent .= "</table>";
$emailContent .= '<table style="border: 1px solid #ccc;width:100%;" cellpadding="10" cellspacing="0">' . $newline;
$emailContent .= "<thead>";
$emailContent .= "<tr bgcolor=\"#54bae2\" style=\"color:#fff;font-weight:normal;\">";
$emailContent .= "<td width=\"20%\" align=\"right\">";
$emailContent .= "Field";
$emailContent .= "</td>";
$emailContent .= "<td align='left'>";
$emailContent .= "Value";
$emailContent .= "</td>";
$emailContent .= "</tr>";
$emailContent .= "</thead>";
$emailContent .= "<tbody>" . $newline;
$row = 0;
$okToSend = FALSE;$formPlainText = "";foreach ($_POST as $key=>$value) {
if (!in_array($key, $ignoredFields) && strpos($key, '_label')===FALSE) {
if (!isset($_POST[$key . "_label"])) {
  continue;
 }
$fields[$_POST[$key.'_label']] = is_array($value) ? implode(",",$value) : $value;
$formField = htmlspecialchars($_POST[$key.'_label']);if ($formField == "") { } else {$okToSend = TRUE; 
$formField = urldecode($formField); $row++;
if ($row % 2 == 0) {
$emailContent .= "<tr>";
} else {
$emailContent .= "<tr bgcolor=\"#f8fcfd\">" . $newline;
}$emailContent .= "<td align=\"right\" style=\"border-top:1px solid #c0c0c0;font-weight:bold;font-size:.9em\">" . $newline;
$emailContent .= $formField;
$formPlainText .= "> " . $formField . ": "; $emailContent .= "</td>" . $newline;$emailContent .= "<td style=\"border-top:1px solid #c0c0c0\">" . $newline;$finalValue = "";if (is_array($value)) {
$valueStr = implode(", ", $value);
$finalValue = $valueStr;
} else {
if (array_key_exists("dtpic_" . $key . "_submit",$_POST)) {
$value = $_POST["dtpic_" . $key . "_submit"];
}$value = htmlspecialchars(stripslashes(($value)));
$value = nl2br($value);if ($value!="") {
$finalValue = $value;
} else {
$finalValue = "N/A";
}}if (strlen($finalValue) > 988) {
$finalValue = chunk_split($finalValue, 988, $newline);
}$emailContent .= $finalValue;
$formPlainText .=  str_replace("<br />"," ", $finalValue) . "\n";if ($confirmationEmail!="") {
$confirmationEmail = str_ireplace("%".$formField."%", $finalValue, $confirmationEmail);
}$emailContent .= "</td>";
$emailContent .= "</tr>" . $newline;}}
}
$emailContent .= "<!--email attachements-->";
if ($row % 2 == 0) {
$strEmailAttachments .= "<tr>";
} else {
$strEmailAttachments .= "<tr bgcolor=\"#f8fcfd\">" . $newline;
}
$strEmailAttachments .= "<td align=\"right\" style=\"border-top:1px solid #c0c0c0;font-weight:bold;font-size:.9em\">" . $newline;
$strEmailAttachments .= "Attachments";
$strEmailAttachments .= "</td>" . $newline;$strEmailAttachments .= "<td style=\"border-top:1px solid #c0c0c0\">" . $newline;
$strEmailAttachments .= "<a href='https://www.ragesw.com/ewforms/$form_code/$customer_code/<submission_id>'>Download</a>";
$strEmailAttachments .= "</td>";
$strEmailAttachments .= "</tr>" . $newline;
if ($confirmationEmail!="") {
$confirmationEmail = str_replace("%allformdata%", $formPlainText, $confirmationEmail);
}
$useAPI = false;

$formPlainText = "\n\n" . $formPlainText;$formPlainText = rawurlencode($formPlainText);
$emailContent = str_replace("<!--messagebody-->",$formPlainText,$emailContent);
$emailContent .= "</tbody>";

$emailContent .= "<tfoot>";
$emailContent .= "<tr style=\"text-align:center;background-color:#f6f6f6;color:#555;font-size:1em;\">" . $newline;
$emailContent .= "<td colspan=\"2\" style=\"border-top: 1px solid #ccc;\"><a href=\"https://billing.ragesw.com\">Login to your account</a> to manage form submissions, search, export as CSV and receive file downloads from your website.";
$emailContent .= "</td>" . $newline;
$emailContent .= "</tr>";
$emailContent .= "</tfoot>";
$emailContent .= "</table>";
$fields['customer_code'] = $customer_code;
$fields['form_name'] = $form_name;
if ($check_required_size !== FALSE) {
if ($customer_code != "") {
$everweb = new EverWeb_API($customer_code);
$stats = $everweb->getCustomerStats();
if (!is_array($stats)) {
json(['success' => FALSE, 'message' => "Can't get contact form stats"]);exit;
} else {if ($stats['submissions_used'] < $stats['submissions_quota']) {
$can_submit = TRUE;
} else {
$can_submit = FALSE;
}
if ($can_submit) {
if ($stats['space_quota'] - $stats['space_used'] < $check_required_size) {
$can_upload = FALSE;
} else {
$can_upload = TRUE;
}
} else {
$can_upload = FALSE;
}
json(['success' => TRUE, 'can_submit' => $can_submit, 'can_upload' => $can_upload, 'space_quota' => $stats['space_quota'], 'space_used' => $stats['space_used'], 'submissions_used' => $stats['submissions_used'], 'submissions_quota'=>$stats['submissions_quota']]);
exit;
}} else {json(['success' => TRUE, 'can_submit' => FALSE, 'can_upload' => FALSE]);
exit; }
}
$emailStats = "";if ($customer_code != "") { 
$can_upload = $_POST['can_upload'];
$can_submit = $_POST['can_submit'];
$submissions_quota = $_POST['submissions_quota'];
$submissions_used = $_POST['submissions_used'];
$space_used = $_POST['space_used']; $space_quota = $_POST['space_quota'];
$errorMsg = ""; 
$numAttachments = 0;
if ($can_upload == "true" && $can_submit == "true") { 
if(!empty($_FILES)) {
$fields['num_attachments'] = count($_FILES);
}$everweb = new EverWeb_API($customer_code);
if (!$everweb->submitForm($form_code, $fields)) {
json(['success' => FALSE, 'message' => "The form could not be sent: " . $everweb->getError()]);
$errorMsg = "The form and any file uploads were not added to your management account: ".$everweb->getError();
} else {
$submission_id = $everweb->submission_id;
}
foreach ($_FILES as $key=>$file) {
if ($file['error'] && $file['error'] != UPLOAD_ERR_NO_FILE) {
json(['success' => FALSE, 'message' => $upload_errors[$file['error']]]);
$errorMsg = "The file could no be uploaded: ".  $upload_errors[$file['error']];
exit;
}
elseif (!$file['error']) {
if (!$everweb->upload($file)) {
json(['success' => FALSE, 'message' => 'File '.$file['name'].' could not be uploaded: '.$everweb->getError()]);
$errorMsg = 'File '.$file['name'].' could not be uploaded: '.$everweb->getError();
break; } else {
$numAttachments++;
}
}}
if ($errorMsg != "") {
$emailStats .= "<div style=\"padding:20px;margin-bottom:20px;margin-top:20px;text-align:center;background-color:#f2dede;color:#a94442;border-radius:4px;border: 1px solid #ebccd1;\">" . $errorMsg . "</div>";
}if ($numAttachments > 0) {
$emailContent = str_replace("<!--email attachements-->", $strEmailAttachments, $emailContent);
} else {
$emailContent = str_replace("<!--email attachements-->", "", $emailContent);
}} else {$emailContent = str_replace("<!--placeholder-warnings-->","<div style=\"padding:20px;margin-bottom:20px;text-align:center;background-color:#f2dede;color:#a94442;border-radius:4px;border: 1px solid #ebccd1;\">You don't have enough space to manage submissions or receive file uploads. Please upgrade your account to allow online form submission management.<br/><br><a href=\"https://billing.ragesw.com/link.php?id=73\">Upgrade Now</a></div>" . $newline,$emailContent);}if (($submissions_quota - $submissions_used < 50 && $submissions_quota - $submissions_used > 0) || ($space_quota - $space_used < 50000 && $space_quota - $space_used > 0)) {
$emailContent = str_replace("<!--placeholder-warnings-->","<div style=\"padding:20px;margin-bottom:20px;text-align:center;background-color:#fcf8e3;color:#8a6d3b;border-radius:4px;border: 1px solid #faebcc;\">You are almost out of space for your forms. Please login to your account to upgrade your form limits and make sure you keep receiving your form submissions.<br/><br><a href=\"https://billing.ragesw.com/link.php?id=73\">Upgrade Now</a></div>"  . $newline,$emailContent);
}
$emailStats .= "<!-- form stats -->";
$emailStats .= '<table style="border: 1px solid #ccc;width:100%;margin-top:20px;margin-bottom:20px;background-color:#f8f8f8;color:black;" bgcolor="#f8f8f8" cellpadding="20">' . $newline;
$emailStats .= '<tr>';
$emailStats .= '<td width="50%" style="border-right: 1px solid #ccc"><div style="font-size:2em">'.$submissions_used.' of '.$submissions_quota.'</div><div style="font-weight: 700;color: #888;text-transform: uppercase;font-size: 12px;">submissions</div><div style="background-color:#5cb85c;margin-top: 4px;height: 2px;border-radius: 2px;"></div></td>' . $newline;
$emailStats .= '<td width="50%"><div style="font-size:2em">'.formatBytes($space_used).' of '. formatBytes($space_quota) .'</div><div style="font-weight: 700;color: #888;text-transform: uppercase;font-size: 12px;">Space Used</div><div style="background-color:#d9534f;margin-top: 4px;height: 2px;border-radius: 2px;">' . $newline;
$emailStats .= '</div></td>';
$emailStats .= '</tr>';
$emailStats .= '</table>' . $newline;
$emailContent .= $emailStats; } else {

$emailContent .= '<div style="padding:20px;margin-top:20px;margin-bottom:20px;text-align:center; color: #31708f; background-color: #d9edf7;border-radius:4px;border: 1px solid #bce8f1;">Get Enhanced contact forms and manage your form submissions online, search, export as CSV and receive file uploads.<br/><br><a href="https://billing.ragesw.com/link.php?id=72">Learn More...</a></div>' . $newline;

}
date_default_timezone_set("America/New_York");
$emailContent .= "<p style='font-size:.8em'>IP: " . get_ip_address() . "<br />" . $newline;
$emailContent .= "Page referrer: " . $_SERVER["HTTP_REFERER"] . "<br />" . $newline;
$emailContent .= "Date submitted: " . date("Y/m/d h:i:sa") . " EST <br />" . $newline;
$emailContent .= "</p>" . $newline;
$emailContent = str_replace("<!--placeholder-warnings-->","",$emailContent);
$emailContent .= "</body></html>";
$server  = "";
$port  = "465";
$username  = "";
$password  = "";
$protocol = "None";
if ($okToSend) { 
if (!$useAPI) {
$emailContent = str_replace("<submission_id>",$submission_id, $emailContent);
$mail = new EverWeb_Mail($server, $port, $username, $password, $protocol);$mail->contentType = "text/html";
if(!$mail->sendMail($emailSender, $emailRecipient, $emailSubject, $emailContent)) {
json(['success' => FALSE, 'message' => "Could not send email: ".$mail->errorMessage, 'log' => htmlentities($mail->printDebugLog(TRUE))]);} else {
if (!$doSendConfirmation) {json(['success' => TRUE, 'message' => "Email sent successfully", 'url' => $file_url]);} else {
$mail = new EverWeb_Mail($server, $port, $username, $password, $protocol);$mail->contentType = "text/plain";if(!$mail->sendMail($emailRecipient, $emailSender,  $strConfirmationSubject , $confirmationEmail, null, true, "text/plain")) {
json(['success' => FALSE, 'message' => "Could not send thank you email: ".$mail->errorMessage, 'log' => htmlentities($mail->printDebugLog(TRUE))]);
} else {
json(['success' => TRUE, 'message' => "Emails sent successfully"]);
}
}
}
} else {json(['success' => TRUE, 'message' => "Email sent successfully"]);
}
} else {
json(['success' => FALSE, 'message' => "No data to send"]);
}
?>
