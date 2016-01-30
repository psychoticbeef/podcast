<?php
require '/opt/predis/lib/Predis/Autoloader.php';
require_once('/usr/share/php/getid3/getid3.php');
Predis\Autoloader::register();

class track {
	private static $redis = NULL;
	public $content = array ();

	public function get_xml() {
		include 'item.tmpl';
	}

	function __construct() {
		if (self::$redis === NULL) {
			self::$redis = new Predis\Client();
		}
	}

	public function load($id) {
		$serialized = self::$redis->get($id);
		if ($serialized === NULL)
			return false;
		$this->content = unserialize($serialized);
		return true;
	}

	public function save($id) {
		self::$redis->set($id, serialize($this->content));
	}
}

function by_custom_date($a, $b) {
        if ($a->content["timestamp"] == $b->content["timestamp"]) {
                return 0;
        }
        return ($a->content["timestamp"] < $b->content["timestamp"]) ? 1 : -1;
}

function timestamp_from_preg($preg, $file) {
	$timestamp = 0;
	if (preg_match($preg, $file, $matches)) {
		$timestamp_eu = strtotime($matches[1]);
		$timestamp = $timestamp_eu;
		$timestamp_us = strtotime(str_replace('-', '/', $matches[1]));
		if ($timestamp_eu == 0 || $timestamp_eu > time()) {
			$timestamp = $timestamp_us;
		} else {
			if ($timestamp_us != 0 && $timestamp_us <= time() && $timestamp_us > $timestamp_eu) {
				$timestamp = $timestamp_us;
			}
		}
	}
	return $timestamp;
}

header("Content-type: application/rss+xml");
include 'config.php';
$redis = new Predis\Client();
$last_build_date = $redis->get("last_build_date");
$podcastFiles = scandir("./" . $podcast_directory);
date_default_timezone_set("Europe/Berlin");

foreach ($podcastFiles as $podcast) {
	if (substr($podcast, 0, 1) == ".") continue;

	$current_track = new track();
	if ($current_track->load($podcast)) {
		$tracks[] = $current_track;
		continue;
	}

	// not in database
	$last_build_date = time(); 
	$timestamp = timestamp_from_preg('/(\d\d-\d\d-\d\d\d\d)/', $podcast);
	if ($timestamp == 0) {
		$timestamp = filemtime($podcast_directory . "/" . $podcast);
	}

	$getID3 = new getID3();
	$ThisFileInfo = $getID3->analyze("./" . $podcast_directory . "/" . $podcast);
	$duration= @$ThisFileInfo['playtime_string'];

	$filename = str_replace("_", " ", $podcast);
	if (preg_match('/.*?-(.*?)-(.*?)-.*?/', $filename, $matches)) {
        	$current_track->content["author"] = trim($matches[1]);
        	$current_track->content["title"] = trim($matches[2]);
	}

	$current_track->content["subtitle"] = basename($podcast, ".mp3");
	$current_track->content["filesize"] = filesize("./" . $podcast_directory . "/" . $podcast);
	$current_track->content["url"] = $base_url . "/" . $podcast_directory . "/" . $podcast; 
	$current_track->content["duration"] = $duration;
	$current_track->content["timestamp"] = $timestamp;
	$tracks[] = $current_track;
	$current_track->save($podcast);
}

$redis->set("last_build_date", $last_build_date);
uasort($tracks, "by_custom_date");
include 'header.php';
foreach ($tracks as $track) {
	$track->get_xml();
}
include 'footer.php';
