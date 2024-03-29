@extends('layouts.header')
@section('headerScript')
{{ HTML::script('fullcalendar/lib/jquery.min.js') }}
{{ HTML::script('fullcalendar/lib/moment.min.js') }}
{{ HTML::script('lightbox.js') }}
{{ HTML::script('polyfiller.js') }}

{{ HTML::style('css/calendar.css') }}
{{ HTML::style('css/bookkit.css') }}
{{ HTML::style('css/lightbox.css') }}
{{ HTML::style('css/tableList.css') }}
{{ HTML::style('css/loadingScreen.css') }}
@stop
@section('bookkitli') class="active" @stop
@section('content')

<script> 
//These must be set before document ready	
//enable date picker for firefox
webshims.formcfg = {
		en: {
			dFormat: '-',
			dateSigns: '-',
			patterns: {
				d: "yy-mm-dd"
			}
		}
};

webshim.setOptions('forms', {
	addValidators: true
});

webshim.setOptions("forms-ext", {
	replaceUI: 'auto',
	types: 'date',
	"date": {
		"startView": 2,
		"size":2,
		"popover": {
			"position": {
				"at": "right bottom"
			}
		}
	}
});
webshims.polyfill('forms forms-ext');
webshims.activeLang('en');
	
	
function initTable() {
	//add in our table headers into the table layout
	//add headers
	$('#kitListing .table-static-header-row')
		.append($('<td></td>').text('Bar Code'))
		.append($('<td></td>').text('Description'))
		.append($('<td></td>').text('Status'));
}
		
	
	
