var updates;
var request = false;
var loaded = false;
var logLastDate = "";
var logCounter = 0;

var page = {
	list: [],
	curr: function() {
		return $(":mobile-pagecontainer").pagecontainer("getActivePage").prop("id");
	},
    prev: function (currPage) {
		if ( arguments.length == 0 ) currPage = this.curr();
		return this.list[($.inArray(currPage, this.list) - 1 + this.list.length) % this.list.length];
	},
	next: function (currPage) {
		if ( arguments.length == 0 ) currPage = this.curr();
		return this.list[($.inArray(currPage, this.list) + 1) % this.list.length];
	}
}

function homebrain(name, verb, params) {	

	console.log(request);
	if ( !request ) {
		requesting();
		if ( typeof Android !== 'undefined' ) {
			if ( typeof params === 'undefined' ) params = null;
			Android.homebrain(name, verb, "{\"param1\":" + params + "}");
		}
		console.log("homebrain "+name+" "+verb+" "+params);
	}
}

function initListeners() {

	$('input:radio').change(function(e) {

		switch (this.name) {

			case "amp":
				homebrain(this.name, this.value);
			break;
						
			case "heat-set":
				homebrain('heat', 'set', $('#term-slider').val());
			
			case "lawn-time":
			case "garden-time":
			break;

			default: homebrain(this.name, this.value);
		}
	});

	$('input:checkbox').change(function(e) {

		switch (this.name) {
			
			case "mpd-on":
				homebrain('mpd', 'on');
			break;

			case "mpd-off":
				homebrain('mpd', 'off');
			break;
			
			case "kodi-on":
				homebrain('kodi', 'on');
			break;
			
			case "kodi-off":
				homebrain('kodi', 'off');
			break;

			case "heat-auto":
				homebrain('heat', 'auto');
			break;

			case "heat-afreeze":
				homebrain('heat', 'antifreeze');
			break;

			case "lawn":
			case "garden":				
				if ( typeof this.checked !== 'undefined' ) {
					if ( this.checked )
						homebrain(this.name, "on", $('input:checked[name='+this.name+'-time]').val());
					else
						homebrain(this.name, "off");
				}
			break;

			default:				
				homebrain(this.name, this.checked ? "on" : "off");
		}

		$('input[name="'+this.name+'"]').prop('checked', !this.checked).checkboxradio( 'refresh' );
	});

}


function toast(msg) {
	
	if ( typeof Android !== 'undefined' ) {
		Android.toast(msg);
	}
	
	console.log("Toasting: " + msg);
}

function speak(msg) {

	if ( typeof Android !== 'undefined' ) {
		Android.speak(msg);
	}
	
	console.log("Speaking: " + msg);	
}

function requesting() {
	request=true;
	loading(1);
	setTimeout(requestingDone, 3072);
}

function requestingDone() {	
	setTimeout( function() { 
		loading(false);
		request= false;
	}, 128);
	setTimeout( function() {
		$('input:checked[name="hbr"]').prop('checked', false).checkboxradio( 'refresh' );
		$('input:checked[name="hsrv"]').prop('checked', false).checkboxradio( 'refresh' );
		$('input:checked[name="amp"]').prop('checked', false).checkboxradio( 'refresh' );
		$('input:checked[name="mpd"]').prop('checked', false).checkboxradio( 'refresh' );
		$('input:checked[name="tv"]').prop('checked', false).checkboxradio( 'refresh' );
		$('input[name="heat-set"]').prop('checked', false).checkboxradio( 'refresh' );
	 }, 256);
}

function loading(msg) {
	if ( typeof msg !== 'undefined' && msg !== false ) {
		$("#overlay").fadeIn(96);
	} else {
		$("#overlay").delay(128).fadeOut(128);
	}	
}

function notice(connType) {
	$("#connectionType").html("&nbsp;" + connType);
}

function changePage(page, reverse) {
	$(":mobile-pagecontainer").pagecontainer("change", "#" + page, {
		transition: "slide",
		reverse: reverse,
		changeHash: false
		});
}

function slideRight(toPage) {	
	if ( arguments.length == 0 ) toPage = page.prev();
	changePage(toPage, true);
}

function slideLeft(toPage) {
	if ( arguments.length == 0 ) toPage = page.next();
	changePage(toPage, false);
}

function prependHeader(item) {

	var pageTitle = item.data("title");
	var pageId = item.attr('id');
	var prevPage = $("#" + page.prev(pageId));
	var nextPage = $("#" + page.next(pageId));

	var header = '' + "\n" +
	'<!-- header -->' + "\n" +
	'<div data-role="header" data-position="fixed" data-tap-toggle="false">' + "\n";
	if ( prevPage.attr("id") != pageId ) {
		header += '		<a href="#" class="ui-btn ui-btn-active ui-icon-arrow-l ui-btn-icon-left" onclick="slideRight(\'' + 
							prevPage.attr("id") + '\')">' + prevPage.data("title") + '</a>' + "\n";
	}
	header += '		<h1>' + pageTitle + '</h1>' + "\n";
	if ( (page.list.length == 2 && ($.inArray(pageId, page.list) == 1)) || nextPage.attr("id") != pageId ) {
		header += '		<a href="#" class="ui-btn ui-btn-active ui-icon-arrow-r ui-btn-icon-right" onclick="slideLeft(\'' + 
							nextPage.attr("id") + '\')">' + nextPage.data("title") + '</a>' + "\n";
	}
	header += '</div><!-- /header -->' + "\n";
	item.prepend(header);
}

