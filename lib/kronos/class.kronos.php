<?php

/*
  +--+ Project Name: KronoClass
  +--+ Version: 0.7
  +--+ Project Author: Tommaso D'Argenio
  +--+ Author Email: rajasi@ziobudda.net, info@holosoft.it
  +--+ Build Date:  January 10 2003 16.18 (CET)
  +--+ Update: September 9 2003 16.35 (CET)

  +--+ DISCLAIMER
  Copyright (c) 2002-03 Tommaso D'Argenio <rajasi@ziobudda.net>
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU Lesser General Public License
  as published by the Free Software Foundation; either version
  2.1 of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied
  warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
  PURPOSE.  See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public
  License along with this program; if not, write to the
  Free Software Foundation, Inc.,
  59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
  http://www.fsf.org

  +--+ NOTES FROM AUTHOR
 * Please, if you make any change in the code let me know by email!!!
  if use this class in your project, please let me know, in this way i can publish it.

  If you want to personalize the language settings, please take a look in all ini files.

  +--+ Requirements:
  PHP 4.0+
 */

/**
 * Class for compute some calculations on date and time
 *
 * @copyright  2002-2003 Holosoft - Tommaso D'Argenio <rajasi@ziobudda.net>
 * @version $Id class.kronos.php,v 0.7 2003/09/09 16.35.00(CET) marms Exp $
 * @link http://www.holosoft.it/  Holosoft Home Page
 * @link http://lafucina.holosoft.it/kronoclass/  KronoClass Home at Author's Site
 * @link http://www.phpclasses.org/browse.html/package/943.html KronoClass Home at phpclasses
 * @link http://freshmeat.net/projects/kronoclass/?topic_id=914 KronoClass Home at freshmeat
 */
class Krono {

    /** Array that contain the name of days in long format 	*
     *   @access private
     */
    var $day_name_ext;

    /** Array that contain the name of days in short format
     *   @access private
     */
    var $day_name_con;

    /** Array that contain the name of month in long format
     *   @access private
     */
    var $month_name_ext;

    /** Array that contain the name of month in short format
     *   @access private
     */
    var $month_name_con;

    /** General purpose use
     *   @access private
     */
    var $data_from;

    /** General purpose use
     *   @access private
     */
    var $data_to;

    /** Used for errors
     *  @access private
     */
    var $error;

    /** Variable for choose long or short day names format
     *  @access public
     */
    var $abbr;

    /** Set to desidered language
     *  @access public
     */
    var $lan;

    /** Version number
     *   @access private
     */
    var $version = '0.7';

    /**
     * 	Set to desidered date format
     * 	@access public
     * + possible values:
     * + it -> italian (dd-mm-yyyy)
     * + en,std -> international (mm-dd-yyyy)
     * + ansi -> used in dbase and other source (yyyy-mm-dd)
     */
    var $date_format;

    /**
     * 	Set to desidered time format
     * 	@access public
     * + possible values:
     * + 24,it -> italian format 24H ie: 20.17, 9.11
     * + 12,en -> international 12H ie: 8.17PM, 9.11AM
     */
    var $time_format;

    /** Char for separating date
     *   @access public
     */
    var $separator;

    /** Constructor
     *   @access public
     *   @see Krono::$date_format
     *   @param string $lan The language to use for month/day names
     *   @param string $date_format the format for date
     *   @param char $separator Character to use as date separator
     *   @return void
     */
    function __construct($lan = 'it', $date_format = 'it', $time_format = 'it', $separator = '/') {
        $this->lan = $lan;
        $this->date_format = $date_format;
        $this->separator = $separator;
        $this->time_format = $time_format;
        $this->error = '';

        $this->_check_ini_file();
    }

    /** Function that check if ini files for languages exists
     *    @access private
     *    @reutrn void
     */
    function _check_ini_file() {
        if (file_exists('_long_day.ini'))
            $this->day_name_ext = parse_ini_file('_long_day.ini', TRUE);

        if (file_exists('_short_day.ini'))
            $this->day_name_con = parse_ini_file('_short_day.ini', TRUE);

        if (file_exists('_long_month.ini'))
            $this->month_name_ext = parse_ini_file('_long_month.ini', TRUE);

        if (file_exists('_short_month.ini'))
            $this->month_name_con = parse_ini_file('_short_month.ini', TRUE);

//        $this->exit_on_error();
    }

    /** Function that exit with the error message if given
     *   @access private
     *   @return void
     */
    function exit_on_error() {
        if ($this->error != '') {
            echo ' [Fatal Error] <b>' . $this->error . '</b> ';
            exit;
        }
    }

    /** Return the last modified date of class file
     *   @access private
     *   @return string The formatted date of this file last modified field
     */
    function _update() {
        $s = stat(__FILE__);
        return $this->k_date('%l %d %F %Y', $s[9]);
    }

    /** Return the format string for date function according to date_format parameter and separator
     *   @access private
     *   @return string
     */
    function _format() {
        switch ($this->date_format) {
            case 'ansi':
                if (!$this->abbr)
                    return 'Y' . $this->separator . 'm' . $this->separator . 'd';
                else
                    return 'Y' . $this->separator . 'n' . $this->separator . 'j';
                break;
            case 'ptb':
            case 'pt-br':
            case 'pt':
            case 'br':
            case 'it':
                if (!$this->abbr)
                    return 'd' . $this->separator . 'm' . $this->separator . 'Y';
                else
                    return 'j' . $this->separator . 'n' . $this->separator . 'Y';
                break;
            case 'en':
            case 'std':
                if (!$this->abbr)
                    return 'm' . $this->separator . 'd' . $this->separator . 'Y';
                else
                    return 'n' . $this->separator . 'j' . $this->separator . 'Y';
                break;
            default: $this->error = 'Date Format not recognized, must be "ansi", "it", "std" or "en" !! ';
                $this->exit_on_error();
        }
    }

    /**
     *  Return the literal name of language code
     *  @access private
     *  @return string The long name of language format
     */
    function _language() {
        switch ($this->lan) {
            case 'it': return 'Italian';
                break;
            case 'en': return 'English';
                break;
            case 'de': return 'Deutch';
                break;
            case 'fr': return 'French';
                break;
            case 'es': return 'Spanish';
                break;
            case 'id': return 'Indonesian';
                break;
            case 'no': return 'Norway';
                break;
            case 'jp': return 'Japanese';
                break;
            case 'fi': return 'Finnish';
                break;
            case 'nl': return 'Dutch';
                break;
            case 'ptb': return 'Portugues/Brasil';
                break;
            default: return 'Language not recognized!!';
        }
    }

    /** Print out some debug information
     *    @access: private
     *    @return void
     */
    function _debug() {
        echo '<span style="font-family:helvetica,verdana,serif;font-size:12px;color:darkgray;">
					<b>Debug Information</b><br>
					Format of Date: <i>' . $this->_format() . '</i><br>
   					Date Separator: <i>' . $this->separator . '</i><br>
					Language: <i>' . $this->_language() . '</i>
		  			</span>
					<br><hr size="1px" width="50%" color="black" align="left"><br>
					';
    }

