<?php

// @codingStandardsIgnoreStart
namespace Drupal\hr_paragraphs\Controller;

/**
 * CalFileParser.
 *
 * Parser for iCal and vCal files. Reads event information and
 * outputs data into an Array or JSON.
 *
 * @author Michael Mottola <mikemottola@gmail.com>
 * @license MIT
 * @version 1.0
 */
class CalFileParser {

  private string $_base_path = './';
  private string $_file_name = '';
  private string $_output = 'array';
  private string $_default_output = 'array';
  private ?string $_user_timezone = NULL;
  private ?string $_file_timezone = NULL;

  /**
   * Supported fields.
   *
   * @var array<string>
   */
  private array $DTfields = ['DTSTART', 'DTEND', 'DTSTAMP', 'CREATED', 'EXDATE', 'LAST-MODIFIED'];

  /**
   *
   */
  public function __construct() {
    $this->_default_output = $this->_output;
  }

  /**
   *
   */
  public function set_base_path(string $path) : void {
    if (!empty($path)) {
      $this->_base_path = $path;
    }
  }

  /**
   *
   */
  public function set_file_name(string $filename) : void {
    if (!empty($filename)) {
      $this->_file_name = $filename;
    }
  }

  /**
   *
   */
  public function set_output(string $output) : void {
    if (!empty($output)) {
      $this->_output = $output;
    }
  }

  /**
   *
   */
  public function set_timezone(string $timezone) : void {
    if (!empty($timezone)) {
      $this->_user_timezone = $timezone;
    }
  }

  /**
   *
   */
  public function get_base_path() : string {
    return $this->_base_path;
  }

  /**
   *
   */
  public function get_file_name() : string {
    return $this->_file_name;
  }

  /**
   *
   */
  public function get_output() : string {
    return $this->_output;
  }

  /**
   * Read File.
   *
   * @param string $file
   *
   * @return string|bool
   *
   * @example
   *  read_file('schedule.vcal')
   *  read_file('../2011-08/'schedule.vcal');
   *  read_file('http://michaelencode.com/example.vcal');
   */
  public function read_file($file = '') {

    if (empty($file)) {
      $file = $this->_file_name;
    }

    // Check to see if file path is a url.
    if (preg_match('/^(http|https):/', $file) === 1) {
      return $this->read_remote_file($file);
    }

    // Empty base path if file starts with forward-slash.
    if (substr($file, 0, 1) === '/') {
      $this->set_base_path('');
    }

    if (!empty($file) && file_exists($this->_base_path . $file)) {
      $file_contents = file_get_contents($this->_base_path . $file);
      return $file_contents;
    }

    // Assume it's a string.
    if (!empty($file)) {
      return $file;
    }

    return FALSE;
  }

  /**
   * Read Remote File.
   *
   * @param string $file
   *
   * @return bool|string
   */
  public function read_remote_file($file) {
    if (!empty($file)) {
      $data = file_get_contents($file);
      if ($data !== FALSE) {
        return $data;
      }
    }
    return FALSE;
  }

  /**
   * Parse
   * Parses iCal or vCal file and returns data of a type that is specified
   *
   * @param string $file
   * @param string $output
   *
   * @return mixed|string
   */
  public function parse($file = '', $output = '') {
    $file_contents = $this->read_file($file);

    if ($file_contents == FALSE) {
      return 'Error: File Could not be read';
    }

    if (empty($output)) {
      $output = $this->_output;
    }

    if (empty($output)) {
      $output = $this->_default_output;
    }

    $events_arr = [];

    // Fetch timezone to create datetime object.
    if (preg_match('/X-WR-TIMEZONE:(.+)/i', $file_contents, $timezone) === 1) {
      $this->_file_timezone = trim($timezone[1]);
      if ($this->_user_timezone == NULL) {
        $this->_user_timezone = $this->_file_timezone;
      }
    }
    else {
      $this->_file_timezone = $this->_user_timezone;
    }

    // Tell user if setting timezone is necessary.
    if ($this->_user_timezone == NULL) {
      return 'Error: no timezone set or found';
    }

    // Put contains between start and end of VEVENT into array called $events.
    preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $file_contents, $events);

    if (!empty($events)) {
      foreach ($events[0] as $event_str) {

        // Remove begin and end "tags".
        $event_str = trim(str_replace(['BEGIN:VEVENT', 'END:VEVENT'], '', $event_str));

        // Convert string of entire event into an array with elements containing string of 'key:value'.
        $event_key_pairs = $this->convert_event_string_to_array($event_str);

        // Convert array of 'key:value' strings to an array of key => values.
        $events_arr[] = $this->convert_key_value_strings($event_key_pairs);
      }
    }

    $this->_output = $this->_default_output;

