<?php

function get_sub_cats($id, $subcategory = '') {
	
	global $cat_info;
	$subfound = array ();
	
	if( $subcategory == '' ) $subcategory = $id;
	
	foreach ( $cat_info as $cats ) {
		if( $cats['parentid'] == $id ) {
			$subfound[] = $cats['id'];
		}
	}
	
	foreach ( $subfound as $parentid ) {
		$subcategory .= "|" . $parentid;
		$subcategory = get_sub_cats( $parentid, $subcategory );
	}
	
	return $subcategory;

}

function cdnhub_action($action) {

	global $baseUrl;

	return "{$baseUrl}&action={$action}";

}