    /** Print out a disclaimer
     *    @access private
     *    @return void
     */
    function _disclaimer() {
        echo '<span style="font-family:helvetica,verdana,serif;font-size:14px;color:#ff9900;">';
        echo '<b>KronoClass</b> v. ' . $this->version . ' <br>';
        echo '<i>Copyright (c) 2002-2003 by Tommaso D\'Argenio &lt;<a href="mailto:rajasi@ziobudda.net" title="Send me an email">rajasi@ziobudda.net</a>&gt;<br>';
        echo 'Last modified on: ' . $this->_update() . '</i><br><hr size="1px" width="50%" color="black" align="left"></span><br>';
    }

    /** Return if a given time is daylight saving or not
     *    @access private
     *    @return int 1 if time is daylight saving 0 otherwise.
     */
    function _is_daylight($time) {
        list($dom, $dow, $month, $hour, $min) = explode(":", date("d:w:m:H:i", $time));

        if ($month > 4 && $month < 10) {
            $retval = 1;        # May thru September
        } elseif ($month == 4 && $dom > 7) {
            $retval = 1;        # After first week in April
        } elseif ($month == 4 && $dom <= 7 && $dow == 0 && $hour >= 2) {
            $retval = 1;        # After 2am on first Sunday ($dow=0) in April
        } elseif ($month == 4 && $dom <= 7 && $dow != 0 && ($dom - $dow > 0)) {
            $retval = 1;        # After Sunday of first week in April
        } elseif ($month == 10 && $dom < 25) {
            $retval = 1;        # Before last week of October
        } elseif ($month == 10 && $dom >= 25 && $dow == 0 && $hour < 2) {
            $retval = 1;        # Before 2am on last Sunday in October
        } elseif ($month == 10 && $dom >= 25 && $dow != 0 && ($dom - 24 - $dow < 1)) {
            $retval = 1;        # Before Sunday of last week in October
        } else {
            $retval = 0;
        }

        return($retval);
    }

    /** Convert the name of a day in its numerical value.
     *    + i.e.: Monday stay for 0, Saturday stay for 6
     *    @access public
     *    @param string $day The name of day, short or long.
     *    @return int the number of day
     */
    function day_to_n($day) {
        if ($day == '' || strlen($day) < 3) {
            $this->error = 'Day name not valid!';
            $this->exit_on_error();
        }

        $day = ucwords($day);
        if (strlen($day) == 3)
            $ar = $this->day_name_con[$this->lan];
        else
            $ar = $this->day_name_ext[$this->lan];

        if (in_array($day, $ar)) {
            $k = array_keys($ar, $day);
            return($k[0]);
        }
    }

    /** Convert the numerical value of a day in its name for the setted language by constructor.
     *    + Short o long format is choosed by setting the abbr value to true o false
     *    @access public
     *    @param int $day The number of day, 0 stay for Sunday and 6 for Saturday
     *    @return string the name of day in language setted by constructor
     */
    function n_to_day($day) {
        if ($day > 6 || $day < 0) {
            $this->error = 'Day range not valid. Must be 0 to 6!';
            $this->exit_on_error();
        }

        if ($this->abbr === true)
            return($this->day_name_con[$this->lan][$day]);
        elseif ($this->abbr != true)
            return($this->day_name_ext[$this->lan][$day]);
    }

    /** Convert the name of a month in its numerical value.
     *    + i.e.: February stay for 2, December stay for 12
     *    @access public
     *    @param string $month The name of month, short or long format, in language setted by constructor
     *    @return int The number rappresenting the month
     */
    function month_to_n($month) {
        if ($month == '' || strlen($month) < 3) {
            $this->error = 'Month name not valid!';
            $this->exit_on_error();
        }

        $month = ucwords($month);
        if (strlen($month) == 3)
            $ar = $this->month_name_con[$this->lan];
        else
            $ar = $this->month_name_ext[$this->lan];

        if (in_array($month, $ar)) {
            $k = array_search($month, $ar);
            return($k + 1);
        } else
            return -1;
    }

    /** Convert the numerical value of a month in its name.
     *    + Short o long format is choosed by setting the abbr value to true o false
     *    @access public
     *    @param string $month The number of month
     *    @return string The name of month in language setted by constructor
     */
    function n_to_month($month) {
        if ($month > 12 || $month < 1) {
            $this->error = 'Month range not valid. Must be 1 to 12!';
            $this->exit_on_error();
        }

        if ($this->abbr === true)
            return($this->month_name_con[$this->lan][$month - 1]);
        elseif ($this->abbr != true)
            return($this->month_name_ext[$this->lan][$month - 1]);
    }

    /** Define if the day of date given is equal to day given.
     *    + Is Friday the 22nd of November 2002 ?
     *    + date according to date_format parameter passed on inizialization
     *    @access public
     *    @param date $data The date to check
     *    @param string $day The name of day to check
     *    @return mixed 1 if check is true, otherwise the day of date
     */
    function is_day($data, $day) {
        $data = str_replace('-', '/', $data);
        $data = str_replace('.', '/', $data);
        $exp = explode('/', $data);

        $data_unix = $this->k_mktime($exp);
        $giorno_unix = date('w', $data_unix);

        if (!is_numeric($day)) {
            $day = $this->day_to_n($day);
        }

        if ($giorno_unix == $day)
            return 1;
        else
            return $this->n_to_day($giorno_unix);
    }

    /** Trasform a classical date format in unix timestamp format.
     *    + date according to date_format and time_format parameter passed on inizialization
     *    + Remember that unix timestamp is the amount of seconds since 1/1/1970
     *    @access public
     *    @param date $date The date to transform
     *    @return timestamp The date transformed in timestamp
     */
    function date_to_timestamp($date) {
        if (strstr($date, ' ')) {
            $t = explode(' ', $date);
            $date = $t[0];
            $time = explode(':', $t[1]);
        } else
            $time = '';
        $date = str_replace('-', '/', $date);
        $date = str_replace('.', '/', $date);
        $exp = explode('/', $date);
        if ($time != '') {
            $exp[3] = (isset($time[0])) ? $time[0] : 0;
            $exp[4] = (isset($time[1])) ? $time[1] : 0;
            $exp[5] = (isset($time[2])) ? $time[2] : 0;
        }
        return $this->k_mktime($exp);
    }

    /** Define what's the day difference between two given date.
     *    + date according to date_format parameter passed on inizialization
     *    @access public
     *    @param date $data_ini The start date
     *    @param date $data_fin The end date
     *    @return int The difference in days between the two given dates
     */
    function days_diff($data_ini, $data_fin) {
        $data_ini = str_replace('-', '/', $data_ini);
        $data_ini = str_replace('.', '/', $data_ini);
        $data_fin = str_replace('-', '/', $data_fin);
        $data_fin = str_replace('.', '/', $data_fin);

        $exp_ini = explode('/', $data_ini);
        $exp_fin = explode('/', $data_fin);

        $ini = date('z', $this->k_mktime($exp_ini));
        $fin = date('z', $this->k_mktime($exp_fin));

        $days = floor(($this->k_mktime($exp_fin) - $this->k_mktime($exp_ini)) / (60 * 60 * 24));
        return $days;
    }

