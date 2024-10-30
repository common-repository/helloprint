<?php
// lists of shortcodes that values need to be replace
$shortCodeArray = [
	'site_name' => get_bloginfo('name')
];


foreach ($shortCodeArray as $key => $val) {
	$functionName = "_helloprint_get_" . $key . "_shortcode";

	//create function of each shortcode
	if (!function_exists($functionName)) {
		$functionName = function () use ($val) {
			return $val;
		};
	}

	// add shortcodes for each keys
	add_shortcode($key, $functionName);
}