    return $this->output($events_arr, $output);
  }

  /**
   * Output
   *
   * @param array<int, mixed> $events_arr
   *   Array of events.
   *
   * @param string $output
   *   Output type.
   *
   * @return mixed|array<int, mixed>
   *   Output as json or array.
   */
  private function output($events_arr, $output = 'array') {
    switch ($output) {
      case 'json':
        return json_encode($events_arr);

      default:
        return $events_arr;

    }
  }

  /**
   * Convert event string to array.
   *
   * @param string $event_str
   *   Content of an event.
   *
   * @return array<int, mixed>
   *   Same data as an array.
   */
  private function convert_event_string_to_array($event_str = '') : array {
    if (!empty($event_str)) {
      // Replace new lines with a custom delimiter.
      $event_str = preg_replace("/[\r\n]/", "%%", $event_str);

      // Take care of line wrapping.
      $event_str = preg_replace("/%%%% /", "", $event_str);

      // If this code is executed, then file consisted of one line causing previous tactic to fail.
      if (strpos(substr($event_str, 2), '%%') == '0') {
        $tmp_piece = explode(':', $event_str);
        $num_pieces = count($tmp_piece);

        $event_str = '';
        foreach ($tmp_piece as $key => $item_str) {

          if ($key != ($num_pieces - 1)) {

            // Split at spaces.
            $tmp_pieces = preg_split('/\s/', $item_str);

            // Get the last whole word in the string [item].
            $last_word = end($tmp_pieces);

            // Adds delimiter to front and back of item string, and also between each new key.
            $item_str = trim(str_replace([$last_word, ' %%' . $last_word], ['%%' . $last_word . ':', '%%' . $last_word], $item_str));
          }

          // Build the event string back together, piece by piece.
          $event_str .= trim($item_str);
        }
      }

      // Perform some house cleaning just in case.
      $event_str = str_replace('%%%%', '%%', $event_str);

      if (substr($event_str, 0, 2) == '%%') {
        $event_str = substr($event_str, 2);
      }

      // Break string into array elements at custom delimiter.
      $return = explode('%%', $event_str);
    }
    else {
      $return = [];
    }

    return $return;
  }

  /**
   * Parse key-value string.
   *
   * @param array<int, mixed> $event_key_pairs
   *   Key value pairs.
   *
   * @return array<int, mixed>
   *   Converted array.
   */
  private function convert_key_value_strings($event_key_pairs = []) {
    $event = [];
    $event_alarm = [];
    $event_alarms = [];
    $inside_alarm = FALSE;

    if (!empty($event_key_pairs)) {
      foreach ($event_key_pairs as $line) {

        if (empty($line)) {
          continue;
        }

        $line_data = explode(':', $line, 2);
        $key = trim((isset($line_data[0])) ? $line_data[0] : "");
        $value = trim((isset($line_data[1])) ? $line_data[1] : "");

        // We are parsing an alarm for this event.
        if ($key == "BEGIN" && $value == "VALARM") {
          $inside_alarm = TRUE;
          $event_alarm = [];
          continue;
        }

        // We finished parsing an alarm for this event.
        if ($key == "END" && $value == "VALARM") {
          $inside_alarm = FALSE;
          $event_alarms[] = $event_alarm;
          continue;
        }

        // Autoconvert datetime fields to DateTime object.
        $date_key = (strstr($key, ";")) ? strstr($key, ";", TRUE) : $key;
        $date_format = (strstr($key, ";")) ? strstr($key, ";") : ";VALUE=DATE-TIME";

        if (in_array($date_key, $this->DTfields)) {

          // Set date key without format.
          $key = $date_key;

          $timezone = $this->_file_timezone;

          // Found time zone in date format info.
          if (strstr($date_format, "TZID")) {
            $strstr = strstr($date_format, "TZID");
            $timezone = substr($strstr, 5);
          }

          // Process all dates if there are more then one and comma seperated.
          $processed_value = [];
          foreach (explode(",", $value) as $date_value) {

            // This is simply a date.
            if ($date_format == ";VALUE=DATE") {
              $date_value .= "T000000";
            }

            // date-time in UTC.
            if (substr($date_value, -1) == "Z") {
              $timezone = "UTC";
            }

            // Format date.
            $date = \DateTime::createFromFormat('Ymd\THis', str_replace('Z', '', $date_value), new \DateTimeZone($timezone));
            if ($date !== FALSE) {
              $date->setTimezone(new \DateTimeZone($this->_user_timezone));
            }

            if ($date !== FALSE) {
              $processed_value[] = $date;
            }
          }

          // We have more then one date value then return it as an array.
          if (count($processed_value) > 1) {
            $value = $processed_value;
          }
          else {
            if ($date !== FALSE) {
              $value = $date;
            }
          }
        }

        // Check if current key was already set
        // if this is the case then add value data and turn it into an array.
        $value_current_key = FALSE;
        if ($inside_alarm) {
          if (isset($event_alarm[$key])) {
            $value_current_key = $event_alarm[$key];
          }
        }
        else {
          if (isset($event[$key])) {
            $value_current_key = $event[$key];
          }
        }

        // This current key already has data add more.
        if ($value_current_key !== FALSE) {

          // Check if data is array and merge.
          if (is_array($value_current_key)) {
            if (is_array($value)) {
              $value = array_merge($value_current_key, $value);
            }
            else {
              $value = array_merge($value_current_key, [$value]);
            }
          }
          else {
            if (is_array($value)) {
              $value = array_merge([$value_current_key], $value);
            }
            else {
              $value = [$value_current_key, $value];
            }
          }
        }

        if ($inside_alarm) {
          $event_alarm[$key] = $value;
        }
        else {
          $event[$key] = $value;
        }
      }
    }

    // Add alarm data.
    $event["VALARM"] = $event_alarms;

    // Unescape every element if string.
    return array_map(function ($value) {
        return (is_string($value) ? stripcslashes($value) : $value);
    }, $event);
  }

}
