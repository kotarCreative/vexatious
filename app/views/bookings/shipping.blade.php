@extends('layouts.header')
@section('headerScript')
{{ HTML::style('css/tableFilter.css') }}
{{ HTML::style('css/tableList.css') }}
{{ HTML::style('css/shipping.css') }}
{{ HTML::script('fullcalendar/lib/jquery.min.js') }}
{{ HTML::script('fullcalendar/lib/moment.min.js') }}
@stop
@section('shippingli') class="active" @stop
@section('content')
<ul class="TableFilter-Bar">
	{{ Form::open(['method' => 'put', 'route' => 'bookings.update', 'id' => 'form-shipping']) }}
	<li>
	{{ Form::label('branch', 'Shipping from') }}
	{{ Form::select('branch', Branch::lists('name', 'id')); }}
	</li>
	<li>
	{{ Form::hidden('form', 'ship') }} 
	{{ Form::hidden('shipped', '1', ['id'=>'shippedcode']) }}
	</li>
	{{Form::close() }}
</ul>


<div id="todayTable">
	<div class="tableTitle">Today </div>
	<div id="todayDate"> [Today's Date] </div>
	<div class="tableWrapper">
		@include('layouts.tableList')
	</div>
</div>

<div id="tomorrowTable">
	<div class="tableTitle">Tomorrow </div>
	<div id="tomorrowDate"> [Tomorrow's Date] </div>
	<div class="tableWrapper">
		@include('layouts.tableList')
	</div>
</div>

<div class="navMenu footer">
<ul>
  <li>
	<a href="#" id="createBooking">Ship Kit</a>
  </li>
</ul> 
</div>


<script>
$(document).ready(function() {

	var submitRoute = $('#form-shipping').attr("action");
	$(".navMenu.footer").css('bottom','-10%');
			     

	//Set Table Dates
	var todayTitle = document.querySelector("#todayDate");
	var tomorrowTitle = document.querySelector("#tomorrowDate");

	var today = new Date(moment());
	var tomorrow = new Date(moment(today).add(1,'days'));

	
	todayTitle.innerHTML = moment().format("dddd, MMMM Do YYYY");
	tomorrowTitle.innerHTML = moment(today).add(1,'days').format("dddd, MMMM Do YYYY");
	
	//Set the headers for both tables
  	$('.table-static-header-row')
		.append($('<td></td>').text('BarCode'))
		.append($('<td></td>').text('Type'))
		.append($('<td></td>').text('Destination'));

	//Add Rows to table  
	var typeList = {{ json_encode(HardwareType::lists('name', 'id')) }};
	var branchList = {{ json_encode(Branch::lists('name', 'id')) }};
	var allBookings = {{ json_encode($bookings['bookings']); }};
	var todaysBookings;
	var tomorrowsBookings;
	updateTables();
	
	function getShipping() {
		 var currentBranch = $('#branch').val();
		var day = 24*60*60*1000;
		 todaysBookings = allBookings.filter(function(a) {

			var today = moment();
			var shipStart = moment(a.shipping * 1000);
			return (a.currentBranchID == currentBranch &&
					shipStart.format("YYYY") == today.format("YYYY") &&
					shipStart.format("MM") == today.format("MM") &&
					shipStart.format("DD") == today.format("DD") && parseInt(a.shipped) == 0); 
		 });

		tomorrowsBookings = allBookings.filter(function(a) {
			var tomorrow = moment(new Date().getTime() + 24 * 60 * 60 * 1000);
			var shipStart = moment(a.shipping * 1000);
			return (a.currentBranchID == currentBranch &&
					shipStart.format("YYYY") == tomorrow.format("YYYY") &&
					shipStart.format("MM") == tomorrow.format("MM") &&
					shipStart.format("DD") == tomorrow.format("DD") && parseInt(a.shipped) == 0); 
		});	
	}

	$('#branch').change(updateTables);

	function updateTables() {
		getShipping();
		populateTables();
	}         
    
	function populateTables() {
		var todaysTable = $('#todayTable .table-rows-table');
		var tomorrowsTable = $('#tomorrowTable .table-rows-table');
		todaysTable.empty();
		tomorrowsTable.empty();

		if (todaysBookings.length == 0) {
		   var row = document.createElement('tr');
							$(row).append($('<td></td>').text("No Kits to Ship"));
							todaysTable.append($(row));
		} else {
			todaysBookings.forEach(function(booking) {
				
				var bookingID = booking.bookingID;
				var row = document.createElement('tr');
				$(row).attr('id', bookingID);
				$(row).append($('<td></td>').text(booking.barcode))
					.append($('<td></td>').text(booking.hname))
					.append($('<td></td>').text(branchList[booking.destination]));

				todaysTable.append($(row));

				todaysTable.on('click', '#'+bookingID, function() {
					$('.selected').each(function() {
					   $(this).removeClass('selected');
					});
					$(this).addClass('selected');
					$(".navMenu.footer").animate({bottom:"2%"}, 400, function(){});
					var newRoute = submitRoute.replace("%7Bbookings%7D", bookingID);
					$("#form-shipping").attr("action", newRoute);
				});  
			});
		}

		if (tomorrowsBookings.length == 0) {
		   var row = document.createElement('tr');
							$(row).append($('<td></td>').text("No Kits to Ship"));
							tomorrowsTable.append($(row));
		} else {
			tomorrowsBookings.forEach(function(booking) {
				var row = document.createElement('tr');
				$(row).attr('id',booking.bookingID);
				$(row).append($('<td></td>').text(booking.barcode))
						.append($('<td></td>').text(booking.hname))
						.append($('<td></td>').text(branchList[booking.destination]));
				tomorrowsTable.append($(row));
			});
		}
	}

	function datesEqual(a,b) {
		return ((a.getMonth() == b.getMonth()) && 
				(a.getFullYear() == b.getFullYear()) && 
				(a.getDate() == b.getDate()));
	}

	//override our form button with the one in the footer nav
	$('#createBooking').click(function(e) {
		e.preventDefault();
		$('#form-shipping').submit();
	});
});
    
</script>
@stop
