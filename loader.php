<?php
/*
Plugin Name: MU Broader Impacts - National Membership Database
Version: 1.0
Author: Michael C. Barbaro (CARES)
Description: This plugin creates a custom post type, taxonomies, form and map for the Broader Impacts National Membership Database.
*/

	
add_action( 'init', 'register_cpt_bi_natl_membership' );

function register_cpt_bi_natl_membership() {

    $labels = array( 
        'name' => _x( 'BI Natl Members', 'bi_natl_member' ),
        'singular_name' => _x( 'BI Natl Member', 'bi_natl_member' ),
        'add_new' => _x( 'Add New BI Natl Member', 'bi_natl_member' ),
        'all_items' => _x( 'BI Natl Members', 'bi_natl_member' ),
        'add_new_item' => _x( 'Add New BI Natl Member', 'bi_natl_member' ),
        'edit_item' => _x( 'Edit BI Natl Member', 'bi_natl_member' ),
        'new_item' => _x( 'New BI Natl Member', 'bi_natl_member' ),
        'view_item' => _x( 'View BI Natl Member', 'bi_natl_member' ),
        'search_items' => _x( 'Search BI Natl Members', 'bi_natl_member' ),
        'not_found' => _x( 'No BI Natl Members found', 'bi_natl_member' ),
        'not_found_in_trash' => _x( 'No BI Natl Members found in Trash', 'bi_natl_member' ),
        'parent_item_colon' => _x( 'Parent BI Natl Members:', 'bi_natl_member' ),
        'menu_name' => _x( 'BI Natl Members', 'bi_natl_member' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
		'supports' => array(  ),
		'taxonomies' => array( 'post_tag' )		
    );

    register_post_type( 'bi_natl_member', $args );
}

function bi_natl_member_search_scripts() {
		wp_enqueue_script( 'bi_natl_member_search2', plugin_dir_url(__FILE__) . 'js/masked_input.js', array('jquery'), '1.0.0', true ); 
	wp_enqueue_script( 'bi_natl_member_search', plugin_dir_url(__FILE__) . 'js/bi-natlmember-search.js', array('jquery'), '1.0.0', true ); 
	wp_enqueue_script( 'bi_natl_member_search3', plugin_dir_url(__FILE__) . 'js/markerclusterer.js', array('jquery'), '1.0.0', true );

	// wp_enqueue_script( 'bi_natl_member_search', plugin_dir_url(__FILE__) . '/js/jquery.tablesorter.js', array('jquery'), '1.0.0', true ); 	
	wp_enqueue_style( 'bi_natl_member_search', plugin_dir_url(__FILE__) . 'css/tables.css', array(), '1.32' );
	
	$translation_array = array( 'siteUrl' => get_site_url() );
	wp_localize_script( 'bi_natl_member_search', 'object1', $translation_array );
	wp_localize_script( 'bi_natl_member_search', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));	
}

function bi_natl_member_google_maps_script() {	
		wp_register_script( 'bi-natlmember-google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAj8tXV6eDAAU9a9p6RhkYns8JciFwF-W8&callback=initialize', array(), '1.0.0', true );	
}
add_action('wp_enqueue_scripts', 'bi_natl_member_google_maps_script');