    /**
     * 	Give the difference between two times.
     * 	+ (i.e.: how minutes from 4.50 to 12.50?).
     * 	+ Accept only 24H format.
     * 	+ the time is a string like: "4.50" or "4:50"
     *   @access public
     *   @param string $time_from The start time
     * 	@param string $time_to The end time
     *   @param char $result_in The format of result
     * 	+ "m" -> for minutes
     * 	+ "s" -> for seconds
     * 	+ "h" -> for hours
     *   @return string The difference between times according to format given in $result_in
     */
    function times_diff($time_from, $time_to, $result_in = "m") {
        if ((strstr($time_from, '.') || strstr($time_from, ':')) && (strstr($time_to, '.') || strstr($time_to, ':'))) {
            $time_from = str_replace(':', '.', $time_from);
            $time_to = str_replace(':', '.', $time_to);

            $t1 = explode('.', $time_from);
            $t2 = explode('.', $time_to);

            $h1 = $t1[0];
            $m1 = $t1[1];

            $h2 = $t2[0];
            $m2 = $t2[1];

            if ($h1 <= 24 && $h2 <= 24 && $h1 >= 0 && $h2 >= 0 && $m1 <= 59 && $m2 <= 59 && $m1 >= 0 && $m2 >= 0) {
                $diff = ($h2 * 3600 + $m2 * 60) - ($h1 * 3600 + $m1 * 60);
                if ($result_in == "s")
                    return $diff;
                elseif ($result_in == "m") {
                    return $diff / 60;
                } elseif ($result_in == "h") {
                    $r = $diff / 3600;
                    $t = explode('.', $r);
                    $h = $t[0];
                    if ($h > 24)
                        $h -= 24;
                    $m = round("0.$t[1]" * 60);
                    return $h . 'h' . $m . 'm';
                }
            }
            else {
                $this->error = 'Time range not valid. Must be 0 to 24 for hours and 0 to 59 for minutes!';
                $this->exit_on_error();
            }
        } else {
            $this->error = 'Time format not valid. Must be in format HH:mm or HH.mm';
            $this->exit_on_error();
        }
    }

    /**
     * 	Add some minutes or hours to a given time.
     * 	+ i.e.: (add 2 hours to 14.10 -> result is 16.10)
     * 	+ Accept only 24H format.
     * 	+ the time is a string like: "4.50" or "4:50"
     *   @param string $time The time string to transform
     * 	@param int $add The hours or minutes to add
     * 	@param char $what is what add to time
     * 	+ "m" -> for add minutes
     * 	+ "h" -> for add hours
     * 	+ "t" -> for add time string given in HH:mm format
     * 	@return string Result is in format HH:mm, return -1 on error
     */
    function times_add($time, $add, $what) {
        if ((strstr($time, '.') || strstr($time, ':'))) {
            $time = str_replace(':', '.', $time);
            $t1 = explode('.', $time);
            $h1 = $t1[0];
            $m1 = $t1[1];
            if ($h1 <= 24 && $h1 >= 0 && $m1 <= 59 && $m1 >= 0) {
                if ($what == "m") {
                    $res = ($h1 * 60) + $m1 + $add;
                    $r = $res / 60;
                    $t = explode('.', $r);
                    $h = $t[0];
                    if ($h > 24)
                        $h -= 24;
                    $m = round("0.$t[1]" * 60);
                    return $h . ':' . $m;
                }
                elseif ($what == "h") {
                    $res = ($h1 * 60) + $m1 + ($add * 60);
                    $r = $res / 60;
                    $t = explode('.', $r);
                    $h = $t[0];
                    if ($h > 24)
                        $h -= 24;
                    $m = round("0.$t[1]" * 60);
                    return $h . ':' . $m;
                }
                elseif ($what == "t") {
                    if ((strstr($add, '.') || strstr($add, ':'))) {
                        $add = str_replace(':', '.', $add);
                        $t1 = explode('.', $add);
                        $h2 = $t1[0];
                        $m2 = $t1[1];
                        if ($h2 <= 24 && $h2 >= 0 && $m2 <= 59 && $m2 >= 0) {
                            $res = ($h1 * 60) + ($h2 * 60) + $m1 + $m2;
                            $r = $res / 60;
                            $t = explode('.', $r);
                            $h = $t[0];
                            if ($h > 24)
                                $h -= 24;
                            $m = round("0.$t[1]" * 60);
                            return $h . ':' . $m;
                        }
                    }
                    else {
                        $this->error = 'Time format not valid. Must be in format HH:mm or HH.mm';
                        $this->exit_on_error();
                    }
                }
            } else {
                $this->error = 'Time range not valid. Must be 0 to 24 for hours and 0 to 59 for minutes!';
                $this->exit_on_error();
            }
        } else {
            $this->error = 'Time format not valid. Must be in format HH:mm or HH.mm';
            $this->exit_on_error();
        }
    }

    /** Define how days left to given date. date according to date_format parameter passed on inizialization
     *    @access public
     *    @param date $data The date in traditional format for calculating diff
     *    @return int The amount of days between today and given date
     */
    function how_to($data) {
        $data = str_replace('-', '/', $data);
        $data = str_replace('.', '/', $data);
        $exp = explode('/', $data);
        $data_unix = $this->k_mktime($exp);
        $diff = $data_unix - time();
        if ($data_unix > time())
            return (date("z", $diff));
        else {
            $this->error = 'Cannot perform calculation on past time!';
            $this->exit_on_error();
        }
    }

    /** Define how many days (give it in name format) are in period given.
     *    + i.e.: How friday are from Nov,1 2002 to Mar,23 2003 ?
     *    @access public
     *    @param date $data_ini The start date
     *    @param date $data_fin The end date
     *    @param string $day The name of day for calculating on
     *    @return int The number of day in the period given
     */
    function how_days($data_ini, $data_fin, $day) {
        $data_ini = str_replace('-', '/', $data_ini);
        $data_ini = str_replace('.', '/', $data_ini);

        $eta_data = $this->days_diff($data_ini, $data_fin);

        $giorno = (int) $this->atom_date($data_ini, 'd');
        $mese = (int) $this->atom_date($data_ini, 'm');
        $anno = (int) $this->atom_date($data_ini, 'Y');

        $count = 0;

        for ($i = 0; $i < $eta_data; $i++) {
            $data = date($this->_format(), mktime(0, 0, 0, $mese, $giorno + $i, $anno));
            if ($this->is_day($data, $day) === 1)
                $count++;
        }
        return $count;
    }

