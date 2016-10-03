$ = jQuery;
var lat_lng = [];
var markers = [];
var map;
	$( document ).ready(function() {
		// $("#map img").css({'max-width':'none'});
		// $("#myTable").tablesorter(); 

	
	});
	
	$( "#addnewmember" ).click(function() {
		$('#member_form').trigger("reset");
		$("#member_form_title").html("Add Member");
		$("#searchdiv").hide();
		$("#formdiv").show();
		$("#mapdiv").hide();
		$("#resultsdiv").hide();
		$("#approvediv").hide();

	});	
	$( "#newsearch" ).click(function() {
		$("#resultsdiv").hide();
		$("#formdiv").hide();
		$("#mapdiv").hide();
		$("#searchdiv").show();
		$("#approvediv").hide();

	});		
	$( "#approvecollaborator" ).click(function() {
		$("#searchdiv").hide();
		$("#formdiv").hide();
		$("#mapdiv").hide();
		$("#resultsdiv").hide();
		$("#approvediv").show();

	});	
	$( "#maptolist" ).click(function() {
		$("#mapdiv").hide();
		$("#resultsdiv").show();
	});		
	
	$("#searchform").submit(function(e){		
		e.preventDefault();
		$("#resultsdiv").show();
		$("#searchdiv").hide();
		var data = {
			action : "bi_natl_member_search",
			keyword : $("#keywordsearch").val()			
		}
		//console.log(data);
		$.ajax({
			url: MyAjax.ajaxurl,
			data: data,
			success: function(response) {
				console.log(response);
				lat_lng = [];
				$("#resultlist").empty();
				var count = 0;
				var html;
				var tbl = "<table class='pure-table'><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Street Address</th><th>City</th><th>State</th><th>ZIP</th><th>Office Name</th><th>Institution</th><th>Skillset</th><th>Audience</th></tr></thead><tbody>";
				var tblrows = "";
				for(var i = 0; i < response.length ; i++) {
					count = count + 1;
					if (response[i].admin == "YES") {
						html = "<div id='div_" + response[i].id + "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#f5f5f5;width:70%;max-width:70%;'><div style='float:right;'><button onclick='editCollaborator(" + response[i].id + ")'>Edit</button> <button onclick='disapproveCollaborator(" + response[i].id + ")'>Disapprove</button> <button onclick='deleteCollaborator(" + response[i].id + ")'>Delete</button></div><strong style='font-size:14pt;'>" + response[i].title + "</strong><br /><strong>Phone:</strong> " + response[i].phone + "<br /><strong>Email:</strong> <a href='mailto:" + response[i].email + "'>" + response[i].email + "</a><br /><strong>Street Address:</strong> " + response[i].streetaddress + "<br /><strong>City:</strong> " + response[i].city + "<br /><strong>State:</strong> " + response[i].state + "<br /><strong>ZIP Code:</strong> " + response[i].zipcode + "<br /><strong>Office Name:</strong> " + response[i].officename + "<br /><strong>Academic Institution:</strong> " + response[i].institution + "<br /><strong>Your Skillset:</strong> " + response[i].skillset + "<br /><strong>Audience:</strong> " + response[i].audience + "<br /></div><br />";					
					} else {
						html = "<div id='div_" + response[i].id + "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#f5f5f5;width:70%;max-width:70%;'><strong style='font-size:14pt;'>" + response[i].title + "</strong><br /><strong>Phone:</strong> " + response[i].phone + "<br /><strong>Email:</strong> <a href='mailto:" + response[i].email + "'>" + response[i].email + "</a><br /><strong>Street Address:</strong> " + response[i].streetaddress + "<br /><strong>City:</strong> " + response[i].city + "<br /><strong>State:</strong> " + response[i].state + "<br /><strong>ZIP Code:</strong> " + response[i].zipcode + "<br /><strong>Office Name:</strong> " + response[i].officename + "<br /><strong>Academic Institution:</strong> " + response[i].institution + "<br /><strong>Your Skillset:</strong> " + response[i].skillset + "<br /><strong>Audience:</strong> " + response[i].audience + "<br /></div><br />";					
						tblrows = tblrows + "<tr><td>" + response[i].title + "</td><td>" + response[i].phone + "</td><td>" + response[i].email + "</td><td>" + response[i].streetaddress + "</td><td>" + response[i].city + "</td><td>" + response[i].state + "</td><td>" + response[i].zipcode + "</td><td>" + response[i].officename + "</td><td>" + response[i].institution + "</td><td>" + response[i].skillset + "</td><td>" + response[i].audience + "</td></tr>";
					}					
					$("#resultlist").append(html);
					if (response[i].lat != "") {
						lat_lng.push({
							name: response[i].title,
							desc: response[i].skillset,
							lat: parseFloat(response[i].lat),
							lng: parseFloat(response[i].lng),
							email: response[i].email,
							phone: response[i].phone
						});
						
					}
				}
				
				var newtable = tbl + tblrows + "</tbody></table>";
				//$("#rtable").append(newtable);

				
				$("#resultsfound").html( count + " results found");
				markers=[];
			},
			error: function(xhr, status, error) {
				console.log(error);
			}			
		});		
	});	
	
	function editCollaborator(collaboratorid) {
		$("#collaborator_id").val(collaboratorid);
		$("#resultsdiv").hide();	
		$("#formdiv").show();
		$("#collaborator_form_title").html("Edit a Collaborator");
		
		var data = {
			action: 'bi_collaborator_edit',
			id: collaboratorid
		};		
		
		$.post(MyAjax.ajaxurl, data, function(response) {
			//console.log(response);
			var tagstr = "";
			for(var i = 0; i < response.length ; i++) {					
				$("#collaboratorname").val(response[i].title);
				$("#phone").val(response[i].phone[0]);
				$("#email").val(response[i].email[0]);
				$("#street_address").val(response[i].streetaddress[0]);
				$("#city").val(response[i].city[0]);
				$("#state").val(response[i].state[0]);
				$("#zip_code").val(response[i].zipcode[0]);
				$("#researchinterest").val(response[i].researchinterest);
				$("#academicarea").val(response[i].academicarea[0]);
				$("#audience").val(response[i].audience[0]);
				var tags = response[i].tags;
				for (var j = 0; j < tags.length; j++) {
					tagstr = tagstr + tags[j].name + ", ";
				}
				
				
			}
			tagstr = tagstr.slice(0,-2);
			$("#tags").val(tagstr);
		});			
	}	
	
	$( "#showmap" ).click(function(e) {
		e.preventDefault();
		$("#resultsdiv").hide();
		$("#mapdiv").show();		
		
		initialize();
		
		google.maps.event.trigger(map, 'resize');

		$("#map img").css({'max-width':'none !important'});

	});	

	function initialize() {		
			
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }
            markers.length = 0;		
		
			var mapCanvas = document.getElementById('map');
			var mapOptions = {
			  center: { lat: 38.5, lng: -92 },
			  zoom: 4,
			  mapTypeId: google.maps.MapTypeId.ROADMAP			 
			}
			map = new google.maps.Map(mapCanvas, mapOptions);
			
			markers = lat_lng;
			
			var markerset = [];
			
			var infowindow = new google.maps.InfoWindow();
			console.log(markers);
			// Loop through our array of markers & place each one on the map  
			for( i = 0; i < markers.length; i++ ) {
				
				var position = new google.maps.LatLng(markers[i].lat, markers[i].lng);	
				
				marker = new google.maps.Marker({
					position: position,
					map: map,
					title: markers[i].name
				});	
				
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
				  infowindow.setContent("<span style='font-size:14pt;font-weight:bold;'>" + markers[i].name + "</span><br /><strong>Skillset: </strong>" + markers[i].desc + "<br /><strong>Email: </strong><a href='mailto:" + markers[i].email + "'>" + markers[i].email + "</a><br /><strong>Phone: </strong>" + markers[i].phone);
				  infowindow.open(map, marker);
				}
				})(marker, i));
				markerset.push(marker);
			}
			
			var siteurl = object1.siteUrl;									
			var mc_options = {				
				imagePath: siteurl + '/wp-content/plugins/broader-impacts-natl/images/m'
			};			
			
			var markerCluster = new MarkerClusterer(map, markerset, mc_options);			
			
	}	
	
	$("#phone").mask("(999) 999-9999");
	$("#zip_code").mask("99999");
	
	$("#collaborator_form").submit(function(e){
		if ($("#collaboratorname").val() == "") {
			e.preventDefault();
			alert("Please enter a Collaborator Name.");
		}
	});	
	
	function approveCollaborator(collaboratorid) {
		var data = {
			action: 'bi_collaborator_approve',
			id: collaboratorid
		};		
		
		$.post(MyAjax.ajaxurl, data, function(response) {	
			for(var i = 0; i < response.length ; i++) {				
				alert("The '" + response[i].title + "' collaborator has been approved!");
				$("#" + response[i].id).hide();				
			}
			window.location.href = window.location.href;
		});	

	}	
	
	function disapproveCollaborator(collaboratorid) {
				
			var data = {
				action: 'bi_collaborator_disapprove',
				id: collaboratorid
			};		
			
			$.post(MyAjax.ajaxurl, data, function(response) {	
				for(var i = 0; i < response.length ; i++) {				
					alert("The '" + response[i].title + "' collaborator has been disapproved!");
					$("#div_" + response[i].id).hide();
					$("#approvediv").append("<div id='" + response[i].id + "' style='padding:15px;border:solid 2px #a9a9a9;background-color:#FAF0E6;width:70%;max-width:70%;'><div style='float:right;'><button style='margin-left:5px;' onclick='approveCollaborator(" + response[i].id + ")'>Approve</button></div><div style='float:right;'><button style='margin-left:5px;' onclick='deleteCollaborator(" + response[i].id + ")'>Delete</button></div><strong style='font-size:14pt;'>" + response[i].title + "</strong><br /><strong>Phone:</strong> " + response[i].phone + "<br /><strong>Email:</strong> <a href='mailto:" + response[i].email + "'>" + response[i].email + "</a><br /><strong>Street Address:</strong> " + response[i].streetaddress + "<br /><strong>City:</strong> " + response[i].city + "<br /><strong>State:</strong> " + response[i].state + "<br /><strong>ZIP Code:</strong> " + response[i].zipcode + "<br /><strong>Academic Area:</strong> " + response[i].academicarea + "<br /><strong>Research Interest:</strong> " + response[i].researchinterest + "<br /><strong>Audience:</strong> " + response[i].audience + "<br /></div><br />");					
						
				}
				window.location.href = window.location.href;
			});	
		
	}	
	
	function deleteCollaborator(collaboratorid) {
		var r = confirm("Are you sure you want to delete this collaborator?");
		if (r == true) {
				
			var data = {
				action: 'bi_collaborator_delete',
				id: collaboratorid
			};		
			
			$.post(MyAjax.ajaxurl, data, function(response) {	
				for(var i = 0; i < response.length ; i++) {				
					alert("The '" + response[i].title + "' collaborator has been deleted!");
					$("#div_" + response[i].id).hide();	
					$("#" + response[i].id).hide();						
				}
			});	
		} else {
			return false;
		}
	}	
	
