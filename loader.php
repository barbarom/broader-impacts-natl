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
		'supports' => array(  )
    );

    register_post_type( 'bi_natl_member', $args );
}

add_action( 'init', 'create_binm_taxonomy' );
function create_binm_taxonomy() {
	register_taxonomy(
		'binm_tags',
		'bi_natl_member',
		array(
			'label' => 'BINM Tags',
			'hierarchical' => true,
		)
	);
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
						<div style="float:right;display:none;"><input type="submit" id="runlatlongcalc" name="runlatlongcalc" value="Re-calculate Map Coordinates" /></div>
					</form>		
				<?php
					}
				?>		
			</div>
			
			<div id="searchdiv">
				<h2>Search Members</h2>
				<form id="searchform" action="" method="get">
					<input type="text" id="keywordsearch" name="keywordsearch" placeholder="Search keywords here..." style="width:400px" /><br /><br />
					<strong>Search by tag:</strong><br />
					<?php
							$args9 = array(
								'taxonomy' => 'binm_tags',
								'orderby' => 'name',
								'field' => 'name',
								'order' => 'ASC',
								'hide_empty' => false
							);

							$binmtags = get_categories( $args9 );

							foreach ( $binmtags as $binmtag ){
								if ($binmtag->count > 0) {
									echo '<label><input type="checkbox" id="type-'. $binmtag->name . '" rel="'. $binmtag->name . '" value="' . $binmtag->term_id . '" name="searchbinmtags" class="sally"> '. $binmtag->name . '</label><br />';
								}
							}
					?>
					<br />
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
						$phone = get_post_meta( $approval_postid, 'phone', true );
						$email = get_post_meta( $approval_postid, 'email', true );
						$streetaddress = get_post_meta( $approval_postid, 'street_address', true );
						$city = get_post_meta( $approval_postid, 'city', true );
						$state = get_post_meta( $approval_postid, 'state', true );
						$country = get_post_meta( $approval_postid, 'country', true );
						$zipcode = get_post_meta( $approval_postid, 'zip_code', true );	
						$socialmedia = get_post_meta( $approval_postid, 'socialmedia', true );
						$website = get_post_meta( $approval_postid, 'website', true );						
						echo "<div id='" . $approval_postid . "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#FAF0E6;width:70%;max-width:70%;'><div style='float:right;'><button style='margin-left:5px;' onclick='approvemember(" . $approval_postid . ")'>Approve</button></div><div style='float:right;'><button style='margin-left:5px;' onclick='deletemember(" . $approval_postid . ")'>Delete</button></div><strong style='font-size:14pt;'>" . $approval_title . "</strong><br /><strong>Phone:</strong> " . $phone . "<br /><strong>Email:</strong> <a href='mailto:" . $email . "'>" . $email . "</a><br /><strong>Street Address:</strong> " . $streetaddress . "<br /><strong>City:</strong> " . $city . "<br /><strong>State:</strong> " . $state . "<br /><strong>ZIP Code:</strong> " . $zipcode . "<br /><strong>Office Name:</strong> " . $officename . "<br /><strong>Academic Institution: " . $institution . "</strong><strong>Your Skillset:</strong> " . $skillset . "<br /><strong>Audience:</strong> " . $audience . "<br /></div><br />";					
						
					}	
				?>
			</div>
			<div id="formdiv" style="display:none">
			<?php 
				if (is_user_logged_in()) {
			?>			
				<h2 id="member_form_title">Add Yourself as a Member</h2>			
				<form id="member_form" name="member_form" action="" method="post">
					<input type="hidden" id="member_id" name="member_id" value="" />
					<strong>Name*:</strong><br /><input type="text" id="membername" name="membername" style="width:400px" required /><br /><br />
					<strong>Phone*:</strong><br /><input type="text" id="phone" name="phone" placeholder="(XXX) XXX-XXXX" style="width:400px" required /><br /><br />
					<strong>Email*:</strong><br /><input type="text" id="email" name="email" style="width:400px" required /><br /><br />
					<strong>Street Address*:</strong><br /><input type="text" id="street_address" name="street_address" style="width:400px" required /><br /><br />
					<strong>City*:</strong><br /><input type="text" id="city" name="city" style="width:400px" required /><br /><br />
					<strong>State*:</strong><br />
					<select id="state" name="state" required>
						<option value="" selected>---Select---</option>	
							<option value="n/a">N/A (Outside the U.S.)</option>						
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
					<strong>ZIP Code:</strong><br /><input type="text" id="zip_code" name="zip_code" style="width:100px" /><br /><br/>
					<strong>Country:</strong><br />
					<select id="country" name="country">
						<option value="">---Select---</option>
						<option value="United States of America">United States of America</option>					
						<option value="Afganistan">Afghanistan</option>
						<option value="Albania">Albania</option>
						<option value="Algeria">Algeria</option>
						<option value="American Samoa">American Samoa</option>
						<option value="Andorra">Andorra</option>
						<option value="Angola">Angola</option>
						<option value="Anguilla">Anguilla</option>
						<option value="Antigua &amp; Barbuda">Antigua &amp; Barbuda</option>
						<option value="Argentina">Argentina</option>
						<option value="Armenia">Armenia</option>
						<option value="Aruba">Aruba</option>
						<option value="Australia">Australia</option>
						<option value="Austria">Austria</option>
						<option value="Azerbaijan">Azerbaijan</option>
						<option value="Bahamas">Bahamas</option>
						<option value="Bahrain">Bahrain</option>
						<option value="Bangladesh">Bangladesh</option>
						<option value="Barbados">Barbados</option>
						<option value="Belarus">Belarus</option>
						<option value="Belgium">Belgium</option>
						<option value="Belize">Belize</option>
						<option value="Benin">Benin</option>
						<option value="Bermuda">Bermuda</option>
						<option value="Bhutan">Bhutan</option>
						<option value="Bolivia">Bolivia</option>
						<option value="Bonaire">Bonaire</option>
						<option value="Bosnia &amp; Herzegovina">Bosnia &amp; Herzegovina</option>
						<option value="Botswana">Botswana</option>
						<option value="Brazil">Brazil</option>
						<option value="British Indian Ocean Ter">British Indian Ocean Ter</option>
						<option value="Brunei">Brunei</option>
						<option value="Bulgaria">Bulgaria</option>
						<option value="Burkina Faso">Burkina Faso</option>
						<option value="Burundi">Burundi</option>
						<option value="Cambodia">Cambodia</option>
						<option value="Cameroon">Cameroon</option>
						<option value="Canada">Canada</option>
						<option value="Canary Islands">Canary Islands</option>
						<option value="Cape Verde">Cape Verde</option>
						<option value="Cayman Islands">Cayman Islands</option>
						<option value="Central African Republic">Central African Republic</option>
						<option value="Chad">Chad</option>
						<option value="Channel Islands">Channel Islands</option>
						<option value="Chile">Chile</option>
						<option value="China">China</option>
						<option value="Christmas Island">Christmas Island</option>
						<option value="Cocos Island">Cocos Island</option>
						<option value="Colombia">Colombia</option>
						<option value="Comoros">Comoros</option>
						<option value="Congo">Congo</option>
						<option value="Cook Islands">Cook Islands</option>
						<option value="Costa Rica">Costa Rica</option>
						<option value="Cote DIvoire">Cote D'Ivoire</option>
						<option value="Croatia">Croatia</option>
						<option value="Cuba">Cuba</option>
						<option value="Curaco">Curacao</option>
						<option value="Cyprus">Cyprus</option>
						<option value="Czech Republic">Czech Republic</option>
						<option value="Denmark">Denmark</option>
						<option value="Djibouti">Djibouti</option>
						<option value="Dominica">Dominica</option>
						<option value="Dominican Republic">Dominican Republic</option>
						<option value="East Timor">East Timor</option>
						<option value="Ecuador">Ecuador</option>
						<option value="Egypt">Egypt</option>
						<option value="El Salvador">El Salvador</option>
						<option value="Equatorial Guinea">Equatorial Guinea</option>
						<option value="Eritrea">Eritrea</option>
						<option value="Estonia">Estonia</option>
						<option value="Ethiopia">Ethiopia</option>
						<option value="Falkland Islands">Falkland Islands</option>
						<option value="Faroe Islands">Faroe Islands</option>
						<option value="Fiji">Fiji</option>
						<option value="Finland">Finland</option>
						<option value="France">France</option>
						<option value="French Guiana">French Guiana</option>
						<option value="French Polynesia">French Polynesia</option>
						<option value="French Southern Ter">French Southern Ter</option>
						<option value="Gabon">Gabon</option>
						<option value="Gambia">Gambia</option>
						<option value="Georgia">Georgia</option>
						<option value="Germany">Germany</option>
						<option value="Ghana">Ghana</option>
						<option value="Gibraltar">Gibraltar</option>
						<option value="Great Britain">Great Britain</option>
						<option value="Greece">Greece</option>
						<option value="Greenland">Greenland</option>
						<option value="Grenada">Grenada</option>
						<option value="Guadeloupe">Guadeloupe</option>
						<option value="Guam">Guam</option>
						<option value="Guatemala">Guatemala</option>
						<option value="Guinea">Guinea</option>
						<option value="Guyana">Guyana</option>
						<option value="Haiti">Haiti</option>
						<option value="Hawaii">Hawaii</option>
						<option value="Honduras">Honduras</option>
						<option value="Hong Kong">Hong Kong</option>
						<option value="Hungary">Hungary</option>
						<option value="Iceland">Iceland</option>
						<option value="India">India</option>
						<option value="Indonesia">Indonesia</option>
						<option value="Iran">Iran</option>
						<option value="Iraq">Iraq</option>
						<option value="Ireland">Ireland</option>
						<option value="Isle of Man">Isle of Man</option>
						<option value="Israel">Israel</option>
						<option value="Italy">Italy</option>
						<option value="Jamaica">Jamaica</option>
						<option value="Japan">Japan</option>
						<option value="Jordan">Jordan</option>
						<option value="Kazakhstan">Kazakhstan</option>
						<option value="Kenya">Kenya</option>
						<option value="Kiribati">Kiribati</option>
						<option value="Korea North">Korea North</option>
						<option value="Korea Sout">Korea South</option>
						<option value="Kuwait">Kuwait</option>
						<option value="Kyrgyzstan">Kyrgyzstan</option>
						<option value="Laos">Laos</option>
						<option value="Latvia">Latvia</option>
						<option value="Lebanon">Lebanon</option>
						<option value="Lesotho">Lesotho</option>
						<option value="Liberia">Liberia</option>
						<option value="Libya">Libya</option>
						<option value="Liechtenstein">Liechtenstein</option>
						<option value="Lithuania">Lithuania</option>
						<option value="Luxembourg">Luxembourg</option>
						<option value="Macau">Macau</option>
						<option value="Macedonia">Macedonia</option>
						<option value="Madagascar">Madagascar</option>
						<option value="Malaysia">Malaysia</option>
						<option value="Malawi">Malawi</option>
						<option value="Maldives">Maldives</option>
						<option value="Mali">Mali</option>
						<option value="Malta">Malta</option>
						<option value="Marshall Islands">Marshall Islands</option>
						<option value="Martinique">Martinique</option>
						<option value="Mauritania">Mauritania</option>
						<option value="Mauritius">Mauritius</option>
						<option value="Mayotte">Mayotte</option>
						<option value="Mexico">Mexico</option>
						<option value="Midway Islands">Midway Islands</option>
						<option value="Moldova">Moldova</option>
						<option value="Monaco">Monaco</option>
						<option value="Mongolia">Mongolia</option>
						<option value="Montserrat">Montserrat</option>
						<option value="Morocco">Morocco</option>
						<option value="Mozambique">Mozambique</option>
						<option value="Myanmar">Myanmar</option>
						<option value="Nambia">Nambia</option>
						<option value="Nauru">Nauru</option>
						<option value="Nepal">Nepal</option>
						<option value="Netherland Antilles">Netherland Antilles</option>
						<option value="Netherlands">Netherlands (Holland, Europe)</option>
						<option value="Nevis">Nevis</option>
						<option value="New Caledonia">New Caledonia</option>
						<option value="New Zealand">New Zealand</option>
						<option value="Nicaragua">Nicaragua</option>
						<option value="Niger">Niger</option>
						<option value="Nigeria">Nigeria</option>
						<option value="Niue">Niue</option>
						<option value="Norfolk Island">Norfolk Island</option>
						<option value="Norway">Norway</option>
						<option value="Oman">Oman</option>
						<option value="Pakistan">Pakistan</option>
						<option value="Palau Island">Palau Island</option>
						<option value="Palestine">Palestine</option>
						<option value="Panama">Panama</option>
						<option value="Papua New Guinea">Papua New Guinea</option>
						<option value="Paraguay">Paraguay</option>
						<option value="Peru">Peru</option>
						<option value="Phillipines">Philippines</option>
						<option value="Pitcairn Island">Pitcairn Island</option>
						<option value="Poland">Poland</option>
						<option value="Portugal">Portugal</option>
						<option value="Puerto Rico">Puerto Rico</option>
						<option value="Qatar">Qatar</option>
						<option value="Republic of Montenegro">Republic of Montenegro</option>
						<option value="Republic of Serbia">Republic of Serbia</option>
						<option value="Reunion">Reunion</option>
						<option value="Romania">Romania</option>
						<option value="Russia">Russia</option>
						<option value="Rwanda">Rwanda</option>
						<option value="St Barthelemy">St Barthelemy</option>
						<option value="St Eustatius">St Eustatius</option>
						<option value="St Helena">St Helena</option>
						<option value="St Kitts-Nevis">St Kitts-Nevis</option>
						<option value="St Lucia">St Lucia</option>
						<option value="St Maarten">St Maarten</option>
						<option value="St Pierre &amp; Miquelon">St Pierre &amp; Miquelon</option>
						<option value="St Vincent &amp; Grenadines">St Vincent &amp; Grenadines</option>
						<option value="Saipan">Saipan</option>
						<option value="Samoa">Samoa</option>
						<option value="Samoa American">Samoa American</option>
						<option value="San Marino">San Marino</option>
						<option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option>
						<option value="Saudi Arabia">Saudi Arabia</option>
						<option value="Senegal">Senegal</option>
						<option value="Serbia">Serbia</option>
						<option value="Seychelles">Seychelles</option>
						<option value="Sierra Leone">Sierra Leone</option>
						<option value="Singapore">Singapore</option>
						<option value="Slovakia">Slovakia</option>
						<option value="Slovenia">Slovenia</option>
						<option value="Solomon Islands">Solomon Islands</option>
						<option value="Somalia">Somalia</option>
						<option value="South Africa">South Africa</option>
						<option value="Spain">Spain</option>
						<option value="Sri Lanka">Sri Lanka</option>
						<option value="Sudan">Sudan</option>
						<option value="Suriname">Suriname</option>
						<option value="Swaziland">Swaziland</option>
						<option value="Sweden">Sweden</option>
						<option value="Switzerland">Switzerland</option>
						<option value="Syria">Syria</option>
						<option value="Tahiti">Tahiti</option>
						<option value="Taiwan">Taiwan</option>
						<option value="Tajikistan">Tajikistan</option>
						<option value="Tanzania">Tanzania</option>
						<option value="Thailand">Thailand</option>
						<option value="Togo">Togo</option>
						<option value="Tokelau">Tokelau</option>
						<option value="Tonga">Tonga</option>
						<option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option>
						<option value="Tunisia">Tunisia</option>
						<option value="Turkey">Turkey</option>
						<option value="Turkmenistan">Turkmenistan</option>
						<option value="Turks &amp; Caicos Is">Turks &amp; Caicos Is</option>
						<option value="Tuvalu">Tuvalu</option>
						<option value="Uganda">Uganda</option>
						<option value="Ukraine">Ukraine</option>
						<option value="United Arab Erimates">United Arab Emirates</option>
						<option value="United Kingdom">United Kingdom</option>
						<option value="Uraguay">Uruguay</option>
						<option value="Uzbekistan">Uzbekistan</option>
						<option value="Vanuatu">Vanuatu</option>
						<option value="Vatican City State">Vatican City State</option>
						<option value="Venezuela">Venezuela</option>
						<option value="Vietnam">Vietnam</option>
						<option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option>
						<option value="Virgin Islands (USA)">Virgin Islands (USA)</option>
						<option value="Wake Island">Wake Island</option>
						<option value="Wallis &amp; Futana Is">Wallis &amp; Futana Is</option>
						<option value="Yemen">Yemen</option>
						<option value="Zaire">Zaire</option>
						<option value="Zambia">Zambia</option>
						<option value="Zimbabwe">Zimbabwe</option>
					</select>					
					<br /><br/>
					<strong>Institution*:</strong><br /><input type="text" id="institution" name="institution" style="width:400px" required /><br /><br />					
					<strong>Office Name:</strong><br /><input type="text" id="officename" name="officename" style="width:400px" /><br /><br />
					<strong>Website:</strong><br /><input type="text" id="website" name="website" style="width:400px" /><br /><br />
					<strong>Social Media:</strong><br /><input type="text" id="socialmedia" name="socialmedia" style="width:400px" /><br /><br />
					<strong>Brief Description of Work (50 words or less):</strong><br /><textarea id="skillset" name="skillset" style="width:400px"></textarea>
					<br />
					Total word count: <span id="display_count">0</span> words. Words left: <span id="word_left">50</span>					
					<br /><br />
					<strong>Tags:</strong><br />
					<?php 
							$args8 = array(
								'taxonomy' => 'binm_tags',
								'orderby' => 'name',
								'field' => 'name',
								'order' => 'ASC',
								'hide_empty' => false
							);

							$categories = get_categories( $args8 );

							foreach ( $categories as $category ){
								echo '<label><input type="checkbox" id="type-'. $category->name . '" rel="'. $category->name . '" value="' . $category->name . '" name="binmtags[]"> '. $category->name . '</label><br />';
							}
							
							
					?>
					<div id="newusertags"></div>
					<input type="text" id="addtag" name="addtag" placeholder="Add your own tag here" /><button id="useraddtag">Add</button>
					<br /><br />
					
					<?php if( function_exists( 'cptch_display_captcha_custom' ) ) { echo "<strong>Verification (spam prevention):</strong><br /><input type='hidden' name='cntctfrm_contact_action' value='true' />"; echo cptch_display_captcha_custom(); } ?>
					<br /><br/><input type="submit" id="submit_natlmember_form" name="submit_natlmember_form" value="Submit" /><br /><br/>	
					<span style="font-style:italic">* required fields</span>
				</form>	
			<?php
				} else {
					echo "<h2>You must be logged in to use this form.</h2>";
				}
			?>				
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
					if (!empty($_POST['country'])) {
						add_post_meta($newmemberid, 'country', $_POST['country']);
					}	
					if (!empty($_POST['socialmedia'])) {
						add_post_meta($newmemberid, 'socialmedia', $_POST['socialmedia']);
					}
					if (!empty($_POST['website'])) {
						add_post_meta($newmemberid, 'website', $_POST['website']);
					}					
					if (!empty($_POST['binmtags'])) {
						foreach($_POST['binmtags'] as $check) {
							wp_set_object_terms( $newmemberid, $check, 'binm_tags', true);							
						}						
					}				
				
					//Set latitude and longitude
					if ($_POST['country'] == 'United States of America') {	
						$strAddress = $_POST['street_address'] . " " . $_POST['city'] . " " . $_POST['state'] . " " . $_POST['zip_code'];
					} else {
						$strAddress = $_POST['street_address'] . " " . $_POST['city'] . " " . $_POST['country'];
					}
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
						//I commented out the send mail function because the approval process is not used.						
						//wp_mail( $to, 'A New Broader Impacts member Needs Approval', $mailcontent );
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
						if (!empty($_POST['country'])) {
							update_post_meta($update_post_id, 'country', $_POST['country']);
						} else {
							delete_post_meta($update_post_id, 'country');
						}	
						if (!empty($_POST['website'])) {
							update_post_meta($update_post_id, 'website', $_POST['website']);
						} else {
							delete_post_meta($update_post_id, 'website');
						}
						if (!empty($_POST['socialmedia'])) {
							update_post_meta($update_post_id, 'socialmedia', $_POST['socialmedia']);
						} else {
							delete_post_meta($update_post_id, 'socialmedia');
						}						
						if (!empty($_POST['binmtags'])) {
							wp_delete_object_term_relationships( $update_post_id, 'binm_tags' );
							foreach($_POST['binmtags'] as $check2) {
								wp_set_object_terms( $update_post_id, $check2, 'binm_tags', true);							
							}											
						} else {
							wp_delete_object_term_relationships( $update_post_id, 'binm_tags' );
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
	$current_userid = $current_user->ID;
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
				'key' => 'country',
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
	
	$arrids = array_map('intval', $_GET['tags']);	
	
	$q4 = get_posts(array(
		'post_type' => 'bi_natl_member',
		'posts_per_page' => -1,
		'post_status' => 'publish',	
		'tax_query' => array(
			array(
				'taxonomy' => 'binm_tags',
				'field' => 'term_id',
				'terms' => $arrids
			)
		)
	));

	$bigmerge = array_merge( $q1, $q2, $q3, $q4 );
	$first_merge = array_merge( $q1, $q2 );
	$merged = array_merge( $first_merge, $q3 );
	
	//$merged2 = array_merge( $merged, $q4 );
	
	$post_ids = array();
	$combined = array();
	$A = array();
	$B = array();
	if (!empty($keywordsearch) && !empty($q4)) {
		$A = wp_list_pluck($merged, 'ID');
		$Astr = array_map('strval', $A);
		$B = wp_list_pluck($q4, 'ID');
		$Bstr = array_map('strval', $B);
		$combined = array_intersect($Astr, $Bstr);
		foreach( $combined as $item ) {
			$post_ids[] = (int)$item;
		}		
	} else if (!empty($keywordsearch)) {
		foreach( $bigmerge as $item ) {
			$post_ids[] = $item->ID;
		}		
	} else if (!empty($q4))  {
		foreach( $q4 as $item ) {
			$post_ids[] = $item->ID;
		}				
	} else {
		foreach( $merged as $item ) {
			$post_ids[] = $item->ID;
		}		
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
		// if ($allowed) {
			// $admin_check = "YES";
		// } else {
			// $admin_check = "NO";
		// }
		
			
		
		$member_query = new WP_Query( $args );
		
		while ( $member_query->have_posts() ) {
			$member_query->the_post();
			$postid = get_the_ID();
			$authorid = get_the_author_meta( 'ID' );
			if ($authorid == $current_userid) {
				$admin_check = "YES";
			} else {
				$admin_check = "NO";
			}
			
			$term_list = wp_get_post_terms( $postid, 'binm_tags' );
			$termstr = "";
			foreach($term_list as $term_single) {
				$termstr = $termstr . $term_single->name . ", "; //do something here
			}
			if (strlen($termstr) > 0) {
				$termstr = substr($termstr, 0, -2);
			}
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
				"country" => get_post_meta( $postid, 'country' ),
				"website" => get_post_meta( $postid, 'website' ),
				"socialmedia" => get_post_meta( $postid, 'socialmedia' ),				
				"lat" => get_post_meta( $postid, 'bi_natl_member_lat' ),
				"lng" => get_post_meta( $postid, 'bi_natl_member_long' ),
				"admin" => $admin_check,
				"tags" => $termstr

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
		"country" => get_post_meta( $postid, 'country' ),
		"website" => get_post_meta( $postid, 'website' ),
		"socialmedia" => get_post_meta( $postid, 'socialmedia' ),		
		"tags" => wp_get_object_terms( $postid, 'binm_tags' )


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
		"socialmedia" => get_post_meta( $postid, 'socialmedia' ),		
		"website" => get_post_meta( $postid, 'website' ),		
		"country" => get_post_meta( $postid, 'country' )
	
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
		"socialmedia" => get_post_meta( $postid, 'socialmedia' ),		
		"website" => get_post_meta( $postid, 'website' ),		
		"country" => get_post_meta( $postid, 'country' )
	
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

add_action( 'wp_ajax_bi_natl_member_addtag', 'bi_natl_member_addtag_callback' );
add_action( 'wp_ajax_nopriv_bi_natl_member_addtag', 'bi_natl_member_addtag_callback' );

function bi_natl_member_addtag_callback() {
	do_action("init");
	header('Content-type: application/json');
	
	$newterm = $_POST['term'];
	wp_insert_term( $newterm, 'binm_tags' );	
	
	wp_die();
}