@extends('layouts.header')
@section('headerScript')
{{ HTML::style('css/editkit.css') }}
{{ HTML::script('fullcalendar/lib/jquery.min.js') }}
{{ HTML::script('js/Hardware.js') }}
{{ HTML::script('js/post.js') }}

{{ HTML::style('css/hardware.css') }}


<script>

	
	
$(document).ready(function() {
	//since we are already in the kit edit page,
	//we don't need our device info to include a reference url to
	//this page
	$('#hw-kitinfo').hide();
	$('#deleteForm').hide();
	
	var kitDevices = {{json_encode($devices);}}


	var HWForm = HardwareForm.init({
		devices: kitDevices,
		hwInfoRoute: "{{ route('hardware.get'); }}",
		kitInfoRoute: "{{ route('kits.edit'); }}"
	});
	
	HWForm.getStart(function(){
		$('.loadingImg').show();
		console.log("start");
	});
	HWForm.getDone(function(){
		console.log("Done");
		$('.loadingImg').hide();
	});
	

	$('#assetTable').on('click', '.deviceRow', function() {
		$('.selected').each(function(){ $(this).toggleClass('selected') });
		$(this).toggleClass('selected');
		HWForm.fill($(this).attr('id'));
	});
	
	//check if there are any devices

	//var devExists = $('#assetTable tr').first();

	if (kitDevices.length > 0) {
		HWForm.fill(kitDevices[0].id);
		$('#'+kitDevices[0].id + '.deviceRow').toggleClass('selected');
	} else {
		HWForm.fill(0);
		$('#0.deviceRow').toggleClass('selected');	
	}
	
	HWForm.post();
});
</script>



@stop
@section('browsekitsli') class="" @stop
@section('content')
<div class="sideBySide">
<div id="kitInfo">
     {{ Form::open(['method' => 'post', 'route' => 'kits.edit']) }}
     	<div class="inputs">
	<div>
		{{ Form::label('kitNumber', 'Bar Code:') }}
    		{{ Form::text('KitNumber', $kits->barcode) }}
	</div>
	<div>
		{{ Form::label('currentBranch', 'Current Branch:') }}
		{{ Form::select('CurrentBranch',Branch::lists("name","id"), $kits->currentBranchID, ['id'=>'CurrentBranch'] ) }}
	</div>
	<div>
		{{ Form::label('description', 'Description:') }}
		{{ Form::Input('string', 'description', $kits->description) }}
	</div>
	</div>
	<div>
	{{ Form::label('assetTable','Items within this kit') }}
	<table id="assetTable">
		<thead>
			<th>Name</th>
			<th>Condition</th>
		<thead>
		<tbody>
			@if(count($devices) > 0)
				@foreach($devices as $device)
				<tr class="deviceRow" id={{$device->id}}>
					<td>{{ $device->name }}</td>
					@if($device->damaged)
						<td>Damaged</td>
					@else
					  <td>None</td>
					@endif
				</tr>
				@endforeach
			@else
				<tr class="deviceRow" id="0">
					<td>No Devices</td>
					<td></td>
				</tr>
			@endif
		</tbody>
	</table>
	</div>
	<div>
	{{ Form::Submit('Apply Changes') }}
	</div>		       
{{ Form::close() }}
</div>
@if(count($devices) > 0)
	<div id="hardware">
		@include('layouts.hardware')
	</div>
	<div class="loadingImg">
		<img src="/images/loading_spinner.gif"/>
	</div>

@endif
</div>


@stop