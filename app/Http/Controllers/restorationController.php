<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\lending;
use App\models\stuffstock;
use App\models\restoration;
use App\models\user;
use App\helpers\ApiFormatter;
use Illuminate\Support\Facades\Validator;

class restorationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(){
        $restoration = restoration::all();

        return response()->json([
            'success' => true,
            'message' => 'Lihat semua barang',
            'data' => $restoration
        ],200);
    }

    public function store(Request $request){
        try{
            $this->validate($request, [
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
            ]);

            $lending = Lending::where('id', $request->lending_id)->first();
            $totalStuffRestoration = (int)$request->total_good_stuff + (int)$request->total_defec_stuff;

            if ((int)$totalStuffRestoration > (int)$lending['total_stuff']) {
                return ApiFormatter::sendResponse(400, 'bad request', 'total barang kembali lebih banyak dari barang yang dipinjam!!');
        } else {
            $restoration = restoration::updateOrCreate([
                'lending_id' => $lending['id']
            ], [
                'user_id' => auth()->user()->id,
                'date_time' => $request->input('date_time'),
                'total_good_stuff' => $request->input('total_good_stuff'),
                'total_defec_stuff' => $request->input('total_defec_stuff'),
            ]);

            $stuffStock = stuffStock::where('stuff_id', $lending['stuff_id'])->first();
            $totalAvailableStock = (int)$stuffStock['total_available'] + (int)$request->total_good_stuff;
            $totalDefecStock = (int)$stuffStock['total_defect'] + (int)$request->total_defec_stuff;
            $stuffStock->update([
                'total_available' => $totalAvailableStock,
                'total_defect' => $totalDefecStock
            ]);

            $lendingRestoration = lending::where('id', $lending['id'])->with('user', 'restorations', 'stuff', 'stuff.stuffstock')->first();
            return ApiFormatter::sendResponse(200, true, 'data berhasil ditambahkan', $lendingRestoration);
        }

        }

        catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }


}

public function show($id){
    try{
        $restoration = restoration::findOrFail($id);
        return response()->json([
         'success' => true,
         'message' => 'Lihat Barang dengan id $id',
            'data' => $restoration
        ],200);

} catch(Exception $th) {
    return response()->json([
   'success' => false,
   'message' => 'Data dengan id $id tidak ditemukan',
    ],400);
}
}


public function update(Request $request, $id){
    try{
        $restoration = restoration::findOrFail($id);
        $user_id = ($request->user_id) ? $request->user_id : $restoration->user_id;
        $lending_id = ($request->lending_id)? $request->lending_id : $restoration->lending_id;
        $date_time = ($request->date_time)? $request->date_time : $restoration->date_time;
        $total_good_stuff = ($request->total_good_stuff)? $request->total_good_stuff : $restoration->total_good_stuff;
        $total_defec_stuff = ($request->total_defec_stuff)? $request->total_defec_stuff : $restoration->total_defec_stuff;

        if ($restoration) {
            $restoration->update([
                'user_id' => $user_id,
                'lending_id,' => $lending_id,
                'date_time,' => $date_time,
                'total_good_stuff,' => $total_good_stuff,
                'total_defec_stuff,' => $total_defec_stuff,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang Ubah Data dengan id $id',
                    'data' => $restoration
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
          'message' => 'Proses gagal! data dengan id $id tidak ditemukan',
        ],400);
    }

}

public function destroy($id){
    try{
        $restoration = restoration::findOrFail($id);

        $restoration->delete();

        return response()->json([
         'success' => true,
         'message' => 'resto$restoration dihapus Data dengan id $id',
            'data' => $restoration
        ],200);
    } catch(\Throwable $th){
        return response()->json([
        'success' => false,
        'message' => 'Proses gagal! data dengan id $id tidak ditemukan',
        ],400);
    }
}

}
