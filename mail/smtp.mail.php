<?php
include("interface.mail.php");
include("smtp.phpmailer.php");
/**
 * @file
 * The code processing mail in the smtp module.
 *
 */

/**
* Modify the drupal mail system to use smtp when sending emails.
* Include the option to choose between plain text or HTML
*/
class SmtpMailSystem implements MailSystemInterface {

  public $errorInfo;
  
  protected $AllowHtml;
  /**
   * Concatenate and wrap the e-mail body for either
   * plain-text or HTML emails.
   *
   * @param $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return
   *   The formatted $message.
   */
  public function format(array $message) {
    $this->AllowHtml = AfwSession::config('smtp_allowhtml', 1);
    // Join the body array into one string.
    $message['body'] = implode("\n<br>\n", $message['body']);
    if ($this->AllowHtml == 0) {
      // Convert any HTML to plain-text.
      $message['body'] = hzm_html_to_text($message['body']);
      // Wrap the mail body for sending.
      $message['body'] = hzm_wrap_mail($message['body']);
    }
    return $message;
  }

  /**
   * Send the e-mail message.
   *
   * @see hzm_mail()
   *
   * @param $message
   *   A message array, as described in hook_mail_alter().
   * @return
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   */
  public function mail(array $message) {
    /*if (AfwSession::config('smtp_queue', FALSE)
       && (!isset($message['params']['skip_queue']) || !$message['params']['skip_queue'])) {
      smtp_send_queue($message);
      if (AfwSession::config('smtp_debugging', SMTP_LOGGING_ERRORS) == SMTP_LOGGING_ALL) 
      {
        // AfwRunHelper::afw_guard('smtp', 'Queue sending mail to: @to', array('@to' => $to));
      }
      return TRUE;
    }
      
      
    else {
      return $this->mailWithoutQueue($message);
    }*/
    
    return $this->mailWithoutQueue($message);
  }

