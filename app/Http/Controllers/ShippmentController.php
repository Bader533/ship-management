<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Shippment;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Milon\Barcode\DNS1D;


class ShippmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('Read-Shippments')) {
            abort(403);
        } else {
            $shipment = Shippment::where('user_id', auth()->user()->id)->paginate(100);
            return view('Dashboard.user.shipment.index1', ['shipment' => $shipment]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('Create-Shippment')) {
            abort(403);
        } else {
            $city = City::all();
            return view('Dashboard.user.shipment.create', ['city' => $city]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('Create-Shippment')) {
            abort(403);
        } else {
            $validator = Validator($request->all(), [
                'shipment_type' => 'required',
                'shipper' => 'required',
                'city' => 'required',
                'area' => 'required',
                'business' => 'required',
                'receiver_name' => 'required',
                'receiver_phone' => 'required|numeric',
                'address' => 'required',
                'package' => 'max:150',
                'price' => 'required|numeric',
                'note' => 'max:150',
            ]);
            $code = random_int(100000, 999999);
            if (!$validator->fails()) {
                $shipment = new Shippment();
                if ($request->active == 1) {
                    $shipment->allow_open = 'true';
                } elseif ($request->active == 0) {
                    $shipment->allow_open = 'false';
                }
                $shipment->shippment_type = $request->input('shipment_type');
                $shipment->shipper = $request->input('shipper');
                $shipment->city_id = $request->input('city');
                $shipment->area_id = $request->input('area');
                $shipment->business_referance = $request->input('business');
                $shipment->receiver_name = $request->input('receiver_name');
                $shipment->receiver_phone = $request->input('receiver_phone');
                $shipment->user_id = $request->input('user_id');
                $shipment->price = $request->input('price');
                $shipment->package_details = $request->input('package_details');
                $shipment->address = $request->input('address');
                $shipment->note = $request->input('note');
                $shipment->status = 'created';
                $shipment->barcode = random_int(100000, 999999);
                $isSaved = $shipment->save();
                $tracking = new Tracking();
                $tracking->shippment_id = $shipment->id;
                $tracking->status = $shipment->status;
                $Saved = $tracking->save();
                return response()->json(
                    [
                        'message' => $isSaved ? 'Shipment created successfully' : 'Create failed!'
                    ],
                    $isSaved ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST,
                );
            } else {
                return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shippment  $shippment
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('Show-Shippment', $id)) {
            abort(403);
        } else {
            $shipment = Shippment::findOrFail($id);

            return  view('Dashboard.user.shipment.show', ['shippment' => $shipment]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shippment  $shippment
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('Update-Shippment')) {
            abort(403);
        } else {
            $city = City::all();
            $shipment = Shippment::findOrFail($id);
            $type = ['forward', 'exchange', 'cash_collection', 'return_pickup'];
            return view(
                'Dashboard.user.shipment.edit',
                ['shipment' => $shipment, 'city' => $city, 'type' => $type]
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shippment  $shippment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('Update-Shippment')) {
            abort(403);
        } else {
            $validator = Validator($request->all(), [
                // 'receiver_phone' => 'numeric',
                'package' => 'max:150',
                'price' => 'numeric',
                'note' => 'max:150',
            ]);
            if (!$validator->fails()) {
                $shipment = Shippment::findOrFail($id);
                if ($request->active == 1) {
                    $shipment->allow_open = 'true';
                } elseif ($request->active == 0) {
                    $shipment->allow_open = 'false';
                }
                $shipment->shippment_type = $request->input('shipment_type');
                $shipment->shipper = $request->input('shipper');
                $shipment->city_id = $request->input('city');
                $shipment->area_id = $request->input('area');
                $shipment->business_referance = $request->input('business');
                $shipment->receiver_name = $request->input('receiver_name');
                $shipment->receiver_phone = $request->input('receiver_phone');
                // $shipment->allow_open = $request->active;
                $shipment->user_id = $request->input('user_id');
                $shipment->price = $request->input('price');
                $shipment->package_details = $request->input('package_details');
                $shipment->address = $request->input('address');
                $shipment->note = $request->input('note');
                $isSaved = $shipment->save();
                return response()->json(
                    [
                        'message' => $isSaved ? 'Shipment created successfully' : 'Create failed!'
                    ],
                    $isSaved ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST,
                );
            } else {
                return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shippment  $shippment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('Delete-Shippment')) {
            abort(403);
        } else {
            $shippment = Shippment::findOrFail($id);
            // if($shippment->status){}
            if ($shippment->status == "created") {
                $isDeleted = $shippment->delete();
                return response()->json(
                    ['message' => $isDeleted ? 'Deleted successfully' : 'Delete failed!'],
                    $isDeleted ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
                );
            } else {
                return response()->json(
                    ['message' => 'Delete failed!'],
                    // $isDeleted ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
                );
            }
        }
    }
}