function go(toPage, allowedPages) {

	if ( !loaded ) {
		loaded = true;
		page.list = (allowedPages == null) ? ["multimedia"] : allowedPages;

		$.each(page.list, function() {
			prependHeader($("#" + this));
		});		
		initListeners();

		$( document ).on( "swiperight", ".ui-page", function( event ) {
			slideRight();
		});

		$( document ).on( "swipeleft", ".ui-page", function( event ) {
			slideLeft();
		});

		if ( typeof Android !== 'undefined' ) Android.checkConn();

		/*
		$(document).on("pagecontainerchange", function() {
			//console.log($(".ui-page-active").jqmData("title"));		
			//$("[data-role='header'] h1" ).text($(".ui-page-active").jqmData("title"));
		});
		*/
	}

	toPage = ( typeof toPage === 'undefined' || toPage == null ) ? page.list[0] : toPage;

	if ( $.inArray(toPage, page.list) < 0 ) return;

	$(":mobile-pagecontainer").pagecontainer("change", "#" + toPage, {
		transition: "slideup",
		reverse: false,
		changeHash: false
	});
}

function formatTime(date) {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	minutes = minutes < 10 ? '0' + minutes : minutes;
	return hours + ':' + minutes;
}

function formatDate(date) {
	day = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
	month = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	return day[date.getDay()] + ", " + month[date.getMonth()] + " " + getOrdinal(date.getDate()) + " " + date.getFullYear();
}

function getOrdinal(n) {
    if((parseFloat(n) == parseInt(n)) && !isNaN(n)){
        var s=["th","st","nd","rd"],
        v=n%100;
        return n+(s[(v-20)%10]||s[v]||s[0]);
    }
    return n;     
}

function updateLogRow(values, refresh) {
	if ( typeof refresh == 'undefined' ) refresh = false;
	if ( typeof values !== 'object' ) values = JSON.parse(values);

	logCounter++;
	if ( formatDate(new Date(values.timestamp)) != logLastDate) {
		$('<li data-role="list-divider">'+logLastDate+'<span class="ui-li-count">'+logCounter+'</span></li>').prependTo('#log');
		if ( refresh ) $('#log').listview('refresh');
		logCounter = 1;
	}

	switch (true) {
		case values.state.indexOf("user") > -1:
			msg = ["user is logged off..", "user is logged on!"];
		break;
		
		case values.state.indexOf("busy") > -1:
			msg = ["is not busy..", "is busy!"];
		break;
		
		case values.state.indexOf("MPD") > -1:
			msg = ["stopped playing..", "is playing!"];
		break;

		default:
			msg = ["is off..", "is on!"];
	}

	logLastDate	= formatDate(new Date(values.timestamp));
	title 		= '<h3 class="ui-li-heading">' + values.state.split(" ")[0] + '</h3>';
	timestamp 	= '<p class="ui-li-aside"><strong>' + formatTime(new Date(values.timestamp))+ " " + '</strong></p>';
	subtitle 	= '<p style="white-space: normal;"><strong>' + values.statebefore + ' </strong>';
	text 		= msg[values.changedto] + '</p>';
	
	$('#log li')[0].remove();
	$('<li style="padding: 0 10px;">' + title + subtitle + text + timestamp +'</li>').prependTo('#log');
	$('<li data-role="list-divider">'+logLastDate+'<span class="ui-li-count">'+logCounter+'</span></li>').prependTo('#log');
	if ( refresh ) $('#log').listview('refresh');
}

function updateLog(refresh) {
	if ( typeof refresh == 'undefined' ) refresh = false;
	if ( typeof Android !== undefined ) {
		list = JSON.parse(Android.get());
		if ( refresh ) $('#log').listview('refresh');
		for (var key in list) {
			updateLogRow(list[key], refresh);
		}
	}
}

function updateHeatData(data) {
	data = JSON.parse(data);
	if ( typeof data.tempSet != 'undefined' ) {
		$('#temp-set').html(data.tempSet+'&deg;C&nbsp;');
		$('#term-slider').val(data.tempSet).slider('refresh');;
	}
	if ( typeof data.humidIn != 'undefined' ) {
		$('#humid-in').html(data.humidIn+'&nbsp;%&nbsp;');
	}
	if ( typeof data.tempIn != 'undefined' ) {
		$('#temp-in').html(data.tempIn+'&deg;C&nbsp;');
	}
	if ( typeof data.humidOut != 'undefined' ) {
		$('#humid-out').html(data.humidOut+'&nbsp;%&nbsp;');
	}
	if ( typeof data.tempOut != 'undefined' ) {
		$('#temp-out').html(data.tempOut+'&deg;C&nbsp;');
	}
}


$(document).ready(function(){

	$( "[data-role='header']" ).toolbar({
		theme: "a",
		position: "fixed",
		tapToggle: false
	});	
	$( "[data-role='footer']" ).toolbar({
		theme: "a",
		position: "fixed",
		tapToggle: false
	});

	updateLog(false);
});