    /** Work like php native mktime() but with date accordingly to format used
     *    @access private
     *    @param array $exp The date to transform
     *    @return timestamp The timestamp calculated on date given
     */
    function k_mktime($exp) {
        (isset($exp[3])) ? $h = $exp[3] : $h = 0;
        (isset($exp[4])) ? $m = $exp[4] : $m = 0;
        (isset($exp[5])) ? $s = $exp[5] : $s = 0;
        switch ($this->date_format) {
            case 'ansi': return mktime($h, $m, $s, $exp[1], $exp[2], $exp[0]);
                break; // using YYYY-MM-DD
            case 'it': return mktime($h, $m, $s, $exp[1], $exp[0], $exp[2]);
                break; // using DD-MM-YYYY
            case 'std': return mktime($h, $m, $s, $exp[0], $exp[1], $exp[2]);
                break; // using MM-DD-YYYY
            case 'en': return mktime($h, $m, $s, $exp[0], $exp[1], $exp[2]);
                break; // using MM-DD-YYYY
            default: $this->error = 'Date Format not recognized, must be "ansi", "it", "std" or "en" !! ';
                $this->exit_on_error();
        }
    }

    /**
     * 	Return a single component of given date according to format in date_format
     *   date example with hour: 03/05/2003 23:43:00 (use only ':' as time separator)
     *   @access public
     *   @return date
     *   @param date to extract atom from
     *   @param atom ->
     * 		 	+	'm' for return month;
     * 			+	'd' for return day;
     * 			+	'y' for return last two number of year
     * 			+	'Y' for return entire year
     * 			+	'h' for hours
     * 			+	'i' for minutes
     * 			+	's' for seconds
     */
    function atom_date($date, $atom) {
        if (strlen($date) <= 10) {
            $date .= ' 00:00:00';
        }

        $t = explode(' ', $date);
        $exp1 = explode('/', $t[0]);
        $exp2 = explode(':', $t[1]);
        $exp = array_merge($exp1, $exp2);
        // Extract only time
        switch ($atom) {
            case 'h': return $exp[3];
                break;
            case 'i': return $exp[4];
                break;
            case 's': return $exp[5];
                break;
        }
        // Extract day,month and year
        switch ($this->date_format) {
            case 'ansi': {
                    switch ($atom) {
                        case 'd': return $exp[2];
                            break;
                        case 'm': return $exp[1];
                            break;
                        case 'y': return substr($exp[0], 2, 2);
                            break;
                        case 'Y': return $exp[0];
                            break;
                        default: $this->error = 'Atom not recognized, must be "d", "m", "y" or "Y" !!';
                            $this->exit_on_error();
                    }
                    break;
                }
            case 'it': {
                    switch ($atom) {
                        case 'd': return $exp[0];
                            break;
                        case 'm': return $exp[1];
                            break;
                        case 'y': return substr($exp[2], 2, 2);
                            break;
                        case 'Y': return $exp[2];
                            break;
                        default: $this->error = 'Atom not recognized, must be "d", "m", "y" or "Y" !!';
                            $this->exit_on_error();
                    }
                    break;
                }
            case 'en':
            case 'std': {
                    switch ($atom) {
                        case 'd': return $exp[1];
                            break;
                        case 'm': return $exp[0];
                            break;
                        case 'y': return substr($exp[2], 2, 2);
                            break;
                        case 'Y': return $exp[2];
                            break;
                        default: $this->error = 'Atom not recognized, must be "d", "m", "y" or "Y" !!';
                            $this->exit_on_error();
                    }
                    break;
                }
            default: $this->error = 'Date Format not recognized, must be "ansi", "it", "std" or "en" !! ';
                $this->exit_on_error();
        }
    }

    /** Date like function. Using the same format functionality
     *  @access public
     *  @return string The date according with format given
     *  @param string format ->
     * 	+ valid format parameter:
     * 	+ %l (L lowercase): Day textual long
     * 	+ %d: Day of month, 2 digits with leading zeros
     * 	+ %F: Month textual Long
     * 	+ %Y: Year, 4 digits
     * 	+ %y: Year, 2 digits
     * 	+ %m: Month numeric, 2 digits with leading zeros
     * 	+ %D: Day textual short
     * 	+ %M: Month textual short
     * 	+ %n: Month numeric, without leading zeros
     * 	+ %j: Day of month, without leading zeros
     *  @param timestamp $timestamp The time to transform
     */
    function k_date($format = "%l %d %F %Y", $timestamp = 0) {
        if ($timestamp == 0)
            $timestamp = time();


        if (!preg_match('/\%l|\%F|\%D|\%M/', $format)) {
            return date(str_replace('%', '', $format), $timestamp);
        } else {
            $out = $format;
            if (strstr($format, '%l')) {
                $this->abbr = false;
                $out = str_replace('%l', $this->n_to_day(date('w', $timestamp)), $out);
            }
            if (strstr($format, '%F')) {
                $this->abbr = false;
                $out = str_replace('%F', $this->n_to_month(date('m', $timestamp)), $out);
            }
            if (strstr($format, '%D')) {
                $this->abbr = true;
                $out = str_replace('%D', $this->n_to_day(date('w', $timestamp)), $out);
            }
            if (strstr($format, '%M')) {
                $this->abbr = true;
                $out = str_replace('%M', $this->n_to_month(date('m', $timestamp)), $out);
            }
            if (strstr($format, '%Y'))
                $out = str_replace('%Y', date('Y', $timestamp), $out);
            if (strstr($format, '%y'))
                $out = str_replace('%y', date('y', $timestamp), $out);
            if (strstr($format, '%d'))
                $out = str_replace('%d', date('d', $timestamp), $out);
            if (strstr($format, '%m'))
                $out = str_replace('%m', date('m', $timestamp), $out);
            if (strstr($format, '%n'))
                $out = str_replace('%n', date('n', $timestamp), $out);
            if (strstr($format, '%j'))
                $out = str_replace('%j', date('j', $timestamp), $out);

            return $out;
        }
    }

    /* Perform operation like sum or subtraction on date
     *  @access public
     *  @return date The date transformed by calc
     *  @param  string $operator Operator may be
     *  +  '+' -> for sum
     *  +  'sum' -> for sum
     *  +  'add' -> for sum
     *  +  '-' -> for subtraction
     *  +  'sub'-> for subtraction
     *  +  'sot'-> for subtraction
     *  @param  date $date The date to calc on
     *  @param  string $operand is a number plus '%D' for days, '%M' for months, '%Y' for years
     *  + Example:
     * 	- Add 1 month to a date:
     * 	- $obj->operation('+','10/01/2003','1%M');
     *
     * 	- Subtract 20 days from a date:
     * 	- $obj->operation('-','10/01/2003','20%D');
     */