function bi_natl_member_form_creation() {
	bi_natl_member_search_scripts();
	
	$bi_admins = array(
		"barbarom@missouri.edu",
		"vassmers@missouri.edu",
		"renoes@missouri.edu",
		"gillos@missouri.edu",
		"svq87@mail.missouri.edu"			
	);
	$current_user = wp_get_current_user();
	$current_email = $current_user->user_email;
	$allowed = false;
	if (in_array($current_email,$bi_admins)) {
		$allowed = true;
	}
	
	wp_enqueue_script( 'bi-natlmember-google-maps' );

	ob_start();		
?>

			
			<div id="buttonsdiv" style="border-bottom:solid 1px #e0e0e0;height:40px;padding-top:30px;">
				<div style="float:left;"><button id="newsearch">New Search</button></div>
				<div style="float:right;margin-left:15px;"><button id="addnewmember">Add Yourself as a Member</button></div>
				<?php
					//********CHANGE THIS TO SUIT THE ROLE OF SOMEONE WHO CAN APPROVE memberS, IF NEEDED*********
					if ($allowed) {
				?>
					
					<form id="recalc_form" name="recalc_form" action="" method="post" onsubmit="return alert('Map Coordinates have been re-calculated!')">
						<div style="float:right;"><input type="submit" id="runlatlongcalc" name="runlatlongcalc" value="Re-calculate Map Coordinates" /></div>
					</form>		
				<?php
					}
				?>		
			</div>
			
			<div id="searchdiv">
				<h2>Search Members</h2>
				<form id="searchform" action="" method="get">
					<input type="text" id="keywordsearch" name="keywordsearch" placeholder="Search keywords here..." style="width:400px" /><br /><br />

					<button type="submit">Search</button>				
				</form>
			</div>
			<div id="resultsdiv" style="display:none">
				<h2>Member Results</h2>
				<div id="resultsfound"></div>		
				<div id="showmapdiv"><button id="showmap" type="button">Map the Results</button></div>
				<br />
				<div id="resultboxes">
					<ul id="resultlist"></ul>
				</div>
				<div id="resulttable">
					<div id="rtable"></div>
				</div>
			</div>		
			<div id="mapdiv" style="display:none">	
				<br />
				<div>
					<div style="float:left;"><h2>Member Locations</h2></div>
					<div style="float:right;"><button id="maptolist">Return to List</button></div>
				</div>	
				<br /><br />		
				<div id="map" style="width:950px;height:600px;"></div>
			</div>
			<div id="approvediv" style="display:none">
				<h2>Approve Member(s)</h2>
				<?php		
					$approval_args = array(
						'post_type' => 'bi_natl_member',
						'posts_per_page' => -1,
						'post_status' => 'draft'
					);			
					$approval_query = new WP_Query( $approval_args );
					while ( $approval_query->have_posts() ) {
						$approval_query->the_post();
						$approval_postid = get_the_ID();
						$approval_title = get_the_title();
						$skillset = get_the_content();
						$officename = get_post_meta( $approval_postid, 'officename', true );
						$institution = get_post_meta( $approval_postid, 'institution', true );
						$audience = get_post_meta( $approval_postid, 'audience', true );
						$phone = get_post_meta( $approval_postid, 'phone', true );
						$email = get_post_meta( $approval_postid, 'email', true );
						$streetaddress = get_post_meta( $approval_postid, 'street_address', true );
						$city = get_post_meta( $approval_postid, 'city', true );
						$state = get_post_meta( $approval_postid, 'state', true );
						$zipcode = get_post_meta( $approval_postid, 'zip_code', true );		
						echo "<div id='" . $approval_postid . "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#FAF0E6;width:70%;max-width:70%;'><div style='float:right;'><button style='margin-left:5px;' onclick='approvemember(" . $approval_postid . ")'>Approve</button></div><div style='float:right;'><button style='margin-left:5px;' onclick='deletemember(" . $approval_postid . ")'>Delete</button></div><strong style='font-size:14pt;'>" . $approval_title . "</strong><br /><strong>Phone:</strong> " . $phone . "<br /><strong>Email:</strong> <a href='mailto:" . $email . "'>" . $email . "</a><br /><strong>Street Address:</strong> " . $streetaddress . "<br /><strong>City:</strong> " . $city . "<br /><strong>State:</strong> " . $state . "<br /><strong>ZIP Code:</strong> " . $zipcode . "<br /><strong>Office Name:</strong> " . $officename . "<br /><strong>Academic Institution: " . $institution . "</strong><strong>Your Skillset:</strong> " . $skillset . "<br /><strong>Audience:</strong> " . $audience . "<br /></div><br />";					
						
					}	
				?>
			</div>			
			<div id="formdiv" style="display:none">
				<h2 id="member_form_title">Add a member</h2>			
				<form id="member_form" name="member_form" action="" method="post">
					<input type="hidden" id="member_id" name="member_id" value="" />
					<strong>Name:</strong><br /><input type="text" id="membername" name="membername" style="width:400px" required /><br /><br />
					<strong>Phone:</strong><br /><input type="text" id="phone" name="phone" placeholder="(XXX) XXX-XXXX" style="width:400px" required /><br /><br />
					<strong>Email:</strong><br /><input type="text" id="email" name="email" style="width:400px" required /><br /><br />
					<strong>Street Address:</strong><br /><input type="text" id="street_address" name="street_address" style="width:400px" required /><br /><br />
					<strong>City:</strong><br /><input type="text" id="city" name="city" style="width:400px" required /><br /><br />
					<strong>State:</strong><br />
					<select id="state" name="state" required>
						<option value="" selected>---Select---</option>		
							<option value="Alabama">Alabama</option>
							<option value="Alaska">Alaska</option>
							<option value="Arizona">Arizona</option>
							<option value="Arkansas">Arkansas</option>
							<option value="California">California</option>
							<option value="Colorado">Colorado</option>
							<option value="Connecticut">Connecticut</option>
							<option value="Delaware">Delaware</option>
							<option value="District of Columbia">District of Columbia</option>
							<option value="Florida">Florida</option>
							<option value="Georgia">Georgia</option>
							<option value="Hawaii">Hawaii</option>
							<option value="Idaho">Idaho</option>
							<option value="Illinois">Illinois</option>
							<option value="Indiana">Indiana</option>
							<option value="Iowa">Iowa</option>
							<option value="Kansas">Kansas</option>
							<option value="Kentucky">Kentucky</option>
							<option value="Louisiana">Louisiana</option>
							<option value="Maine">Maine</option>
							<option value="Maryland">Maryland</option>
							<option value="Massachusetts">Massachusetts</option>
							<option value="Michigan">Michigan</option>
							<option value="Minnesota">Minnesota</option>
							<option value="Mississippi">Mississippi</option>
							<option value="Missouri">Missouri</option>
							<option value="Montana">Montana</option>
							<option value="Nebraska">Nebraska</option>
							<option value="Nevada">Nevada</option>
							<option value="New Hampshire">New Hampshire</option>
							<option value="New Jersey">New Jersey</option>
							<option value="New Mexico">New Mexico</option>
							<option value="New York">New York</option>
							<option value="North Carolina">North Carolina</option>
							<option value="North Dakota">North Dakota</option>
							<option value="Ohio">Ohio</option>
							<option value="Oklahoma">Oklahoma</option>
							<option value="Oregon">Oregon</option>
							<option value="Pennsylvania">Pennsylvania</option>
							<option value="Rhode Island">Rhode Island</option>
							<option value="South Carolina">South Carolina</option>
							<option value="South Dakota">South Dakota</option>
							<option value="Tennessee">Tennessee</option>
							<option value="Texas">Texas</option>
							<option value="Utah">Utah</option>
							<option value="Vermont">Vermont</option>
							<option value="Virginia">Virginia</option>
							<option value="Washington">Washington</option>
							<option value="West Virginia">West Virginia</option>
							<option value="Wisconsin">Wisconsin</option>
							<option value="Wyoming">Wyoming</option>		
					</select>
					<br /><br/>
					<strong>ZIP Code:</strong><br /><input type="text" id="zip_code" name="zip_code" style="width:100px" required /><br /><br/>	
					<strong>Office Name:</strong><br /><input type="text" id="officename" name="officename" style="width:400px" required /><br /><br />
					<strong>Academic Institution:</strong><br /><input type="text" id="institution" name="institution" style="width:400px" required /><br /><br />
					<strong>Your Skillset (50 words or less):</strong><br /><textarea id="skillset" name="skillset" rows="4" cols="55" required></textarea><br />Total word count: <span id="display_count">0</span> words. Words left: <span id="word_left">50</span><br /><br />
					<strong>Audiences Served:</strong><br /><input type="text" id="audience" name="audience" style="width:400px" required /><br /><br />
					<strong>Tags:</strong> <span style="font-style:italic">(separate tags with a comma)</span><br /><input type="text" id="tags" name="tags" style="width:400px" /><br /><br />
		
					<br /><br/>
					<?php if( function_exists( 'cptch_display_captcha_custom' ) ) { echo "<strong>Verification (spam prevention):</strong><br /><input type='hidden' name='cntctfrm_contact_action' value='true' />"; echo cptch_display_captcha_custom(); } ?>
					<br /><br/><input type="submit" id="submit_natlmember_form" name="submit_natlmember_form" value="Submit" /><br /><br/>				
				</form>	
			</div>
		<style>
			#map img {max-width: none !important;}
		</style>	

<?php
		if ($_POST["submit_natlmember_form"]) {
			if( function_exists( 'cptch_check_custom_form' ) && cptch_check_custom_form() !== true ) {
				echo "<br /><br /><span style='color:red'>The CAPTCHA answer is incorrect.</span><br /><br />";
			} else {
			
				if (empty($_POST['member_id'])) {			
					$post = array();


					$post = array(
							'post_content' => $_POST['skillset'],
							'post_title' => $_POST['membername'],
							'post_status' => 'publish',
							'post_type' => 'bi_natl_member'
					);

					$newmemberid = wp_insert_post( $post );

					if (!empty($_POST['phone'])) {
						add_post_meta($newmemberid, 'phone', $_POST['phone']);
					}		
					if (!empty($_POST['email'])) {
						add_post_meta($newmemberid, 'email', $_POST['email']);
					}	
					if (!empty($_POST['street_address'])) {
						add_post_meta($newmemberid, 'street_address', $_POST['street_address']);
					}	
					if (!empty($_POST['city'])) {
						add_post_meta($newmemberid, 'city', $_POST['city']);
					}	
					if (!empty($_POST['state'])) {
						add_post_meta($newmemberid, 'state', $_POST['state']);
					}	
					if (!empty($_POST['zip_code'])) {
						add_post_meta($newmemberid, 'zip_code', $_POST['zip_code']);
					}	
					if (!empty($_POST['officename'])) {
						add_post_meta($newmemberid, 'officename', $_POST['officename']);
					}	
					if (!empty($_POST['institution'])) {
						add_post_meta($newmemberid, 'institution', $_POST['institution']);
					}						
					if (!empty($_POST['audience'])) {
						add_post_meta($newmemberid, 'audience', $_POST['audience']);
					}	
					if (!empty($_POST['tags'])) {
						wp_set_post_tags( $newmemberid, $_POST['tags'] );						
					}				
				
					//Set latitude and longitude 
					$strAddress = $_POST['street_address'] . " " . $_POST['city'] . " " . $_POST['state'] . " " . $_POST['zip_code'];
					if (strlen($strAddress) > 3) {
						$geo = bi_natl_member_geocode($strAddress);
						
						if (!empty($geo)) {
							$latitude = $geo['latitude'];
							$longitude = $geo['longitude'];

							add_post_meta($newmemberid, 'bi_natl_member_lat', $latitude);
							add_post_meta($newmemberid, 'bi_natl_member_long', $longitude);								
						}
					}
					//Email admins that a new resource has been created and needs approval. Only send if created by non-admin
					if (!$allowed) {
						//Add the appropriate emails here for those who are going to approve resources.
						$to = array(						
							'vassmers@missouri.edu',
							'renoes@missouri.edu',
							'gillos@missouri.edu',
							'svq87@mail.missouri.edu'
						);
						$mailcontent = "The following member needs your approval:<br /><br /><strong>" . $_POST['membername'] . "</strong>";							
						wp_mail( $to, 'A New Broader Impacts member Needs Approval', $mailcontent );
					}					
				} else {
						$update_post_id = $_POST['member_id'];
						$post = array(
								'ID' => $update_post_id,
								'post_content' => $_POST['skillset'],
								'post_title' => $_POST['membername']						
						);
							
						wp_update_post( $post );
						
						if (!empty($_POST['phone'])) {
							update_post_meta($update_post_id, 'phone', $_POST['phone']);
						} else {
							delete_post_meta($update_post_id, 'phone');
						}
						if (!empty($_POST['email'])) {
							update_post_meta($update_post_id, 'email', $_POST['email']);
						} else {
							delete_post_meta($update_post_id, 'email');
						}
						if (!empty($_POST['street_address'])) {
							update_post_meta($update_post_id, 'street_address', $_POST['street_address']);
						} else {
							delete_post_meta($update_post_id, 'street_address');
						}
						if (!empty($_POST['city'])) {
							update_post_meta($update_post_id, 'city', $_POST['city']);
						} else {
							delete_post_meta($update_post_id, 'city');
						}
						if (!empty($_POST['state'])) {
							update_post_meta($update_post_id, 'state', $_POST['state']);
						} else {
							delete_post_meta($update_post_id, 'state');
						}
						if (!empty($_POST['zip_code'])) {
							update_post_meta($update_post_id, 'zip_code', $_POST['zip_code']);
						} else {
							delete_post_meta($update_post_id, 'zip_code');
						}
						if (!empty($_POST['officename'])) {
							update_post_meta($update_post_id, 'officename', $_POST['officename']);
						} else {
							delete_post_meta($update_post_id, 'officename');
						}
						if (!empty($_POST['institution'])) {
							update_post_meta($update_post_id, 'institution', $_POST['institution']);
						} else {
							delete_post_meta($update_post_id, 'institution');
						}						
						if (!empty($_POST['audience'])) {
							update_post_meta($update_post_id, 'audience', $_POST['audience']);
						} else {
							delete_post_meta($update_post_id, 'audience');
						}		
						if (!empty($_POST['tags'])) {
							wp_set_post_tags( $update_post_id, $_POST['tags'] );						
						} 
						
						//Set latitude and longitude 
						$strAddress = $_POST['street_address'] . " " . $_POST['city'] . " " . $_POST['state'] . " " . $_POST['zip_code'];
						if (strlen($strAddress) > 3) {
							$geo = bi_natl_member_geocode($strAddress);
							
							if (!empty($geo)) {
								$latitude = $geo['latitude'];
								$longitude = $geo['longitude'];
								//var_dump($geo['latitude']);
								update_post_meta($update_post_id, 'bi_natl_member_lat', $latitude);
								update_post_meta($update_post_id, 'bi_natl_member_long', $longitude);								
							}
						}				
			
				}
			?>
				<script>
					alert("Your entry has been saved!");
				</script>
			<?php
			
			}

		return ob_get_clean();
	}
}
add_shortcode('bi_natl_member_form', 'bi_natl_member_form_creation');

