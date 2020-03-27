<?php

/**
*
* @package WordPress
* Auto Post update with API DATA
* Author: Mashiur Rahman -  http://mashiurz.com
*
*/

function postUpdateApi(){

	//making request to the api
	$request = wp_remote_get("https://ostorei.com/wp-json/wl/v1/posts");

	if( is_wp_error( $request ) ) {
		return false;
	}

	//get row data from api
	$rowContent = wp_remote_retrieve_body( $request );

	//convert to Json
	$apiData = json_decode( $rowContent );

	if( ! empty( $apiData ) ) {

		foreach( $apiData as $postData ) {

	      $getPost = get_page_by_path($postData->slug, 'OBJECT', 'post');

			//check if the post is exist
	      if ( isset($getPost) && !empty($getPost) ){

				//post data to update
				$single_post = array(
	            'ID' =>  $getPost->ID,
	            'post_excerpt'  => $postData->excerpt,
	            'post_status'   => 'publish',
	            'meta_input' => array(
	              "payment" => $postData->payment,
					  "store_at_ostorei" => 'yes',
				   ),
	         );

				//update post data
	         $post_id = wp_update_post( $single_post );

				//print sucess msg after update
				echo "Post updated: ". get_permalink($post_id) . "<br> <hr>";


	      }else{
	         echo "Post not found with slug: ". $postData->slug . "<br> <hr>";
	      }

		}

	}

}


/* set schedules to run the update */
function apiPost_custom_cron_schedule( $schedules ) {
    $schedules['every_six_hours'] = array(
        'interval' => 120, // 21600 Every 6 hours
        'display'  => __( 'Every 6 hours' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'apiPost_custom_cron_schedule' );

//Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'apiPost_cron_hook' ) ) {
    wp_schedule_event( time(), 'every_six_hours', 'apiPost_cron_hook' );
}

///Hook into that action that'll fire every six hours
 add_action( 'apiPost_cron_hook', 'postUpdateApi' );



 /*if ( ! wp_next_scheduled( 'postUpdateApi' ) ) {
     wp_schedule_event( time(), 'hourly', 'postUpdateApi' );
 }*/
