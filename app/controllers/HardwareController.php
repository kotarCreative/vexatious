<?php
class HardwareController extends \BaseController {
	protected $fields = ['type', 'assetTag', 'damaged'];
	
    public function __construct()
    {
        $this->beforeFilter('auth');
    }
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		
		$devices = Hardware::join('hardwareType', 'hardware.hardwareTypeID', '=', 'hardwareType.id')
				->leftJoin('kithardware', 'hardware.id', '=', 'kithardware.hardwareID')
				->leftJoin('kit', 'kithardware.kitID', '=', 'kit.id')
				->get(['hardware.id', 'assetTag', 'damaged', 'hardwareType.name',  
						 'hardwareType.description', 'kit.id as kitID', 'kit.barcode', 
					   'hardware.hardwareTypeID as type']);
		
		//$devices = Hardware::all();
		if(Request::wantsJson()) {
			if($devices) return Response::json(["status"=>"0", "devices"=>$devices]);
			return Response::json(["status"=>"1", "devices"=>[]]);
		}
		return View::make('hardware.index')->with('devices', $devices);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('hardware/create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
//		if (!Auth::user()->isAdmin()) return Redirect::back();
		
        $device = new Hardware;
		$device->hardwareTypeID = Input::get('type');
		$device->assetTag = Input::get('assetTag');
        $device->save();

		if(Request::wantsJson()) {
			return Response::json(["status"=>"0", "device"=>$device]);
		}
		return Redirect::route('hardware.show', $device->id);
			//Redirect::back();     
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */	
	public function show($id)
	{
		
		$device = Hardware::where('hardware.id', '=', $id)
				->join('hardwareType', 'hardware.hardwareTypeID', '=', 'hardwareType.id')
				->leftJoin('kithardware', 'hardware.id', '=', 'kithardware.hardwareID')
				->leftJoin('kit', 'kithardware.kitID', '=', 'kit.id')
				->first(['hardware.id', 'assetTag', 'damaged', 'hardwareType.name',  
						 'hardwareType.description', 'kit.id as kitID', 'kit.barcode']);
		
		//handle json request types
		if(Request::wantsJson()) {
			if($device)return Response::json(["status"=>"0", "device"=>$device]);
			return Response::json(["status"=>"1", "device"=>[]]);
		}
		
		//return Response::json($device);
		return View::make('hardware/show')->with('hardware', $device);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if (!Auth::user()->isAdmin()) return Redirect::back();
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//$user = Auth::user();
		//if (!$user || !$user->isAdmin()) return Redirect::back();
		
		//$response = ["status" => "1"];
		$device = Hardware::find($id);
		if($device) {
			//$input = array_filter(Input::only($this->fields));
			//$device->fill($input);
			$clearDamage = Input::get('clear');
			if($clearDamage) {
				$device->damaged = null;
			} else {
				$newDamage = trim(Input::get('damaged'));
				
				if ($device->damaged == null) $device->damaged = $newDamage;
				else {
					$device->damaged = $device->damaged."\n".$newDamage;	
				}
			}
			
			$device->save();
		}
	
		//handle json request types
		if(Request::wantsJson()) {
			if($device)return Response::json(["status"=>"0", "device"=>$device]);
			return Response::json(["status"=>"1", "device"=>[]]);
		}
		return Redirect::route('hardware.show', ['id'=>$id]);
	}
	
	public function addToKit($kitID, $hwID) {
		//if(!Auth::user()->isAdmin()) return Redirect::back();
		
		
		KitHardware::create(array(
			"kitID" => $kitID,
			"hardwareID" => $hwID
		));
		
		
		return Redirect::back();
	}
	
	public function removeFromKit($kitID, $hwID) {
		//if(!Auth::user()->isAdmin()) return Redirect::back();
		
		DB::table('kithardware')->where('kitID', '=', $kitID)
			->where('hardwareID', '=', $hwID)->delete();
	
		
		return Redirect::back();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//$user = Auth::user();
		//if (!$user || !$user->isAdmin()) return Redirect::back();
		
		$device = Hardware::find($id);
		
		if($device) $device->delete();
		
		return Redirect::route('hardware.index');
	}


}