  public function mailWithoutQueue(array $message) {
    $id = $message['id'];
    $to = $message['to'];
    $from = $message['from'];
    $body = $message['body'];
    $headers = $message['headers'];
    $subject = $message['subject'];

    // Create a new PHPMailer object - autoloaded from registry.
    $mailer = new PHPMailer();

    $logging = AfwSession::config('smtp_debugging', true);

    // Turn on debugging, if requested.
    if ($logging) 
    {
      $mailer->SMTPDebug = TRUE;
    }

    // Set the from name.
    if (AfwSession::config('smtp_fromname', '') != '') {
      $from_name = AfwSession::config('smtp_fromname', '');
    }
    else {
      // If value is not defined in settings, use site_name.
      $from_name = AfwSession::config('site_name', '');
    }

    //Hack to fix reply-to issue.
    $properfrom = AfwSession::config('site_mail', '');
    if (!empty($properfrom)) {
      $headers['From'] = $properfrom;
    }
    if (!isset($headers['Reply-To']) || empty($headers['Reply-To'])) {
      if (strpos($from, '<')) {
        $reply = preg_replace('/>.*/', '', preg_replace('/.*</', '', $from));
      }
      else {
        $reply = $from;
      }
      $headers['Reply-To'] = $reply;
    }

    // Blank value will let the e-mail address appear.

    if ($from == NULL || $from == '') {
      // If from e-mail address is blank, use smtp_from config option.
      if (($from = AfwSession::config('smtp_from', '')) == '') {
        // If smtp_from config option is blank, use site_email.
        if (($from = AfwSession::config('site_mail', '')) == '') {
          AfwSession::pushWarning(t('There is no submitted from address.'), 'error');
          if ($logging) {
            // AfwRunHelper::afw_guard('smtp', 'There is no submitted from address.', array(), // AfwRunHelper::afw_guard_ERROR);
          }
          return FALSE;
        }
      }
    }
    if (preg_match('/^"?.*"?\s*<.*>$/', $from)) {
      // . == Matches any single character except line break characters \r and \n.
      // * == Repeats the previous item zero or more times.
      $from_name = preg_replace('/"?([^("\t\n)]*)"?.*$/', '$1', $from); // It gives: Name
      $from      = preg_replace("/(.*)\<(.*)\>/i", '$2', $from); // It gives: name@domain.tld
    }
    /*
    elseif (!valid_email_address($from)) {
      AfwSession::pushWarning(t('The submitted from address (@from) is not valid.', array('@from' => $from)), 'error');
      if ($logging) {
        // AfwRunHelper::afw_guard('smtp', 'The submitted from address (@from) is not valid.', array('@from' => $from), // AfwRunHelper::afw_guard_ERROR);
      }
      return FALSE;
    }*/

    // Defines the From value to what we expect.
    $mailer->From     = $from;
    $mailer->FromName = $from_name;
    $mailer->Sender   = $from;


    // Create the list of 'To:' recipients.
    $torecipients = explode(',', $to);
    foreach ($torecipients as $torecipient) {
      if (strpos($torecipient, '<') !== FALSE) {
        $toparts = explode(' <', $torecipient);
        $toname = $toparts[0];
        $toaddr = rtrim($toparts[1], '>');
      }
      else {
        $toname = '';
        $toaddr = $torecipient;
      }
      $mailer->AddAddress($toaddr, $toname);
    }


    // Parse the headers of the message and set the PHPMailer object's settings
    // accordingly.
    foreach ($headers as $key => $value) {
      //// AfwRunHelper::afw_guard('error', 'Key: ' . $key . ' Value: ' . $value);
      switch (hzm_strtolower($key)) {
        case 'from':
          if ($from == NULL or $from == '') {
            // If a from value was already given, then set based on header.
            // Should be the most common situation since hzm_mail moves the
            // from to headers.
            $from           = $value;
            $mailer->From     = $value;
            // then from can be out of sync with from_name !
            $mailer->FromName = '';
            $mailer->Sender   = $value;
          }
          break;
        case 'content-type':
          // Parse several values on the Content-type header, storing them in an array like
          // key=value -> $vars['key']='value'
          $vars = explode(';', $value);
          foreach ($vars as $i => $var) {
            if ($cut = strpos($var, '=')) {
              $new_var = trim(hzm_strtolower(hzm_substr($var, $cut + 1)));
              $new_key = trim(hzm_substr($var, 0, $cut));
              unset($vars[$i]);
              $vars[$new_key] = $new_var;
            }
          }
          // Set the charset based on the provided value, otherwise set it to UTF-8 (which is Drupals internal default).
          $mailer->CharSet = isset($vars['charset']) ? $vars['charset'] : 'UTF-8';
          // If $vars is empty then set an empty value at index 0 to avoid a PHP warning in the next statement
          $vars[0] = isset($vars[0])?$vars[0]:'';

          switch ($vars[0]) {
            case 'text/plain':
              // The message includes only a plain text part.
              $mailer->IsHTML(FALSE);
              $content_type = 'text/plain';
              break;
            case 'text/html':
              // The message includes only an HTML part.
              $mailer->IsHTML(TRUE);
              $content_type = 'text/html';
              break;
            case 'multipart/related':
              // Get the boundary ID from the Content-Type header.
              $boundary = $this->_get_substring($value, 'boundary', '"', '"');

              // The message includes an HTML part w/inline attachments.
              $mailer->ContentType = $content_type = 'multipart/related; boundary="' . $boundary . '"';
            break;
            case 'multipart/alternative':
              // The message includes both a plain text and an HTML part.
              $mailer->ContentType = $content_type = 'multipart/alternative';

              // Get the boundary ID from the Content-Type header.
              $boundary = $this->_get_substring($value, 'boundary', '"', '"');
            break;
            case 'multipart/mixed':
              // The message includes one or more attachments.
              $mailer->ContentType = $content_type = 'multipart/mixed';

              // Get the boundary ID from the Content-Type header.
              $boundary = $this->_get_substring($value, 'boundary', '"', '"');
            break;
            default:
              // Everything else is unsuppored by PHPMailer.
              AfwSession::pushWarning(t('The %header of your message is not supported by PHPMailer and will be sent as text/plain instead.', array('%header' => "Content-Type: $value")), 'error');
              if ($logging) {
                // AfwRunHelper::afw_guard('smtp', 'The %header of your message is not supported by PHPMailer and will be sent as text/plain instead.', array('%header' => "Content-Type: $value"), // AfwRunHelper::afw_guard_ERROR);
              }
              // Force the Content-Type to be text/plain.
              $mailer->IsHTML(FALSE);
              $content_type = 'text/plain';
          }
          break;

        case 'reply-to':
          // Only add a "reply-to" if it's not the same as "return-path".
          if ($value != $headers['Return-Path']) {
            if (strpos($value, '<') !== FALSE) {
              $replyToParts = explode('<', $value);
              $replyToName = trim($replyToParts[0]);
              $replyToName = trim($replyToName, '"');
              $replyToAddr = rtrim($replyToParts[1], '>');
              $mailer->AddReplyTo($replyToAddr, $replyToName);
            }
            else {
              $mailer->AddReplyTo($value);
            }
          }
          break;

        case 'content-transfer-encoding':
          $mailer->Encoding = $value;
          break;

        case 'return-path':
          if (strpos($value, '<') !== FALSE) {
            $returnPathParts = explode('<', $value);
            $returnPathAddr = rtrim($returnPathParts[1], '>');
            $mailer->Sender = $returnPathAddr;
          }
          else {
            $mailer->Sender = $value;
          }
          break;

        case 'mime-version':
        case 'x-mailer':
          // Let PHPMailer specify these.
          break;

        case 'errors-to':
          $mailer->AddCustomHeader('Errors-To: ' . $value);
          break;

        case 'cc':
          $ccrecipients = explode(',', $value);
          foreach ($ccrecipients as $ccrecipient) {
            if (strpos($ccrecipient, '<') !== FALSE) {
              $ccparts = explode(' <', $ccrecipient);
              $ccname = $ccparts[0];
              $ccaddr = rtrim($ccparts[1], '>');
            }
            else {
              $ccname = '';
              $ccaddr = $ccrecipient;
            }
            $mailer->AddCC($ccaddr, $ccname);
          }
          break;

        case 'bcc':
          $bccrecipients = explode(',', $value);
          foreach ($bccrecipients as $bccrecipient) {
            if (strpos($bccrecipient, '<') !== FALSE) {
              $bccparts = explode(' <', $bccrecipient);
              $bccname = $bccparts[0];
              $bccaddr = rtrim($bccparts[1], '>');
            }
            else {
              $bccname = '';
              $bccaddr = $bccrecipient;
            }
            $mailer->AddBCC($bccaddr, $bccname);
          }
          break;

        case 'message-id':
          $mailer->MessageID = $value;
          break;

        default:
          // The header key is not special - add it as is.
          $mailer->AddCustomHeader($key . ': ' . $value);
      }
    }

    /**
     * TODO
     * Need to figure out the following.
     *
     * Add one last header item, but not if it has already been added.
     * $errors_to = FALSE;
     * foreach ($mailer->CustomHeader as $custom_header) {
     *   if ($custom_header[0] = '') {
     *     $errors_to = TRUE;
     *   }
     * }
     * if ($errors_to) {
     *   $mailer->AddCustomHeader('Errors-To: '. $from);
     * }
     */
    // Add the message's subject.
    $mailer->Subject = $subject;

    // Processes the message's body.
    switch ($content_type) {
      case 'multipart/related':
        $mailer->Body = $body;
        // TODO: Figure out if there is anything more to handling this type.
        break;

      case 'multipart/alternative':
        // Split the body based on the boundary ID.
        $body_parts = $this->_boundary_split($body, $boundary);
        foreach ($body_parts as $body_part) {
          // If plain/text within the body part, add it to $mailer->AltBody.
          if (strpos($body_part, 'text/plain')) {
            // Clean up the text.
            $body_part = trim($this->_remove_headers(trim($body_part)));
            // Include it as part of the mail object.
            $mailer->AltBody = $body_part;
          }
          // If plain/html within the body part, add it to $mailer->Body.
          elseif (strpos($body_part, 'text/html')) {
            // Clean up the text.
            $body_part = trim($this->_remove_headers(trim($body_part)));
            // Include it as part of the mail object.
            $mailer->Body = $body_part;
          }
        }
        break;

      case 'multipart/mixed':
        // Split the body based on the boundary ID.
        $body_parts = $this->_boundary_split($body, $boundary);

        // Determine if there is an HTML part for when adding the plain text part.
        $text_plain = FALSE;
        $text_html  = FALSE;
        foreach ($body_parts as $body_part) {
          if (strpos($body_part, 'text/plain')) {
            $text_plain = TRUE;
          }
          if (strpos($body_part, 'text/html')) {
            $text_html = TRUE;
          }
        }

        foreach ($body_parts as $body_part) {
          // If test/plain within the body part, add it to either
          // $mailer->AltBody or $mailer->Body, depending on whether there is
          // also a text/html part ot not.
          if (strpos($body_part, 'multipart/alternative')) {
            // Get boundary ID from the Content-Type header.
            $boundary2 = $this->_get_substring($body_part, 'boundary', '"', '"');
            // Clean up the text.
            $body_part = trim($this->_remove_headers(trim($body_part)));
            // Split the body based on the boundary ID.
            $body_parts2 = $this->_boundary_split($body_part, $boundary2);

            foreach ($body_parts2 as $body_part2) {
              // If plain/text within the body part, add it to $mailer->AltBody.
              if (strpos($body_part2, 'text/plain')) {
                // Clean up the text.
                $body_part2 = trim($this->_remove_headers(trim($body_part2)));
                // Include it as part of the mail object.
                $mailer->AltBody = $body_part2;
                $mailer->ContentType = 'multipart/mixed';
              }
              // If plain/html within the body part, add it to $mailer->Body.
              elseif (strpos($body_part2, 'text/html')) {
                // Get the encoding.
                $body_part2_encoding = trim($this->_get_substring($body_part2, 'Content-Transfer-Encoding', ':', "\n"));
                // Clean up the text.
                $body_part2 = trim($this->_remove_headers(trim($body_part2)));
                // Check whether the encoding is base64, and if so, decode it.
                if (hzm_strtolower($body_part2_encoding) == 'base64') {
                  // Include it as part of the mail object.
                  $mailer->Body = base64_decode($body_part2);
                  // Ensure the whole message is recoded in the base64 format.
                  $mailer->Encoding = 'base64';
                }
                else {
                  // Include it as part of the mail object.
                  $mailer->Body = $body_part2;
                }
                $mailer->ContentType = 'multipart/mixed';
              }
            }
          }
          // If text/plain within the body part, add it to $mailer->Body.
          elseif (strpos($body_part, 'text/plain')) {
            // Clean up the text.
            $body_part = trim($this->_remove_headers(trim($body_part)));

            if ($text_html) {
              $mailer->AltBody = $body_part;
              $mailer->IsHTML(TRUE);
              $mailer->ContentType = 'multipart/mixed';
            }
            else {
              $mailer->Body = $body_part;
              $mailer->IsHTML(FALSE);
              $mailer->ContentType = 'multipart/mixed';
            }
          }
          // If text/html within the body part, add it to $mailer->Body.
          elseif (strpos($body_part, 'text/html')) {
            // Clean up the text.
            $body_part = trim($this->_remove_headers(trim($body_part)));
            // Include it as part of the mail object.
            $mailer->Body = $body_part;
            $mailer->IsHTML(TRUE);
            $mailer->ContentType = 'multipart/mixed';
          }
          // Add the attachment.
          elseif (strpos($body_part, 'Content-Disposition: attachment;') && !isset($message['params']['attachments'])) {
            $file_path     = $this->_get_substring($body_part, 'filename=', '"', '"');
            $file_name     = $this->_get_substring($body_part, ' name=', '"', '"');
            $file_encoding = $this->_get_substring($body_part, 'Content-Transfer-Encoding', ' ', "\n");
            $file_type     = $this->_get_substring($body_part, 'Content-Type', ' ', ';');

            if (file_exists($file_path)) {
              if (!$mailer->AddAttachment($file_path, $file_name, $file_encoding, $file_type)) {
                AfwSession::pushWarning(t('Attahment could not be found or accessed.'));
              }
            }
            else {
              // Clean up the text.
              $body_part = trim($this->_remove_headers(trim($body_part)));

              if (hzm_strtolower($file_encoding) == 'base64') {
                $attachment = base64_decode($body_part);
              }
              elseif (hzm_strtolower($file_encoding) == 'quoted-printable') {
                $attachment = quoted_printable_decode($body_part);
              }
              else {
                $attachment = $body_part;
              }
              /* rafik : this is not clear
              $attachment_new_filename = hzm_tempnam('temporary://', 'smtp');
              $file_path = file_save_data($attachment, $attachment_new_filename, FILE_EXISTS_REPLACE);
              $real_path = hzm_realpath($file_path->uri);
              

              if (!$mailer->AddAttachment($real_path, $file_name)) {
                AfwSession::pushWarning(t('Attachment could not be found or accessed.'));
              }

              */
            }
          }
        }
        break;

      default:
        $mailer->Body = $body;
        break;
    }

    // Process mimemail attachments, which are prepared in mimemail_mail().
    /*
    rafik : not clear and provoque errors
    if (isset($message['params']['attachments'])) {
      foreach ($message['params']['attachments'] as $attachment) {
        if (isset($attachment['filecontent'])) {
          $mailer->AddStringAttachment($attachment['filecontent'], $attachment['filename'], 'base64', $attachment['filemime']);
        }
        if (isset($attachment['filepath'])) {
          $filename = isset($attachment['filename']) ? $attachment['filename'] : basename($attachment['filepath']);
          $filemime = isset($attachment['filemime']) ? $attachment['filemime'] : file_get_mimetype($attachment['filepath']);
          $mailer->AddAttachment($attachment['filepath'], $filename, 'base64', $filemime);
        }
      }
    }

    */

    // Set the authentication settings.
    $username = AfwSession::config('smtp_username', '');
    $password = AfwSession::config('smtp_password', '');

    // If username and password are given, use SMTP authentication.
    if ($username != '' && $password != '') {
      $mailer->SMTPAuth = TRUE;
      $mailer->Username = $username;
      $mailer->Password = $password;
    }


    // Set the protocol prefix for the smtp host.
    switch (AfwSession::config('smtp_protocol', 'standard')) {
      case 'ssl':
        $mailer->SMTPSecure = 'ssl';
        break;

      case 'tls':
        $mailer->SMTPSecure = 'tls';
        break;

      default:
        $mailer->SMTPSecure = '';
    }


    // Set other connection settings.
    $mailer->Host = AfwSession::config('smtp_host', '') . ';' . AfwSession::config('smtp_hostbackup', '');
    $mailer->Port = AfwSession::config('smtp_port', '25');
    $mailer->Mailer = 'smtp';

    $error = FALSE;
    if (!$mailer->send()) 
    {
      /*
      $params = array(
        '@from' => $from,
        '@to' => $to,
        '!error_message' => $mailer->ErrorInfo
      );*/

      if (AfwSession::config('smtp_queue_fail', FALSE)) 
      {
        // rafik : means if one fail, we fail all queue ?
        if ($logging) 
        {
             // AfwRunHelper::afw_guard('smtp', 'Error sending e-mail from @from to @to, will retry on cron run : !error_message.', $params, // AfwRunHelper::afw_guard_ERROR);
        }
        $this->smtp_failed_messages($message);
      }
      else
      {
        if ($logging) 
        {
           // AfwRunHelper::afw_guard('smtp', 'Error sending e-mail from @from to @to : !error_message', $params, // AfwRunHelper::afw_guard_ERROR);
        }
      }
      $error = TRUE;
      $this->errorInfo = '@from = ' . $from.' @to = ' . $to . " Error : ". $mailer->ErrorInfo;
    }

    $mailer->SmtpClose();
    return !$error;
  }

