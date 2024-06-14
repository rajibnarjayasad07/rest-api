<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\lending;
use App\models\restoration;
use App\models\Stuff;
use App\models\StuffStock;
use App\models\User;
use App\helpers\ApiFormatter;
use Illuminate\Support\Facades\Validator;
use Exception;

class lendingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(){
        $data = lending::with('stuff', 'user', 'stuff.stuffStock')->get();
        return ApiFormatter::sendResponse(200, true, 'Berhasil melihat semua data lending', $data);
    }

    public function store(Request $request){

        try{
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required'
            ]);

        $totalAvailable = StuffStock::Where('stuff_id', $request->stuff_id)->value('total_available');

        if (is_null($totalAvailable)) {
            return ApiFormatter::sendResponse(400, 'Bad Request', 'Belum ada data inbound');
        }

        elseif ((int)$request->total_stuff > (int)$totalAvailable) {
            return ApiFormatter::sendResponse(400, 'Bad Request', 'Stock tidak tersedia');
    } else {
        $lending = lending::create([
            'stuff_id' => $request->input('stuff_id'),
            'date_time' => $request->input('date_time'),
            'name' => $request->input('name'),
            'user_id' => auth()->user()->id,
            'notes' => $request->notes ? $request->notes : '-',
            'total_stuff' => $request->input('total_stuff'),
        ]);

        $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
        $stuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);

        $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.stuffStock')->first();

        return ApiFormatter::sendResponse(200, true, 'Berhasil menambahkan data yang diinput!', $dataLending);
    }
}  catch (\Exception $err){
    return ApiFormatter::sendResponse(400, 'Bad request', $err->getMessage());
}

    }



    public function show($id){
        try{
            $lending = lending::where('id', $id)->with('user', 'restorations', 'restorations.user', 'stuff', 'stuff.stuffstock')->first();
            return ApiFormatter::sendResponse(200, true, 'Lihat barang dengan id' . $id, $lending);

    } catch(Exception $th) {
        return ApiFormatter::sendResponse(400, false, 'Gagal melihat barang');
    }
}

public function update(Request $request, $id){
    try{
        $lending = lending::findOrFail($id);
        $stuff_id = ($request->stuff_id) ? $request->stuff_id : $lending->stuff_id;
        $date_time = ($request->date_time)? $request->date_time : $lending->date_time;
        $name = ($request->name)? $request->name : $lending->name;
        $user_id = ($request->user_id)? $request->user_id : $lending->user_id;
        $notes = ($request->notes)? $request->notes : $lending->notes;
        $total_stuff = ($request->total_stuff)? $request->total_stuff : $lending->total_stuff;

        if ($lending) {
            $lending->update([
                'stuff_id' => $stuff_id,
                'date_time' => $date_time,
                'name' => $name,
                'user_id' => $user_id,
                'notes' => $notes,
                'total_stuff' => $total_stuff,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang Ubah Data dengan id '.$id,
                    'data' => $lending
                ],200);
        } else{
            return response()->json([
              'success' => false,
              'message' => 'Proses gagal',
            ],400);
        }


    } catch(\Throwable $th){
        return response()->json([
          'success' => false,
          'message' => 'Proses gagal! data dengan id '.$id.' tidak ditemukan',
        ],400);
    }

}

public function destroy($id){
    try{
        $lending = Lending::findOrFail($id);
        $checkRes = Restoration::where('lending_id', $lending->id)->first();
        $stuffStock = StuffStock::where('stuff_id', $lending->stuff_id);
        $totalAvailable = StuffStock::Where('stuff_id', $lending->stuff_id)->value('total_available');


        if ($checkRes) {
            return ApiFormatter::sendResponse(400, false, 'Barang gagal dihapus!!', $checkRes);
        } else {
            $availableUpdate = (int)$lending->total_stuff + $totalAvailable;
            $stuffStock->update([
                'total_available' => $availableUpdate
            ]);
            $lending->delete();
            return ApiFormatter::sendResponse(200, true, 'Barang dihapus dengan data id = ' .$id, $checkRes);
        }



    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(400, false, 'Proses gagal data dengan id' . $id . 'tidak ditemukan', $err->getMessage());
    }
}
}