    function operation($operator, $date, $operand) {
        // Thanks to Tim Hodson tim@trundlie.fsnet.co.uk - Begin Change
        if (is_array($date)) { //to take a date as an array rather than hardcoded string
            $date2 = $this->k_mktime($date);
            $ts = $date2;
        } elseif (is_string($date)) { // to handle an original string
            $ts = $this->date_to_timestamp($date);
        } elseif (is_integer($date)) { // to handle a timestamp
            $ts = $date;
        }
        // end change of Tim

        if (!strstr($operand, '%')) {
            $this->error = 'Bad operand type!!';
            $this->exit_on_error();
        }

        $t = explode('%', $operand);
        $how = $t[0];

        switch ($t[1]) {
            case 'D': {
                    if ($operator == '+' || $operator == 'sum' || $operator == 'add') {
                        return date($this->_format(), mktime(0, 0, 0, date('m', $ts), date('d', $ts) + $how, date('Y', $ts)));
                    } elseif ($operator == '-' || $operator == 'sub' || $operator == 'sot') {
                        return date($this->_format(), mktime(0, 0, 0, date('m', $ts), date('d', $ts) - $how, date('Y', $ts)));
                    } else {
                        $this->error = 'Operator not recognized!!';
                        $this->exit_on_error();
                    }
                    break;
                }
            case 'M': {
                    if ($operator == '+' || $operator == 'sum' || $operator == 'add') {
                        return date($this->_format(), mktime(0, 0, 0, date('m', $ts) + $how, date('d', $ts), date('Y', $ts)));
                    } elseif ($operator == '-' || $operator == 'sub' || $operator == 'sot') {
                        return date($this->_format(), mktime(0, 0, 0, date('m', $ts) - $how, date('d', $ts), date('Y', $ts)));
                    } else {
                        $this->error = 'Operator not recognized!!';
                        $this->exit_on_error();
                    }
                    break;
                }
            case 'Y': {
                    if ($operator == '+' || $operator == 'sum' || $operator == 'add') {
                        return date($this->_format(), mktime(0, 0, 0, date('m', $ts), date('d', $ts), date('Y', $ts) + $how));
                    } elseif ($operator == '-' || $operator == 'sub' || $operator == 'sot') {
                        return date($this->_format(), mktime(0, 0, 0, date('m', $ts), date('d', $ts), date('Y', $ts) - $how));
                    } else {
                        $this->error = 'Operator not recognized!!';
                        $this->exit_on_error();
                    }
                    break;
                }
            default: {
                    $this->error = 'Bad operand type!!';
                    $this->exit_on_error();
                }
        }
    }

    /** Return the timestamp from a NIST TIME SERVER on the net. Get the atomic time!
     *   + attention
     *   + have to stay on line for work!!
     *   @access public
     *   @return timestamp The timestamp from internet
     */
    function net_timestamp($server = 'time-a.nist.gov', $port = 37) {
        if ($fp = fsockopen($server, $port, $errno, $errstr, 25)) {
            fputs($fp, "\n");
            $timevalue = fread($fp, 49);
            fclose($fp);
        } else {
            $this->error = $server . ' Time Server unavailable or u\'re not connected on the net!!';
            $this->exit_on_error();
        }

        $ts = (abs(hexdec('7fffffff') - hexdec(bin2hex($timevalue)) - hexdec('7fffffff')) - 2208988800);
        return $ts;
    }

    /** Returns the current time in swatch .beat format. Remember that 1000 beats = 24 hours
     *    @access public
     *    @return string The swatch beat time
     */
    function swatch_time() {
        $offset = 60;
        $beat_division = 24 * 60 / 1000;
        $current_date = getdate(time());
        $hour = $current_date["hours"];
        $minute = $current_date["minutes"];
        $seconds = $current_date["seconds"];
        $total_minutes = $minute + $offset + $hour * 60;
        $beats = round($total_minutes / $beat_division);
        if ($beats >= 1000) {
            $beats = $beats % 1000;
        }
        return ("@" . $beats);
    }

    /** Transform a MySQL like timestamp to a readable format (and viceversa)
     *    + ie: 20011210002745 -> December 10, 2001, 12:27 am
     *  @access public
     *  @return string The timestamp or the date in readable format
     *  @param timestamp $timestamp The mysql timestamp or date
     *  @param string $mode mysqlfrom (convert mysql timestamp to readable format)
     *  @param string $mode mysqlto (convert a date in readable format to mysql timestamp)
     */
    function mysql_time_easy($timestamp, $mode = 'mysqlfrom') {
        $formated = '';

        if ($mode == 'mysqlfrom') {
            $hour = substr($timestamp, 8, 2);
            $minute = substr($timestamp, 10, 2);
            $second = substr($timestamp, 12, 2);
            $month = substr($timestamp, 4, 2);
            $day = substr($timestamp, 6, 2);
            $year = substr($timestamp, 0, 4);
            $mktime = mktime($hour, $minute, $second, $month, $day, $year);
            $format = $this->_format();
            $f = explode($this->separator, $format);
            $format = '%' . $f[0] . $this->separator . '%' . $f[1] . $this->separator . '%' . $f[2] . ' %g:%i %a';
            $formated = $this->k_date($format, $mktime);
        } else {
            $data = str_replace('-', '/', $timestamp);
            $data = str_replace('.', '/', $data);
            $m = $this->atom_date($data, 'm');
            $d = $this->atom_date($data, 'd');
            $Y = $this->atom_date($data, 'Y');
            $h = $this->atom_date($data, 'h');
            $i = $this->atom_date($data, 'i');
            $s = $this->atom_date($data, 's');
            $formated = $Y . $m . $d . $h . $i . $s;
        }
        return $formated;
    }

    /** Get the date of Nth day of the month ..
     *    + example: what is the date of the 2nd Sunday of April 2003 ???
     *  @access public
     *  @return date The date
     *  @param int $number The ordinal value to get date
     *  @param string $weekday The name of day given in Long or short format
     *  @param mixed $month the name or number of month
     *  @param int $year the year number
     */
    function get_nth_day($number, $weekday, $month, $year = 0) {
        if ($number > 5) {
            $this->error = 'There isn\'t more than 5 ' . $weekday . ' in a month, usually!!';
            $this->exit_on_error();
        }

        $date_counter = 1;
        $week_counter = 0;

        if ($year == 0)
            $year = date('Y');

        if (strlen($weekday) > 3)
            $format_dow = '%l';
        else
            $format_dow = '%D';

        if (!is_numeric($month)) {
            $month = $this->month_to_n($month);
        }

        do {
            $itsit = mktime(0, 0, 0, $month, $date_counter, $year);
            $dow = $this->k_date($format_dow, $itsit);
            if ($dow == $weekday) {
                $week_counter++;
            }

            if (($week_counter == $number) && ($weekday == $dow)) {
                $week_counter = $number;
                if ($date_counter > 1) // Thanks to Maurizio Marini <maumar@datalogica.com>
                    $date_counter--;
            }
            else {
                $date_counter++;
            }
        } while ($week_counter < $number);

        $itsit = mktime(0, 0, 0, $month, $date_counter + 1, $year);
        $format = $this->_format();
        $f = explode($this->separator, $format);
        $format = '%' . $f[0] . $this->separator . '%' . $f[1] . $this->separator . '%' . $f[2];

        if ($this->k_date('n', $itsit) != $month) {
            $this->error = 'Bad request, try again!!';
            $this->exit_on_error();
        } else {
            return $this->k_date($format, $itsit);
        }
    }

