<?php

namespace Ion;

/**
 * @class Util
 */
class Util {
	
	/**
	 * @param $pattern
	 * @param $input
	 * @param int $flags
	 * @return array
	 * @use Selects the array elements whose keys match the pattern. Returns a new array.
	 * @example pregGrepKeys('/^str_/', $array);
	 */
	public static function pregGrepKeys($pattern, $input, $flags = 0) {
		return array_filter($input, function($key) use ($pattern, $flags) {
			return preg_match($pattern, $key, $flags);
		}, ARRAY_FILTER_USE_KEY);
	}
	
	/**
	 * @param $prefix
	 * @param $array
	 * @return array
	 * @use Replaces the prefix in the keys of the array with a new one. Returns a new array.
	 * @example replaceKeysPrefix('STR_', 'str_', $array);
	 */
	public static function replaceKeysPrefix($prefix, $new_prefix, $array) {
		$new_array = [];
		array_walk($array,
			function ($val, $key) use (&$new_array, $prefix, $new_prefix) {
				if($prefix == substr($key, 0, strlen($prefix))) {
					$new_array[$new_prefix . substr($key, strlen($prefix))] = $val;
				} else {
					$new_array[$key] = $val;
				}
			}
		);
		return $new_array;
	}
	
	/**
	 * @param $array
	 * @return array
	 * @use Creates an array of the form [[key => value], [key => value]] from an array of the form [[key, value], [key, value]] and returns it.
	 * @example mapToArray($array);
	 */
	public static function mapToArray($array) {
		$new_array = [];
		foreach ($array as $el) {
			$new_array[$el[0]] = $el[1];
		}
		return $new_array;
	}
	
	public static function getInstagramImages($user_id, $images_count) {
		$items = [];
		$url = "https://instagram.com/graphql/query/?query_id=17888483320059182&id={$user_id}&first={$images_count}";
		$content = json_decode(file_get_contents($url));
		$nodes = $content->data->user->edge_owner_to_timeline_media->edges;
		foreach ($nodes as $obj) {
			$node = $obj->node;
			$thumbnail = $node->thumbnail_resources["1"];
			$item = ["SRC" => $thumbnail->src];
			$items[] = $item;
		}
		return $items;
	}
}
