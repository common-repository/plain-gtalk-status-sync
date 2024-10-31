<?php
/*
Plugin Name: Gtalk (Status) to WordPress (G2W)
Plugin URI: http://www.awflasher.com/blog/
Description: Show your Gtalk Status on blog, see <a href="options-general.php?page=plain-gtalk-status-sync/gtw-options.php">settings</a> for more information
Version: 0.12
Author: Aw Guo
Author URI: http://www.awflasher.com/blog/
*/

//GS2H Option Menu
add_action('admin_menu', 'gtw_menu');
function gtw_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('Gtalk Status to WordPress','Gtalk Status to WordPress', 'manage_options', dirname (__FILE__).'/gtw-options.php') ;
	}
}

//GS2H Options Initialization
register_activation_hook( __FILE__, 'gtw_init' );
function gtw_init() {
	if (!get_option('gtw_display_number'))
		add_option('gtw_display_number',10);
	if (!get_option('gtw_update_frequency'))
		add_option('gtw_update_frequency',1800);
	if (!get_option('gtw_lang'))
		add_option('gtw_lang','en');
}


// GS2H Fectch Status (REST)
// The main function to call outside of plugin
function get_gtalk_status()
{
	// Check the url
	if (get_option('gtw_badge_url'))
	{
		$gtw_badge_url	 = get_option('gtw_badge_url');
	}
	else
	{
		// Did not setup correctly
		return;
	}
	
	// Check the counting numbers
	if (get_option('gtw_display_number'))
	{
		$gtw_display_number = intval(get_option('gtw_display_number'));
		if ($gtw_display_number < 0)
		{
			$gtw_display_number = 10;
		}
	}
	else
	{
		// Did not setup correctly
		return;
	}
	
	// Check the update frequency value
	if (get_option('gtw_update_frequency'))
	{
		$gtw_update_frequency = intval(get_option('gtw_update_frequency'));
		if ($gtw_update_frequency < 0)
		{
			$gtw_update_frequency = 1800;
		}
	}
	else
	{
		// Did not setup correctly
		return;
	}
	
	
	if (get_option('gtw_cache_delay') && get_option('gtw_data')  )
	{
		$lastUpdate = intval(get_option('gtw_cache_delay'));
		if (time() - $lastUpdate > $gtw_update_frequency)
		{
			update_option('gtw_cache_delay', time());
		}
		else
		{
			// Output data from the cache
			echo '<!-- from cache, delay: '. $gtw_update_frequency .' seconds -->';
			getGtalkOption(TRUE);
			return;
		}
	}
	else
	{
		// Update the "last-modified-value"
		if (get_option('gtw_cache_delay'))
			update_option('gtw_cache_delay', time());
		else
			add_option('gtw_cache_delay', time());
	}
	
	// Send the request
	include('simple_html_dom.php');
	$ch = curl_init();
	$timeout = 5;
	curl_setopt ($ch, CURLOPT_URL, $gtw_badge_url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	$s = $file_contents;
	
	if(function_exists('str_get_html'))
	{
		$html = str_get_html($s);
		foreach($html->find('div.r') as $e) 
			continue;
	}
	
	// Insert possible new messages
	setGtalkOption(trim($e->plaintext), $gtw_display_number);
	
	// Output data
	getGtalkOption(TRUE);
	return;
	
}


//GS2H Fetch/Output Option (Local)
function getGtalkOption($display = FALSE)
{
	if ( get_option('gtw_data') )
	{
		if ( get_option('gtw_data') == '' )
		{
			return '';
		}
		$current_im_gtalk_status_string = get_option('gtw_data');
		if ($display === TRUE)
		{
			$message = unserialize($current_im_gtalk_status_string);
			$message = array_reverse($message);
			foreach ($message as $v)
			{
				if ($v['info'] == '') continue;
				echo '<li>'.$v['info'].'   <small>'.distanceOfTimeInWords(intval($v['date']), time(), TRUE).'</small></li>';
			}
		}
		else
		{
			return $current_im_gtalk_status_string;
		}
	}
	return '';
}

// Store Data
function setGtalkOption($currentInfo, $limit_number = 10)
{
	// Pickup the update info
	$gtw_t = time();
	$currentItemArray = array(
		"date" =>$gtw_t,
		"info" =>$currentInfo
		);

	// Get the local data
	$localMessage = getGtalkOption();
	
	// Check whether this is a virgin installation
	if ($localMessage == '')
	{
		// FIRST INIT
		debugInfo('first init');
		if ($currentInfo =='Offline' || $currentInfo == 'Available' || $currentInfo == '') return;
		$im_gtalk_status_arr = array();
		
		array_push($im_gtalk_status_arr, $currentItemArray);
		
		if ( get_option('gtw_data') || get_option('gtw_data') == "" )
		{
			debugInfo('update');
			update_option('gtw_data',serialize($im_gtalk_status_arr));
		}
		else
		{
			debugInfo('add');
			add_option('gtw_data',serialize($im_gtalk_status_arr));
		}
		// DEBUG INFO
		$d = serialize($im_gtalk_status_arr);
		$d = 'push'.$d;
		debugInfo($d);
		// DEBUG INFO
	}
	else
	{
		// NOT FIRST INIT
		$localArray = unserialize($localMessage);

		$lastitem = $localArray[count($localArray)-1];
		$lastItemInfo = trim($lastitem['info']);

		if (count($localArray) > $limit_number)
		{
			$localArray = array_slice($localArray, count($localArray) - $limit_number , count($localArray));
		}

		if ($lastItemInfo != $currentInfo && $currentInfo != 'Offline' && $currentInfo != 'Available')
		{
			debugInfo('update it:'.$currentInfo.'[]');
			array_push($localArray, $currentItemArray);
			if (count($localArray) > $limit_number)
			{
				$localArray = array_slice($localArray, 1, count($localArray));
			}
			update_option('gtw_data',serialize($localArray));
		}
		else
		{
			debugInfo('no change made');
		}
		update_option("gtw_data",serialize($localArray));
		
	}
}

function distanceOfTimeInWords($from_time, $to_time = 0, $include_seconds = false)
{
	$lang = get_option('gtw_lang');
	switch ($lang)
	{
		case 'cn':
		return distance_of_time_in_words_cn($from_time, $to_time, $include_seconds);
		case 'en':
		default:
		return distance_of_time_in_words_en($from_time, $to_time, $include_seconds);
	}
}

/*
    * PHP port of Ruby on Rails famous distance_of_time_in_words method.
    *  See http://api.rubyonrails.com/classes/ActionView/Helpers/DateHelper.html for more details.
    *
    * Reports the approximate distance in time between two timestamps. Set include_seconds
    * to true if you want more detailed approximations.
    *
    */
function distance_of_time_in_words_cn($from_time, $to_time = 0, $include_seconds = false)
{
	$distance_in_minutes = round(abs($to_time - $from_time) / 60);
	$distance_in_seconds = round(abs($to_time - $from_time));

	if ($distance_in_minutes >= 0 and $distance_in_minutes <= 1) {
	    if (!$include_seconds) {
	        return '1 分钟前';
	    } else {
	        if ($distance_in_seconds >= 0 and $distance_in_seconds <= 4) {
	            return '5 秒前';
	        } elseif ($distance_in_seconds >= 5 and $distance_in_seconds <= 9) {
	            return '10 秒前';
	        } elseif ($distance_in_seconds >= 10 and $distance_in_seconds <= 19) {
	            return '20 分钟前';
	        } elseif ($distance_in_seconds >= 20 and $distance_in_seconds <= 39) {
	            return '30 分钟前';
	        } elseif ($distance_in_seconds >= 40 and $distance_in_seconds <= 59) {
	            return '1 分钟前';
	        } else {
	            return '1 分钟前';
	        }
	    }
	} elseif ($distance_in_minutes >= 2 and $distance_in_minutes <= 44) {
	    return $distance_in_minutes . ' 分钟前';
	} elseif ($distance_in_minutes >= 45 and $distance_in_minutes <= 89) {
	    return '约一小时前';
	} elseif ($distance_in_minutes >= 90 and $distance_in_minutes <= 1439) {
	    return round(floatval($distance_in_minutes) / 60.0) . ' 小时前';
	} elseif ($distance_in_minutes >= 1440 and $distance_in_minutes <= 2879) {
	    return '昨天';
	} elseif ($distance_in_minutes >= 2880 and $distance_in_minutes <= 43199) {
	    return round(floatval($distance_in_minutes) / 1440) . ' 天前';
	} elseif ($distance_in_minutes >= 43200 and $distance_in_minutes <= 86399) {
	    return '约一月前';
	} elseif ($distance_in_minutes >= 86400 and $distance_in_minutes <= 525599) {
	    return round(floatval($distance_in_minutes) / 43200) . ' 个月前';
	} elseif ($distance_in_minutes >= 525600 and $distance_in_minutes <= 1051199) {
	    return '约一年前';
	} else {
	    return '约 ' . round(floatval($distance_in_minutes) / 525600) . ' 年前';
	}
}

function distance_of_time_in_words_en($from_time, $to_time = null, $include_seconds = false) {
		$to_time = $to_time? $to_time: time();
		$distance_in_minutes = floor(abs($to_time - $from_time) / 60);
		$distance_in_seconds = floor(abs($to_time - $from_time));
		$string = '';
		$parameters = array();
		if ($distance_in_minutes <= 1) {
			if (!$include_seconds) {
				$string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
			}
			else {
				if ($distance_in_seconds <= 5) {
					$string = 'less than 5 seconds';
				}
				else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10) {
					$string = 'less than 10 seconds';
				}
				else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20) {
					$string = 'less than 20 seconds';
				}
				else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40) {
					$string = 'half a minute';
				}
				else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59) {
					$string = 'less than a minute';
				}
				else {
					$string = '1 minute';
				}
			}
		}
		else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44) {
			$string = '%minutes% minutes';
			$parameters['%minutes%'] = $distance_in_minutes;
		}
		else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89) {
			$string = 'about 1 hour';
		}
		else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439) {
			$string = 'about %hours% hours';
			$parameters['%hours%'] = round($distance_in_minutes / 60);
		}
		else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879) {
			$string = '1 day';
		}
		else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199) {
			$string = '%days% days';
			$parameters['%days%'] = round($distance_in_minutes / 1440);
		}
		else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399) {
			$string = 'about 1 month';
		}
		else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959) {
			$string = '%months% months';
			$parameters['%months%'] = round($distance_in_minutes / 43200);
		}
		else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919) {
			$string = 'about 1 year';
		}
		else {
			$string = 'over %years% years';
			$parameters['%years%'] = floor($distance_in_minutes / 525960);
		}
		return strtr($string, $parameters);
	}

function debugInfo($s)
{
	echo '<!-- ' . $s . ' -->';
}
?>