    /** Return the date in ancient roman date format
     *   + note: the date is output in the form: ddmmyyyy without separator.Support maximum to 5000 years!!!
     *  @access public
     *  @return string The date in roman format
     *  @param date $date The date to transform
     */
    function roman_date($date) {
        $unit = array(0 => "", 1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX');
        $tens = array(0 => "", 10 => 'X', 20 => 'XX', 30 => 'XXX', 40 => 'XL', 50 => 'L', 60 => 'LX', 70 => 'LXX', 80 => 'LXXX', 90 => 'XC');
        $hund = array(0 => "", 100 => 'C', 200 => 'CC', 300 => 'CCC', 400 => 'CD', 500 => 'D', 600 => 'DC', 700 => 'DCC', 800 => 'DCCC', 900 => 'CM');
        $thou = array(0 => "", 1000 => 'M', 2000 => 'MM', 3000 => 'MMM', 4000 => 'MMMM', 5000 => 'MMMMM');

        if ($date == '') {
            $date = date('d/m/Y', time());
            $old_state = $this->date_format;
            $this->date_format = 'it';
            $year = $this->atom_date($date, 'Y');
            $month = $this->atom_date($date, 'm');
            $day = $this->atom_date($date, 'd');
            $this->date_format = $old_state;
        } else {
            $year = $this->atom_date($date, 'Y');
            $month = $this->atom_date($date, 'm');
            $day = $this->atom_date($date, 'd');
        }

        $y_thou = substr($year, -4, 1) * 1000;
        $y_hund = substr($year, -3, 1) * 100;
        $y_tens = substr($year, -2, 1) * 10;
        $y_unit = substr($year, -1, 1);

        $m_tens = substr($month, -2, 1) * 10;
        $m_unit = substr($month, -1, 1);

        $d_tens = substr($day, -2, 1) * 10;
        $d_unit = substr($day, -1, 1);

        return $tens[$d_tens] . $unit[$d_unit] .
                $tens[$m_tens] . $unit[$m_unit] .
                $thou[$y_thou] . $hund[$y_hund] . $tens[$y_tens] . $unit[$y_unit];
    }

    /** Returns an array with all the phases of the moon for a whole year
     *  @access public
     * 	@param int $Y is the year to get moon phases.
     *  @return array The moonphase for each day
     */
    function year_moon_phases($Y) {
        //Converted from Basic by Roger W. Sinnot, Sky & Telescope, March 1985.
        //Converted from javascript by Are Pedersen 2002
        //Javascript found at http://www.stellafane.com/moon_phase/moon_phase.htm

        $R1 = 3.14159265 / 180;
        $U = false;
        $s = ""; // Formatted Output String
        $K0 = intval(($Y - 1900) * 12.3685);
        $T = ($Y - 1899.5) / 100;
        $T2 = $T * $T;
        $T3 = $T * $T * $T;
        $J0 = 2415020 + 29 * $K0;
        $F0 = 0.0001178 * $T2 - 0.000000155 * $T3;
        $F0 += (0.75933 + 0.53058868 * $K0);
        $F0 -= (0.000837 * $T + 0.000335 * $T2);
        //X In the Line Below, F is not yet initialized, and J is not used before it set in the FOR loop.
        //X J += intval(F); F -= INT(F);
        //X Ken Slater, 2002-Feb-19 on advice of Pete Moore of Houston, TX
        $M0 = $K0 * 0.08084821133;
        $M0 = 360 * ($M0 - intval($M0)) + 359.2242;
        $M0 -= 0.0000333 * $T2;
        $M0 -= 0.00000347 * $T3;
        $M1 = $K0 * 0.07171366128;
        $M1 = 360 * ($M1 - intval($M1)) + 306.0253;
        $M1 += 0.0107306 * $T2;
        $M1 += 0.00001236 * $T3;
        $B1 = $K0 * 0.08519585128;
        $B1 = 360 * ($B1 - intval($B1)) + 21.2964;
        $B1 -= 0.0016528 * $T2;
        $B1 -= 0.00000239 * $T3;
        for ($K9 = 0; $K9 <= 28; $K9 = $K9 + 0.5) {
            $J = $J0 + 14 * $K9;
            $F = $F0 + 0.765294 * $K9;
            $K = $K9 / 2;
            $M5 = ($M0 + $K * 29.10535608) * $R1;
            $M6 = ($M1 + $K * 385.81691806) * $R1;
            $B6 = ($B1 + $K * 390.67050646) * $R1;
            $F -= 0.4068 * sin($M6);
            $F += (0.1734 - 0.000393 * $T) * sin($M5);
            $F += 0.0161 * sin(2 * $M6);
            $F += 0.0104 * sin(2 * $B6);
            $F -= 0.0074 * sin($M5 - $M6);
            $F -= 0.0051 * sin($M5 + $M6);
            $F += 0.0021 * sin(2 * $M5);
            $F += 0.0010 * sin(2 * $B6 - $M6);
            $F += 0.5 / 1440; //Adds 1/2 minute for proper rounding to minutes per Sky & Tel article
            $J += intval($F);
            $F -= intval($F);
            //Convert from JD to Calendar Date
            $julian = $J + round($F);
            $s = jdtogregorian($julian);
            //half K
            if (($K9 - floor($K9)) > 0) {
                if (!$U) {
                    //New half
                    $phases[$s] = "New Half";
                } else {
                    //Full half
                    $phases[$s] = "Full Half";
                }
            } else {
                //full K
                if (!$U) {
                    $phases[$s] = "New Moon";
                } else {
                    $phases[$s] = "Full Moon";
                }
                $U = !$U;
            }
        } // Next
        return $phases;
    }

//End MoonPhase

    /*
      Public: Return the Moon phase for given date, if no date is present refer to present day
     */

    function today_moon_phase($date = '') {
        if (!isset($date) || $date == '') {
            $time = time();
        } else
            $time = $this->date_to_timestamp($date);

        $moon_phases = $this->year_moon_phases(date("Y", $time));
        $day = $this->k_date("%n/%j/%Y", $time);
        $k = array_keys($moon_phases);
        $ab = $this->abbr;
        $this->abbr = true;
        while (!$key = array_search($day, $k)) {
            $day = $this->operation('-', $day, '1%D');
        }
        $this->abbr = $ab;
        return $moon_phases[$day];
    }

    /**
     * 	Return the sunset or sunrise for the given date and geo location.
     * + note: the method called without parameter return the sunrise and sunset of Rome (Italy) with daylight actived
     * 	@access public
     * 	@return string
     * 	@param int $latitude Stay for latitude
     *  @param int $longitude Stay for longitude
     * 	@param int $timezone Is the timezone referred to GMT
     *  + (ie: for Italy is 1, for Spain is -1 and so on)
     * 	@param string $location If don't know the geographical coordinates of your city give the name of location. (Support only Italian cities)
     * 	@param int $daylight
     *  + '1' or 'on' if the location use daylight saving time
     * 	+ '0' or 'off' if the location doesn't use daylight saving time
     *  @param string $date The date to calc sunset/sunrise on
     */
    function get_sun_time($latitude = 0, $longitude = 0, $timezone = 1, $location = 'IT|Roma', $daylight = 1, $date = '') {
        $result = '';
        if (!isset($date) || $date == '') {
            $time = time();
        } else
            $time = $this->date_to_timestamp($date);

        if ($location != '' && ($latitude == 0 || $longitude == 0)) {
            require_once '_it_geo_coord.dat.php';
            $location = strtoupper($location);
            if (in_array($location, array_keys($_geo_coord))) {
                $exp = explode('|', $_geo_coord[$location]);
                $latitude = $exp[0];
                $longitude = $exp[1];
            }
        }

        $yday = date('z', $time);
        $mon = date('n', $time);
        $mday = date('j', $time);
        $year = date('Y', $time);


        $DST = $this->_is_daylight($time);
        if ($DST) {
            $timezone = ($timezone + 1);
        }

        if ($timezone == "13") {
            $timezone = "-11";
        }

        $A = 1.5708;
        $B = 3.14159;
        $C = 4.71239;
        $D = 6.28319;
        $E = 0.0174533 * $latitude;
        $F = 0.0174533 * $longitude;
        $G = 0.261799 * $timezone;

        # For astronomical twilight, use R = -.309017
        # For     nautical twilight, use R = -.207912
        # For        civil twilight, use R = -.104528
        # For     sunrise or sunset, use R = -.0145439
        $R = -.0145439;

        for ($i = 0; $i < 2; $i++) {

            if (!$i) {
                $J = $A;
                $type = "rise";
            }    # calculate sunrise
            else {
                $J = $C;
                $type = "set";
            }    # calculate sunset

            $K = $yday + (($J - $F) / $D);
            $L = ($K * .017202) - .0574039;              # Solar Mean Anomoly
            $M = $L + .0334405 * sin($L);                # Solar True Longitude
            $M += 4.93289 + (3.49066E-04) * sin(2 * $L);
            # Quadrant Determination
            if ($D == 0) {
                $this->error = "Trying to normalize with zero offset...";
                $this->exit_on_error();
            }

            while ($M < 0) {
                $M = ($M + $D);
            }

            while ($M >= $D) {
                $M = ($M - $D);
            }

            if (($M / $A) - intval($M / $A) == 0) {
                $M += 4.84814E-06;
            }

            $P = sin($M) / cos($M);                   # Solar Right Ascension
            $P = atan2(.91746 * $P, 1);

            # Quadrant Adjustment
            if ($M > $C) {
                $P += $D;
            } else {
                if ($M > $A) {
                    $P += $B;
                }
            }

            $Q = .39782 * sin($M);            # Solar Declination
            $Q = $Q / sqrt(-$Q * $Q + 1);     # This is how the original author wrote it!
            $Q = atan2($Q, 1);

            $S = $R - (sin($Q) * sin($E));
            $S = $S / (cos($Q) * cos($E));

            if (abs($S) > 1) {
                $result .= 'none';
            }     # Null phenomenon

            $S = $S / sqrt(-$S * $S + 1);
            $S = $A - atan2($S, 1);

            if ($type == 'rise') {
                $S = $D - $S;
            }

            $T = $S + $P - 0.0172028 * $K - 1.73364; # Local apparent time
            $U = $T - $F;                            # Universal timer
            $V = $U + $G;                            # Wall clock time
            # Quadrant Determination
            if ($D == 0) {
                $this->error = "Trying to normalize with zero offset...";
                $this->exit_on_error();
            }

            while ($V < 0) {
                $V = ($V + $D);
            }

            while ($V >= $D) {
                $V = ($V - $D);
            }

            $V = $V * 3.81972;

            $hour = intval($V);
            $min = intval((($V - $hour) * 60) + 0.5);

            $result .= "sun$type is at: ";

            if ($this->time_format == '12' || $this->time_format == 12 || $this->time_format = 'en')
                $result .= date("g:i A", mktime($hour, $min, 0, $mon, $mday, $year));
            else
                $result .= date("H:i", mktime($hour, $min, 0, $mon, $mday, $year));

            $result .= '&nbsp;-&nbsp;';
        }
        return $result;
    }

    function pg_time_easy($timestamp) {
        $Date_Time = explode(" ", $timestamp);
        $date_pieces = explode("-", $Date_Time[0]);
        $time_convert = explode("-", $Date_Time[1]);
        $time_pieces = explode(":", $time_convert[0]);

        $timestamp = mktime($time_pieces[0], $time_pieces[1], $time_pieces[2], $date_pieces[1], $date_pieces[2], $date_pieces[0]);
        return $this->k_date('', $timestamp);
    }

    /**
     * Return the literal value of a unix timestamp or seconds
     * + i.e.: so 3670 seconds mean 1 hour, 1 minute and 10 seconds.
     * @param int $seconds Number of seconds to transform.
     * @param char $format The format of output,
     *                              +"h" for hours.minutes.seconds (short form)
     *                              +"d" for year.months.days.etc (short form)
     *                              +"H" for hours.minutes.seconds (long form)
     *                              +"D" for year.months.days.etc (long form)
     * @access public
     * @return string The seconds transformed in text
     */
    function time_to_text($seconds, $format = "h") {
        $hours = 0;
        $minutes = 0;
        $days = 0;

        if ($seconds <= 60) {
            $hours = 0;
            $minutes = 0;
        } elseif ($seconds >= 60 && $seconds < 3600) {
            $hours = 0;
            $minutes = $seconds / 60;
            $t = explode('.', $minutes);
            $minutes = $t[0];
            $seconds = round("0.$t[1]" * 60);
        } elseif ($seconds >= 3600 && $seconds < 86400) {
            $r = $seconds / 3600;
            $t = explode('.', $r);
            $hours = $t[0];
            if ($hours > 24)
                $hours -= 24;

            $minutes = "0.$t[1]" * 60;
            $t = explode('.', $minutes);
            $minutes = $t[0];
            $seconds = round("0.$t[1]" * 60);
        }
        elseif ($seconds >= 86400) {
            $r = $seconds / 86400;
            $t = explode('.', $r);
            $days = $t[0];
            $hours = "0.$t[1]" * 24;
            if (!strstr($hours, ".")) {
                $minutes = 0;
                $seconds = 0;
            } else {
                $t = explode(".", $hours);
                $hours = $t[0];
                $mi = "0.$t[1]" * 60;
                if (!strstr($mi, ".")) {
                    $minutes = $mi;
                    $seconds = 0;
                } else {
                    $t = explode(".", $mi);
                    $minutes = $t[0];
                    $seconds = round("0.$t[1]" * 60);
                }
            }
        }

        if ($hours > 1)
            $hours .= ($format == 'h' || $format == 'd') ? 'h ' : 'hours ';
        else
            $hours .= ($format == 'h' || $format == 'd') ? 'h ' : 'hour ';

        if ($minutes > 1)
            $minutes .= ($format == 'h' || $format == 'd') ? 'm ' : 'minutes ';
        else
            $minutes .= ($format == 'h' || $format == 'd') ? 'm ' : 'minute ';

        if ($seconds > 1)
            $seconds .= ($format == 'h' || $format == 'd') ? 's ' : 'seconds ';
        else
            $seconds .= ($format == 'h' || $format == 'd') ? 's ' : 'second ';

        if ($days > 1)
            $days .= ($format == 'h' || $format == 'd') ? 'd ' : 'days ';
        else
            $days .= ($format == 'h' || $format == 'd') ? 'd ' : 'day ';

        $timestr = $hours . $minutes . $seconds;
        $datestr = $days;

        if ($format == "d" || $format == "D")
            return $datestr . $timestr;
        else
            return $timestr;
    }

    /**
     * Function to turn seconds into a time
     * + added by tim@trundlie.fsnet.co.uk on 08/21/2003
     * + i.e. 30600sec is 8.30am
     * +		63000sec is 17:30
     * @access public
     * @param int $secs number of seconds to be converted to time of day.
     * @return string The seconds converted into time
     */
    function secs_to_time($secs) {
        if ($secs == 0) {
            return "-empty-";
        } else {
            $hours = round(floor(($secs / 3600)), 0); // just the whole hours
            $minutes = $secs % 3600;  //remainder
            $minutes = $minutes / 60;

            if ($minutes == 0) { // tidy up output
                $minutes = '00';
            }

            return $hours . ":" . $minutes;
        }
    }

    /**
     *  Function that check the validity of a date and/or time
     *  + in according with date_format and time_format
     *  + suggested by Vincenzo Visciano <liberodicrederci@yahoo.it>
     *  @access public
     *  @param string $date The date and/or time to check validity of
     *  @return bool True if is all ok, False is all wrong, -1 if only date is wrong, -2 if only time is wrong
     */
    function is_valid($date) {
        if (strstr($date, ':')) {
            if (strstr($date, " ")) {
                $t = explode(' ', $date);
                $time = $t[1];
                $date = explode($this->separator, $t[0]);
            } else {
                $time = $date;
                $date = "";
            }
        } else
            $date = explode($this->separator, $date);

        $time = (isset($time)) ? explode(':', $time) : "";

        if (isset($time) && $time != "") {
            $hour = $time[0];
            $mins = (isset($time[1])) ? $time[1] : 0;
            $seconds = (isset($time[2])) ? $time[2] : 0;
        } else {
            $time = 1;
            $hour = 0;
            $mins = 0;
            $seconds = 0;
        }

        if ($date != "") {
            switch ($this->date_format) {
                case 'ansi': $year = $date[1];
                    $month = $date[2];
                    $day = $date[0];
                    break; // using YYYY-MM-DD
                case 'it': $day = $date[1];
                    $month = $date[0];
                    $year = $date[2];
                    break; // using DD-MM-YYYY
                case 'std': $month = $date[0];
                    $day = $date[1];
                    $year = $date[2];
                    break; // using MM-DD-YYYY
                case 'en': $month = $date[0];
                    $day = $date[1];
                    $year = $date[2];
                    break; // using MM-DD-YYYY
                default: $this->error = 'Date Format not recognized, must be "ansi", "it", "std" or "en" !! ';
                    $this->exit_on_error();
            }
        }

        switch ($this->time_format) {
            case 'it':
            case 24:
            case '24':
                if ($hour >= 0 && $hour <= 24 && $mins >= 0 && $mins <= 59 && $seconds >= 0 && $seconds <= 59)
                    $time = 1;
                else
                    $time = 0;
                break;
            case 'en':
            case '12':
            case 12:
                if ($hour >= 0 && $hour <= 12 && $mins >= 0 && $mins <= 59 && $seconds >= 0 && $seconds <= 59)
                    $time = 1;
                else
                    $time = 0;
                break;
            default: $this->error = 'Time Format not recognized, must be "it" or "24", "en" or "12" !! ';
                $this->exit_on_error();
        }
        if ($date != "")
            $date = checkdate($month, $day, $year);
        else
            $date = true;

        if ($date && $time)
            return true;
        elseif (!$date)
            return -1;
        elseif (!$time)
            return -2;
        elseif (!$date && !$time)
            return false;
    }

    /**
     *  Function that give some information on a date
     *  + in according with date_format
     *  @access public
     *  @param string $date The date to extract info
     *  @param string $what What info to extract from date given (in any case)
     *                               + "monthname" give the name of month
     *                               + "dayname" give the name of day
     *                               + "dayofweek" give the ordinal number of day in the week (1 for Sunday, 1 for Monday, and so on)
     *                               + "dayofyear" give the ordinal number of day in the year
     *                               + "week" give the number of week in the year
     *                               + "trimester" give the number of trimester in the year
     *  @param string $format For textual information, specify if out in the short or long format
     *                                 +"short" or "s" for the short format (default)
     *                                 +"long" or "l" for the long format
     *  @return string The information asked
     */
    function get_info($date, $what, $format = "short") {
        $result = false;
        if ($this->is_valid($date)) {
            if ($format == "short" || $format == "s")
                $this->abbr = true;
            elseif ($format == "long" || $format == "l")
                $this->abbr = false;
            switch (strtolower($what)) {
                case 'monthname':
                    $m = $this->atom_date($date, 'm');
                    $result = $this->n_to_month($m);
                    break;
                case 'dayname':
                    $time = $this->date_to_timestamp($date);
                    $weekday = date("w", $time);
                    $result = $this->n_to_day($weekday);
                    break;
                case 'dayofweek':
                    $time = $this->date_to_timestamp($date);
                    $weekday = date("w", $time);
                    $result = $weekday + 1;
                    break;
                case 'dayofyear':
                    $time = $this->date_to_timestamp($date);
                    $result = date("z", $time) + 1;
                    break;
                case 'week':
                    $time = $this->date_to_timestamp($date);
                    $week = date("W", $time);
                    $result = $week;
                    break;

                case 'trimester':
                    $m = $this->atom_date($date, 'm');
                    if ($m > 1 && $m <= 3)
                        $result = 1;
                    elseif ($m > 3 && $m <= 6)
                        $result = 2;
                    elseif ($m > 6 && $m <= 9)
                        $result = 3;
                    elseif ($m > 9 && $m <= 12)
                        $result = 4;
                    break;

                default: $this->error = 'You must specify what kind of info need!';
            }
        } else
            $this->error = 'Date Format not recognized, must be "ansi", "it", "std" or "en" !! ';

        $this->exit_on_error();
        return $result;
    }

}