// function to geocode address, it will return false if unable to geocode address
function bi_natl_member_geocode($address){ 

    //$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address2}&key=AIzaSyDzsrDVBlOfzDeyAGpJO35qdOEFKIgT9ZA"; 

	
   $address = str_replace (" ", "+", urlencode($address));
   $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$address."&sensor=false";
 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
 
   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
   if ($response['status'] != 'OK') {
    return null;
   }
 
   //print_r($response);
   $geometry = $response['results'][0]['geometry'];
 
    $longitude = $geometry['location']['lng'];
    $latitude = $geometry['location']['lat'];
 
    $array = array(
        'latitude' => $geometry['location']['lat'],
        'longitude' => $geometry['location']['lng'],
        'location_type' => $geometry['location_type'],
    );
 
    return $array;	

}

add_action( 'wp_ajax_bi_natl_member_search', 'bi_natl_member_search_callback' );
add_action( 'wp_ajax_nopriv_bi_natl_member_search', 'bi_natl_member_search_callback' );

function bi_natl_member_search_callback() {

	$bi_admins = array(
		"barbarom@missouri.edu",
		"vassmers@missouri.edu",
		"renoes@missouri.edu",
		"gillos@missouri.edu",
		"svq87@mail.missouri.edu"		
	);
	$current_user = wp_get_current_user();
	$current_email = $current_user->user_email;
	$allowed = false;
	if (in_array($current_email,$bi_admins)) {
		$allowed = true;
	}	

	do_action("init");
	header('Content-type: application/json');
	
	$keywordsearch = sanitize_text_field( $_GET['keyword'] );

	$q1 = get_posts(array(
		'post_type' => 'bi_natl_member',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		's' => $keywordsearch
	));
	
	$q2 = get_posts(array(
		'post_type' => 'bi_natl_member',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'officename',
				'value' => $keywordsearch,
				'compare' => 'LIKE'
			),
			array(
				'key' => 'audience',
				'value' => $keywordsearch,
				'compare' => 'LIKE'
			),
			array(
				'key' => 'city',
				'value' => $keywordsearch,
				'compare' => 'LIKE'
			),			
			array(
				'key' => 'state',
				'value' => $keywordsearch,
				'compare' => 'LIKE'
			),
			array(
				'key' => 'institution',
				'value' => $keywordsearch,
				'compare' => 'LIKE'
			),	
			array(
				'key' => 'skillset',
				'value' => $keywordsearch,
				'compare' => 'LIKE'
			)			
		)		
	));
	
	$q3 = get_posts(array(
		'post_type' => 'bi_natl_member',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'tag' => $keywordsearch
	));

	$first_merge = array_merge( $q1, $q2 );
	$merged = array_merge( $first_merge, $q3);
	
	$post_ids = array();
	foreach( $merged as $item ) {
		$post_ids[] = $item->ID;
	}

	$unique = array_unique($post_ids);	
	
	$result = array();
	if (!empty($unique)) {
		$args = array(
			'post_type' => 'bi_natl_member',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post__in' => $unique
		);
		
		//Check to see if user is admin. This will determine whether the user can edit a member.
		//********CHANGE THIS TO SUIT THE ROLE OF SOMEONE WHO CAN EDIT memberS, IF NEEDED*********	
		$admin_check="";
		if ($allowed) {
			$admin_check = "YES";
		} else {
			$admin_check = "NO";
		}
		
			
		
		$member_query = new WP_Query( $args );
		
		while ( $member_query->have_posts() ) {
			$member_query->the_post();
			$postid = get_the_ID();
			$result[] = array(
				"id" => $postid,
				"title" => get_the_title(),
				"permalink" => get_permalink(),
				"skillset" => get_the_content(),
				"phone" => get_post_meta( $postid, 'phone' ),
				"email" => get_post_meta( $postid, 'email' ),
				"streetaddress" => get_post_meta( $postid, 'street_address' ),
				"city" => get_post_meta( $postid, 'city' ),
				"state" => get_post_meta( $postid, 'state' ),
				"zipcode" => get_post_meta( $postid, 'zip_code' ),
				"officename" => get_post_meta( $postid, 'officename' ),
				"institution" => get_post_meta( $postid, 'institution' ),
				"audience" => get_post_meta( $postid, 'audience' ),	
				"lat" => get_post_meta( $postid, 'bi_natl_member_lat' ),
				"lng" => get_post_meta( $postid, 'bi_natl_member_long' ),
				"admin" => $admin_check

			);
			
		}
	}
	echo json_encode($result);	
	/* Restore original Post Data */
	wp_reset_postdata();
	wp_die();
}

