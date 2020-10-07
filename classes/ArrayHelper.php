<?php

namespace Ion;

/**
 * Class ArrayHelper
 *
 * @author https://github.com/amensum
 * @package Ion
 */
class ArrayHelper
{

    /**
     * @param $pattern
     * @param $input
     * @param int $flags
     * @return array
     * @use Selects the array elements whose keys match the pattern. Returns a new array.
     * @example pregGrepKeys('/^str_/', $array);
     */
    public static function pregGrepKeys($pattern, $input, $flags = 0): array
	{
        return array_filter($input, static function ($key) use ($pattern, $flags) {
            return preg_match($pattern, $key, $flags);
        }, ARRAY_FILTER_USE_KEY);
    }

	/**
	 * @param $prefix
	 * @param $new_prefix
	 * @param $array
	 * @return array
	 * @use Replaces the prefix in the keys of the array with a new one. Returns a new array.
	 * @example replaceKeysPrefix('STR_', 'str_', $array);
	 */
    public static function replaceKeysPrefix($prefix, $new_prefix, $array): array
	{
        $new_array = [];
        array_walk($array,
            static function ($val, $key) use (&$new_array, $prefix, $new_prefix) {
                if (strpos($key, $prefix) === 0) {
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
    public static function mapToArray($array): array
	{
        $new_array = [];
        foreach ($array as $el) {
            $new_array[$el[0]] = $el[1];
        }
        return $new_array;
    }
}
