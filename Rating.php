<?php
/*
Rating plugin for Typsetter СMS
Author: a2exfr
http://my-sitelab.com/
Version 1.0.1 */

defined('is_running') or die('Not an entry point...');

class Rating{

	static function SectionTypes($section_types){
		$section_types['Rating_section'] = [];
		$section_types['Rating_section']['label'] = 'Rating';
		return $section_types;
	}

	static function NewSections($links){
		global $addonRelativeCode;
		foreach ($links as $key => $link) {
			$match = is_array($link[0]) ? implode('-', $link[0]) : $link[0];
			if ($match == 'Rating_section'){
				$links[$key][1] = $addonRelativeCode . '/img/rating.png';
			}
		}
		return $links;
	}

	static function InlineEdit_Scripts($scripts, $type){
		if ($type !== 'Rating_section'){
			return $scripts;
		}
		global $addonRelativeCode;
		$scripts[] = $addonRelativeCode . '/js/rate_edit.js';
		return $scripts;
	}

	static function SaveSection($return, $section, $type){
		if ($type != 'Rating_section'){
			return $return;
		}
		global $page;
		$page->file_sections[$section]['starnum'] = &$_POST['starnum'];
		$page->file_sections[$section]['showrate'] = &$_POST['showrate'];

		return true;
	}

	static function DefaultContent($default_content, $type){
		if ($type != 'Rating_section'){
			return $default_content;
		}
		global $addonRelativeCode;
		$section = [];
		$section['rate_id'] = "rate" . crc32(uniqid("", true));
		$section['gp_label'] = "Rating";
		$section['content'] = '<div class="rateit" data-rateit-mode="font" data-rateit-icon="" style="font-family:fontawesome">
 </div>';
		$section['addonRelativeCode'] = $addonRelativeCode;
		$section['starnum'] = 5;
		$section['showrate'] = '';
		return $section;
	}

	static function SectionToContent($section_data){
		if ($section_data['type'] != 'Rating_section'){
			return $section_data;
		}
		global $addonPathData;

		$ratetid = $section_data['rate_id'];
		$starnum = $section_data['starnum'];

		$rating_file = $addonPathData . '/rating_' . $ratetid . '.php';
		$rate_data = gpFiles::Get($rating_file, 'rate_data');
		if (!array_key_exists('current_rating', $rate_data)){
			$rate_data['current_rating'] = 0;
		}
		if (!array_key_exists('ip', $rate_data)){
			$rate_data['ip'] = [];
		}

		$voted = false;
		if (array_key_exists($ratetid, $_COOKIE)){
			$voted = true;
		}

		$ip = self::GetIP();

		if ($ip == 'unknown'){
			$voted = true;
		} elseif (in_array($ip, $rate_data["ip"])) {
			$voted = true;
		}

		$r = ($voted) ? 'data-rateit-readonly="true"' : "";

		ob_start();

		echo '<div  id="' . $ratetid . '" class="rateit" data-rateit-max="' . $starnum . '" data-ratetid="' . $ratetid . '" data-rateit-value="' . $rate_data["current_rating"] . '"  ' . $r . ' data-rateit-mode="font" data-rateit-icon="" style="font-family:fontawesome">';
		if ($section_data['showrate']){
			echo '	<p class="current_rate">' . $rate_data["current_rating"] . '</p>';
		} else {
			echo '	<p class="current_rate"></p>';
		}
		echo '	</div>';

		$section_data['content'] = ob_get_clean();

		return $section_data;
	}

	static function HookHead(){
		global $page, $addonRelativeCode;
		common::LoadComponents('fontawesome');
		$page->head_js[] = $addonRelativeCode . '/lib/jquery.rateit.min.js';
		$page->head_js[] = $addonRelativeCode . '/js/rate.js';
		$page->css_user[] = $addonRelativeCode . '/lib/rateit.css';
		$page->css_user[] = $addonRelativeCode . '/css/rate.css';

	}

	static function PageRunScript($cmd){
		global $page, $addonRelativeCode, $addonPathData;

		if ($cmd == 'rating_section'){

			$page->ajaxReplace = [];

			$ratetid = &$_REQUEST['ratetid'];
			$rating_file = $addonPathData . '/rating_' . $ratetid . '.php';

			$rate_value = &$_REQUEST['rate_value'];
			$rate_value = filter_var($rate_value, FILTER_VALIDATE_FLOAT);
			if (!is_numeric($rate_value)){
				die('Invalid rating value!');
			}
			$rate_value = (string)$rate_value;

			$rate_data = gpFiles::Get($rating_file, 'rate_data');

			if (!array_key_exists('rating', $rate_data)){
				$rate_data['rating'] = [];
			}

			if (array_key_exists($rate_value, $rate_data['rating'])){
				$rate_data['rating'][$rate_value]++;
			} else {
				$rate_data['rating'][$rate_value] = 1;
			}

			if (array_key_exists('votes_count', $rate_data)){
				$rate_data['votes_count']++;
			} else {
				$rate_data['votes_count'] = 1;
			}

			$temp = 0;
			foreach ($rate_data['rating'] as $rate_value => $rate_voted) {
				$temp = $rate_voted * $rate_value + $temp;
			}

			$rate_data['current_rating'] = round($temp / $rate_data['votes_count'], 2);

			setcookie($ratetid, 'true', time() + (10 * 365 * 24 * 60 * 60));
			$rate_data['ip'][] = self::GetIP();

			gpFiles::SaveData($rating_file, 'rate_data', $rate_data);

			$arg_value = [];
			$arg_value['current_rating'] = $rate_data['current_rating'];
			$arg_value['ratetid'] = $ratetid;

			$page->ajaxReplace[] = ['respond_rating_section', 'arg', $arg_value];

			return 'return';
		}

		if ($cmd == 'refresh_rating_section'){
			$page->ajaxReplace = [];

			$starnum = &$_REQUEST['starnum'];
			$rate_id = &$_REQUEST['rate_id'];
			$showrate = &$_REQUEST['showrate'];
			$section_options = [
				'type' => "Rating_section", 'rate_id' => $rate_id, 'starnum' => $starnum,
				'showrate' => $showrate,
			];
			$arg_value = \gp\tool\Output\Sections::SectionToContent($section_options, '');
			$page->ajaxReplace[] = ['refresh_respond_rating_section', 'arg', $arg_value];
			return 'return';
		}
		return $cmd;
	}

	static function GetIP(){
		if (isset($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		} else {
			return 'unknown';
		}
	}
}

?>