add_action( 'wp_ajax_bi_natl_member_edit', 'bi_natl_member_edit_callback' );
add_action( 'wp_ajax_nopriv_bi_natl_member_edit', 'bi_natl_member_edit_callback' );

function bi_natl_member_edit_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();	
	
	$queried_post = get_post($_POST['id']);
	$postid = $_POST['id'];
	
	$result[] = array(
		"id" => $postid,
		"title" => $queried_post->post_title,
		"skillset" => $queried_post->post_content,
		"phone" => get_post_meta( $postid, 'phone' ),
		"email" => get_post_meta( $postid, 'email' ),
		"streetaddress" => get_post_meta( $postid, 'street_address' ),
		"city" => get_post_meta( $postid, 'city' ),
		"state" => get_post_meta( $postid, 'state' ),
		"zipcode" => get_post_meta( $postid, 'zip_code' ),
		"officename" => get_post_meta( $postid, 'officename' ),
		"institution" => get_post_meta( $postid, 'institution' ),
		"audience" => get_post_meta( $postid, 'audience' ),
		"tags" => get_the_tags( $postid )


	);
		
	
	echo json_encode($result);	
	wp_die();	

}

add_action( 'wp_ajax_bi_natl_member_approve', 'bi_natl_member_approve_callback' );
add_action( 'wp_ajax_nopriv_bi_natl_member_approve', 'bi_natl_member_approve_callback' );