$(document).ready(function() {
	//hide footer
	$(".navMenu.footer").css('bottom', "-10%");
	
	//kill chromes date picker
   $('input[type=date]').on('click', function(event) {
        event.preventDefault();
    });
	
	var holidays = {{ json_encode(Holiday::lists('date')); }};
	
	$('input.min-today').prop('min', function(){
		var curDate = moment();

		if (parseInt(curDate.format('d')) == 5) {
			curDate.add(4, 'days');
		} else {
			curDate.add(1, 'days');
		}
		//if this falls on a saturday or sunday, black those out too
		while(parseInt(curDate.format('d')) == 6 || parseInt(curDate.format('d')) == 0 || 
			  parseInt(curDate.format('d')) == 1) {
			curDate.add(1, 'days');
		}
		console.log(curDate.format('DD-MM-YYYY'));
        //return curDate.toJSON().split('T')[0];
		return curDate.format('YYYY-MM-DD');
    }).on('validatevalue', function (e, data) {
		var date = data.valueAsDate.toISOString().split('T')[0];

		var isHoliday = holidays.filter(function(d) {
			return d == date;
		});
		var day = data.valueAsDate.getUTCDay();
	
		return (isHoliday.length > 0)
		return false;
	});	
	initTable();

	

	
	var hasStart = 0, hasEnd = 0, hasKit = 0;
	
	var myLightBox = LightBox.init();
	var startSelector = "input[name='start']";
	var endSelector = "input[name='end']";
	
	$('#kitCodeLabel').attr('readonly', true);
	$('#kitCodeLabel').css('background-color' , '#DEDEDE');
	
	function updateSelectButton() {
		var startDate = $(startSelector).val();
		var endDate = $(endSelector).val();
		console.log(startDate);
		
		$('#selectKitBtn').attr('disabled', function() {
			return !startDate || !endDate;
		});	
		
		$("input[type='submit']").attr('disabled', function() {
			return !startDate || !endDate || !$('#kitCodeLabel').attr('data-selected');
		});		
	}
	
	function updateSelectedKit() {
		$(".navMenu.footer").animate({bottom:"-10%"}, 400, function(){});
		$('#kitListing .table-rows-table').empty();
		$('#kitCodeLabel').attr('data-selected', null);
	}
	
	$(startSelector).change(function() {
		updateSelectButton();
		updateSelectedKit();
		
	});
	
	$(endSelector).change(function() {
		updateSelectButton();
		updateSelectedKit();
	});
	
	
	$('#selectKitBtn').click(function() {
		var kitType = $('#type').val();
		var kitName = $('#type option:selected').html();
		
		var startDate = $(startSelector).val();
		var endDate = $(endSelector).val();
		checkAvailability(kitType,startDate,endDate);
	}); 
	
	
	//set initial state for our buttons
	updateSelectButton();
	updateSelectedKit();
	
	function populateTable(kitList){
		var table = $('#kitListing .table-rows-table');
		table.empty();
		if(kitList.length == 0) {
			var row = document.createElement('tr');
			$(row).append($('<td></td>').text("No Kits Available"));
			table.append($(row));
			return;
		}
		
		
		kitList.forEach(function(kit) {
			var row = document.createElement('tr');
			$(row).attr('id', kit.id);


			//add asset tag
			$(row).append($('<td></td>').text(kit.barcode));

			$(row).append($('<td></td>').text(kit.description));
			
			//var isDamaged = $.grep(allDamagedKits, function(e){ return e.id == kit.id; });
			if (kit.damaged > 0) {
				$(row).append($('<td></td>').text("Damaged"));
			} else {
				$(row).append($('<td></td>').text("Good"));
			}

			table.append($(row));
			table.on('click', '#'+kit.id, function() {
				$('.selected').each(function() { 
				   $(this).removeClass('selected');
				});
				$('#kitCodeLabel').val(kit.barcode).attr("data-selected","true");
				$(this).addClass('selected');
				updateSelectButton();
				$(".navMenu.footer").animate({bottom:"2%"}, 400, function(){});
			});
		});	
	}	

	function checkAvailability(kitType,startDate,endDate) {
		var url = "/checkForKit/" + kitType + "/" + startDate + "/" + endDate;
	 	console.log(url);
		$('.loadingImg').show();
	 	$.get(url).done(function(data) {
			//if(data.status == 1) {
			//	return;
			//}
		 	populateTable(data.available);
			$('.loadingImg').hide();
		});    
	}
	//override our form button with the one in the footer nav
	$('#createBooking').click(function(e) {
		e.preventDefault();
		console.log("creating booking!");
		$('#form-booking').submit();
	});
	
	
	
});
</script> 
	<div class="booking">
		<div id="headerLabel">Create an Event</div>
		<div class="bookingBox">
		   {{ Form::open([ 'route' => 'bookings.store',
							'id' => 'form-booking']
			)}}
			<div class="bookkit">

				<div class="bookitFormElement" id="eventNameBox">
					{{ Form::label('eventName', 'Event Name: ', ['id' => 'eventLabel']) }}
					{{ Form::text('eventName') }}
				</div>
				<div class="bookitFormElement" id="hardwareTypeBox">
					{{ Form::label('type', 'Type:') }}
					{{ Form::select('type', HardwareType::lists('name', 'id')) }}
				</div>
				<div class="bookitFormElement" id="destinationBox">
					{{ Form::label('destination', "Deliver To: ", ['id' => "destLabel"]) }}
						   {{ Form::select('destination', Branch::lists('name', 'id')) }}
				</div>
				<div class="bookitFormElement" id="startDateBox">
					{{ Form::label('start', 'Start Date: ', ['id' => 'startDateLabel']) }}
							{{ Form::input('date', 'start', '',
								['class'=>'disable-weekends min-today', 'placeholder'=>'yyyy-mm-dd', 'required'=> ""])
							}}
				</div>
				<div class="bookitFormElement" id="endDateBox">
					{{ Form::label('end', 'End Date: ', ['id' => 'endDateLabel']) }}
					{{ Form::input('date', 'end', '', 
						['class'=>'min-today', 'placeholder'=>'yyyy-mm-dd', 'required' => ""]) 
					}}
				</div>
				<div class="bookitFormElement" id="bookKitButtonsBox">
					{{ Form::button('Check Kit Availability', ['id'=>'selectKitBtn']); }}
					{{ Form::hidden('kitCode', 'No Kit Selected', ['id'=>'kitCodeLabel']); }}
				</div>
			</div>

		   {{ Form::close() }}
		</div>
	</div>
	<div class="listing">
		<div id="headerLabel">Please select which kit you would like to book</div>
		<div id="kitListing">
			@include('layouts.tableList')
		</div>
	</div>


<div class="navMenu footer">
	<ul>
	  <li>
		<a href="#" id="createBooking">Create Booking</a>
	  </li>
	</ul> 
</div>
@include('layouts.loadingScreen')

@stop