<?php
class HzmMailFormatter{
    public static function hzm_wrap_mail($text, $indent = '') {
  // Convert CRLF into LF.
  $text = str_replace("\r", '', $text);
  // See if soft-wrapping is allowed.
  $clean_indent = self::_hzm_html_to_text_clean($indent);
  $soft = strpos($clean_indent, ' ') === FALSE;
  // Check if the string has line breaks.
  if (strpos($text, "\n") !== FALSE) {
    // Remove trailing spaces to make existing breaks hard, but leave signature
    // marker untouched (RFC 3676, Section 4.3).
    $text = preg_replace('/(?(?<!^--) +\n|  +\n)/m', "\n", $text);
    // Wrap each line at the needed width.
    $lines = explode("\n", $text);
    array_walk($lines, '_hzm_wrap_mail_line', array('soft' => $soft, 'length' => strlen($indent)));
    $text = implode("\n", $lines);
  }
  else {
    // Wrap this line.
    self::_hzm_wrap_mail_line($text, 0, array('soft' => $soft, 'length' => strlen($indent)));
  }
  // Empty lines with nothing but spaces.
  $text = preg_replace('/^ +\n/m', "\n", $text);
  // Space-stuff special lines.
  $text = preg_replace('/^(>| |From)/m', ' $1', $text);
  // Apply indentation. We only include non-'>' indentation on the first line.
  $text = $indent . substr(preg_replace('/^/m', $clean_indent, $text), strlen($indent));

  return $text;
}

/**
 * Transforms an HTML string into plain text, preserving its structure.
 *
 * The output will be suitable for use as 'format=flowed; delsp=yes' text
 * (RFC 3676) and can be passed directly to hzm_mail() for sending.
 *
 * We deliberately use LF rather than CRLF, see hzm_mail().
 *
 * This function provides suitable alternatives for the following tags:
 * <a> <em> <i> <strong> <b> <br> <p> <blockquote> <ul> <ol> <li> <dl> <dt>
 * <dd> <h1> <h2> <h3> <h4> <h5> <h6> <hr>
 *
 * @param $string
 *   The string to be transformed.
 * @param $allowed_tags (optional)
 *   If supplied, a list of tags that will be transformed. If omitted, all
 *   all supported tags are transformed.
 *
 * @return
 *   The transformed string.
 */
public static function hzm_html_to_text($string, $allowed_tags = NULL) {
  // Cache list of supported tags.
  static $supported_tags;
  if (empty($supported_tags)) {
    $supported_tags = array('a', 'em', 'i', 'strong', 'b', 'br', 'p', 'blockquote', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr');
  }

  // Make sure only supported tags are kept.
  $allowed_tags = isset($allowed_tags) ? array_intersect($supported_tags, $allowed_tags) : $supported_tags;

  // Make sure tags, entities and attributes are well-formed and properly nested.
  //$string = _filter_htmlcorrector(filter_xss($string, $allowed_tags));

  // Apply inline styles.
  $string = preg_replace('!</?(em|i)((?> +)[^>]*)?>!i', '/', $string);
  $string = preg_replace('!</?(strong|b)((?> +)[^>]*)?>!i', '*', $string);

  // Replace inline <a> tags with the text of link and a footnote.
  self::_hzm_html_to_mail_urls(NULL, TRUE);
  $pattern = '@(<a[^>]+?href="([^"]*)"[^>]*?>(.+?)</a>)@i';
  $string = preg_replace_callback($pattern, '_hzm_html_to_mail_urls', $string);
  $urls = self::_hzm_html_to_mail_urls();
  $footnotes = '';
  if (count($urls)) {
    $footnotes .= "\n";
    for ($i = 0, $max = count($urls); $i < $max; $i++) {
      $footnotes .= '[' . ($i + 1) . '] ' . $urls[$i] . "\n";
    }
  }

  // Split tags from text.
  $split = preg_split('/<([^>]+?)>/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
  // Note: PHP ensures the array consists of alternating delimiters and literals
  // and begins and ends with a literal (inserting $null as required).

  $tag = FALSE; // Odd/even counter (tag or no tag)
  $casing = NULL; // Case conversion function
  $output = '';
  $indent = array(); // All current indentation string chunks
  $lists = array(); // Array of counters for opened lists
  foreach ($split as $value) {
    $chunk = NULL; // Holds a string ready to be formatted and output.

    // Process HTML tags (but don't output any literally).
    if ($tag) {
      list($tagname) = explode(' ', strtolower($value), 2);
      switch ($tagname) {
        // List counters
        case 'ul':
          array_unshift($lists, '*');
          break;
        case 'ol':
          array_unshift($lists, 1);
          break;
        case '/ul':
        case '/ol':
          array_shift($lists);
          $chunk = ''; // Ensure blank new-line.
          break;

        // Quotation/list markers, non-fancy headers
        case 'blockquote':
          // Format=flowed indentation cannot be mixed with lists.
          $indent[] = count($lists) ? ' "' : '>';
          break;
        case 'li':
          $indent[] = isset($lists[0]) && is_numeric($lists[0]) ? ' ' . $lists[0]++ . ') ' : ' * ';
          break;
        case 'dd':
          $indent[] = '    ';
          break;
        case 'h3':
          $indent[] = '.... ';
          break;
        case 'h4':
          $indent[] = '.. ';
          break;
        case '/blockquote':
          if (count($lists)) {
            // Append closing quote for inline quotes (immediately).
            $output = rtrim($output, "> \n") . "\"\n";
            $chunk = ''; // Ensure blank new-line.
          }
          // Fall-through
        case '/li':
        case '/dd':
          array_pop($indent);
          break;
        case '/h3':
        case '/h4':
          array_pop($indent);
        case '/h5':
        case '/h6':
          $chunk = ''; // Ensure blank new-line.
          break;

        // Fancy headers
        case 'h1':
          $indent[] = '======== ';
          $casing = 'hzm_strtoupper';
          break;
        case 'h2':
          $indent[] = '-------- ';
          $casing = 'hzm_strtoupper';
          break;
        case '/h1':
        case '/h2':
          $casing = NULL;
          // Pad the line with dashes.
          $output = self::_hzm_html_to_text_pad($output, ($tagname == '/h1') ? '=' : '-', ' ');
          array_pop($indent);
          $chunk = ''; // Ensure blank new-line.
          break;

        // Horizontal rulers
        case 'hr':
          // Insert immediately.
          $output .= self::hzm_wrap_mail('', implode('', $indent)) . "\n";
          $output = self::_hzm_html_to_text_pad($output, '-');
          break;

        // Paragraphs and definition lists
        case '/p':
        case '/dl':
          $chunk = ''; // Ensure blank new-line.
          break;
      }
    }
    // Process blocks of text.
    else {
      // Convert inline HTML text to plain text; not removing line-breaks or
      // white-space, since that breaks newlines when sanitizing plain-text.
      $value = trim(decode_entities($value));
      if (hzm_strlen($value)) {
        $chunk = $value;
      }
    }

    // See if there is something waiting to be output.
    if (isset($chunk)) {
      // Apply any necessary case conversion.
      if (isset($casing)) {
        $chunk = $casing($chunk);
      }
      // Format it and apply the current indentation.
      $output .= self::hzm_wrap_mail($chunk, implode('', $indent)) . MAIL_LINE_ENDINGS;
      // Remove non-quotation markers from indentation.
      $indent = array_map('_hzm_html_to_text_clean', $indent);
    }

    $tag = !$tag;
  }

  return $output . $footnotes;
}

/**
 * Wraps words on a single line.
 *
 * Callback for array_walk() winthin hzm_wrap_mail().
 */
public static function _hzm_wrap_mail_line(&$line, $key, $values) {
  // Use soft-breaks only for purely quoted or unindented text.
  $line = wordwrap($line, 77 - $values['length'], $values['soft'] ? " \n" : "\n");
  // Break really long words at the maximum width allowed.
  $line = wordwrap($line, 996 - $values['length'], $values['soft'] ? " \n" : "\n", TRUE);
}

/**
 * Keeps track of URLs and replaces them with placeholder tokens.
 *
 * Callback for preg_replace_callback() within hzm_html_to_text().
 */
public static function _hzm_html_to_mail_urls($match = NULL, $reset = FALSE) {
  global $base_url, $base_path;
  static $urls = array(), $regexp;

  if ($reset) {
    // Reset internal URL list.
    $urls = array();
  }
  else {
    if (empty($regexp)) {
      $regexp = '@^' . preg_quote($base_path, '@') . '@';
    }
    if ($match) {
      list(, , $url, $label) = $match;
      // Ensure all URLs are absolute.
      $urls[] = strpos($url, '://') ? $url : preg_replace($regexp, $base_url . '/', $url);
      return $label . ' [' . count($urls) . ']';
    }
  }
  return $urls;
}

/**
 * Replaces non-quotation markers from a given piece of indentation with spaces.
 *
 * Callback for array_map() within hzm_html_to_text().
 */
public static function _hzm_html_to_text_clean($indent) {
  return preg_replace('/[^>]/', ' ', $indent);
}

/**
 * Pads the last line with the given character.
 *
 * @see hzm_html_to_text()
 */
public static function _hzm_html_to_text_pad($text, $pad, $prefix = '') {
  // Remove last line break.
  $text = substr($text, 0, -1);
  // Calculate needed padding space and add it.
  if (($p = strrpos($text, "\n")) === FALSE) {
    $p = -1;
  }
  $n = max(0, 79 - (strlen($text) - $p) - strlen($prefix));
  // Add prefix and padding, and restore linebreak.
  return $text . $prefix . str_repeat($pad, $n) . "\n";
}
}