function bi_natl_member_approve_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();

	$postid = $_POST['id'];	
	wp_publish_post( $postid );
	
	$queried_post = get_post($_POST['id']);	
	
	$result[] = array(
		"id" => $postid,
		"title" => $queried_post->post_title,
		"skillset" => $queried_post->post_content,
		"phone" => get_post_meta( $postid, 'phone' ),
		"email" => get_post_meta( $postid, 'email' ),
		"streetaddress" => get_post_meta( $postid, 'street_address' ),
		"city" => get_post_meta( $postid, 'city' ),
		"state" => get_post_meta( $postid, 'state' ),
		"zipcode" => get_post_meta( $postid, 'zip_code' ),
		"officename" => get_post_meta( $postid, 'officename' ),
		"institution" => get_post_meta( $postid, 'institution' ),
		"audience" => get_post_meta( $postid, 'audience' )
	
	);
	
	echo json_encode($result);	
	wp_die();
}

add_action( 'wp_ajax_bi_natl_member_disapprove', 'bi_natl_member_disapprove_callback' );
add_action( 'wp_ajax_nopriv_bi_natl_member_disapprove', 'bi_natl_member_disapprove_callback' );

function bi_natl_member_disapprove_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();

	$postid = $_POST['id'];	
	
	$queried_post = get_post($_POST['id']);	

	$my_post = array(
      'ID'           => $postid,      
      'post_status' => 'draft'
	);

	// Update the post into the database
	  wp_update_post( $my_post );
	
	$result[] = array(
		"id" => $postid,
		"title" => $queried_post->post_title,
		"skillset" => $queried_post->post_content,
		"phone" => get_post_meta( $postid, 'phone' ),
		"email" => get_post_meta( $postid, 'email' ),
		"streetaddress" => get_post_meta( $postid, 'street_address' ),
		"city" => get_post_meta( $postid, 'city' ),
		"state" => get_post_meta( $postid, 'state' ),
		"zipcode" => get_post_meta( $postid, 'zip_code' ),
		"officename" => get_post_meta( $postid, 'officename' ),
		"institution" => get_post_meta( $postid, 'institution' ),
		"audience" => get_post_meta( $postid, 'audience' )
	
	);
	
	echo json_encode($result);	
	wp_die();
}

add_action( 'wp_ajax_bi_natl_member_delete', 'bi_natl_member_delete_callback' );
add_action( 'wp_ajax_nopriv_bi_natl_member_delete', 'bi_natl_member_delete_callback' );

function bi_natl_member_delete_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$result = array();

	$post_id = $_POST['id'];	
	
	$queried_post = get_post($_POST['id']);	
	wp_delete_post( $post_id );
	
	$result[] = array(
		"id" => $post_id,
		"title" => $queried_post->post_title
	);
	
	echo json_encode($result);	
	wp_die();
}