  function smtp_failed_messages($message)
  {
    // rafik 
    // @todo
  }

  /**
   * Splits the input into parts based on the given boundary.
   *
   * Swiped from Mail::MimeDecode, with modifications based on Drupal's coding
   * standards and this bug report: http://pear.php.net/bugs/bug.php?id=6495
   *
   * @param input
   *   A string containing the body text to parse.
   * @param boundary
   *   A string with the boundary string to parse on.
   * @return
   *   An array containing the resulting mime parts
   */
  protected function _boundary_split($input, $boundary) {
    $parts       = array();
    $bs_possible = hzm_substr($boundary, 2, -2);
    $bs_check    = '\"' . $bs_possible . '\"';

    if ($boundary == $bs_check) {
      $boundary = $bs_possible;
    }

    $tmp = explode('--' . $boundary, $input);

    for ($i = 1; $i < count($tmp); $i++) {
      if (trim($tmp[$i])) {
        $parts[] = $tmp[$i];
      }
    }

    return $parts;
  }  //  End of _smtp_boundary_split().

  /**
   * Strips the headers from the body part.
   *
   * @param input
   *   A string containing the body part to strip.
   * @return
   *   A string with the stripped body part.
   */
  protected function _remove_headers($input) {
    $part_array = explode("\n", $input);

    // will strip these headers according to RFC2045
    $headers_to_strip = array( 'Content-Type', 'Content-Transfer-Encoding', 'Content-ID', 'Content-Disposition');
    $pattern = '/^(' . implode('|', $headers_to_strip) . '):/';

    while (count($part_array) > 0) {

      // ignore trailing spaces/newlines
      $line = rtrim($part_array[0]);

      // if the line starts with a known header string
      if (preg_match($pattern, $line)) {
        $line = rtrim(array_shift($part_array));
        // remove line containing matched header.

        // if line ends in a ';' and the next line starts with four spaces, it's a continuation
        // of the header split onto the next line. Continue removing lines while we have this condition.
        while (substr($line, -1) == ';' && count($part_array) > 0 && substr($part_array[0], 0, 4) == '    ') {
          $line = rtrim(array_shift($part_array));
        }
      }
      else {
        // no match header, must be past headers; stop searching.
        break;
      }
    }

    $output = implode("\n", $part_array);
    return $output;
  }  //  End of _smtp_remove_headers().

  /**
   * Returns a string that is contained within another string.
   *
   * Returns the string from within $source that is some where after $target
   * and is between $beginning_character and $ending_character.
   *
   * @param $source
   *   A string containing the text to look through.
   * @param $target
   *   A string containing the text in $source to start looking from.
   * @param $beginning_character
   *   A string containing the character just before the sought after text.
   * @param $ending_character
   *   A string containing the character just after the sought after text.
   * @return
   *   A string with the text found between the $beginning_character and the
   *   $ending_character.
   */
  protected function _get_substring($source, $target, $beginning_character, $ending_character) {
    $search_start     = strpos($source, $target) + 1;
    $first_character  = strpos($source, $beginning_character, $search_start) + 1;
    $second_character = strpos($source, $ending_character, $first_character) + 1;
    $substring        = hzm_substr($source, $first_character, $second_character - $first_character);
    $string_length    = hzm_strlen($substring) - 1;

    if ($substring[$string_length] == $ending_character) {
      $substring = hzm_substr($substring, 0, $string_length);
    }

    return $substring;
  }  //  End of _smtp_get_substring().
}
