<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Stuff;
use App\helpers\ApiFormatter;
use App\models\StuffStock;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(){
        $stuffStock = StuffStock::with('stuff')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat semua barang!!', $stuffStock);

        // return response()->json([
        //  'success' => true,
        //  'message' => 'Lihat semua barang',
        //     'barang' => $stuff,
        //     'data' => $stuffStock
        // ],200);
    }

    public function store(Request $request){
        $validator = Validator::make
        ($request->all(), [
            'stuff_id' => 'required',
            'total_available' => 'required',
            'total_defect' => 'required'
        ]);

        if($validator->fails()){
            return ApiFormatter::sendResponse(400, false, 'Semua kolom wajib diisi', $validator->errors);
            // return response()->json([
            //   'success' => false,
            //   'message' => 'Semua kolom wajib disi!',
            //     'data' => $validator->errors()
            // ],400);
        } else{
            $stock = StuffStock::updateOrCreate([
                'stuff_id' => $request->input('stuff_id')
            ],[
                'total_available' => $request->input('total_available'),
                'total_defect' => $request->input('total_defect')
            ]);


            if($stock) {
                return ApiFormatter::sendResponse(200, true, 'Barang berhasil ditambahkan', $stock);
                // return response()->json([
                //  'success' => true,
                //  'message' => 'Barang berhasil ditambahkan',
                //     'data' => $stock
                // ],200);
            } else{
                return ApiFormatter::sendResponse(400, false, 'Barang gagal ditambahkan');

            }
        }
    }


    public function show($id){
        try{
            $stock = StuffStock::with('stuff')->find($id);

            return ApiFormatter::sendResponse(200, true, 'Lihat semua stock barang dengan id. ' . $id, $stock);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Lihat semua stock barang dengan id ' . $id,
            //     'data' => $stock
            // ], 200);
        } catch(\Throwable $th){

            return ApiFormatter::sendResponse(400, false, 'Barang dengan id . '. $id . ' tidak ditemukan', $stock);
            // return response() -> json([
            //     'success' => false,
            //     'message' => 'dara dengan id' . $id .'tidak ditemukan'
            // ], 400);
        }
    }

    public function update(Request $request, $id) {
        try{
            $stock = StuffStock::with('stuff')->find($id);


            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $stock->stuff_id;
            $total_available = ($request->total_available) ? $request->total_available : $stock->total_available;
            $total_defect = ($request->total_defect) ? $request->total_defect : $stock->total_defect;

            if ($stock) {
                $stock->update([
                    'stuff_id' => $stuff_id,
                    'total_available' => $total_available,
                    'total_defect' => $total_defect
                ]);

            return ApiFormatter::sendResponse(200, true, 'Barang berhasil diubah', $stock);
                // return response()->json([
                //   'success' => true,
                //   'message' => 'Barang berhasil diubah',
                //     'data' => $stock
                // ],200);
            } else{
                return ApiFormatter::sendResponse(400, false, 'gagal', $stock);
                // return response()->json([
                //     'success' => false,
                //     'message' => 'Proses gagal',
                //   ],400);
            }
        } catch(\Throwable $th){
            return ApiFormatter::sendResponse(400, false, 'Bad request', $stock);
            // return response()->json([
            //   'success' => false,
            //   'message' => 'Proses gagal! data dengan id '.$id.' tidak ditemukan',
            // ],400);
        }
    }

    public function destroy($id){
        try{
            $stuffStock = stuffStock::findOrFail($id);

            $stuffStock->delete();
            return ApiFormatter::sendResponse(400, false, 'Barang dihapus', $stuffStock);
            // return response()->json([
            //  'success' => true,
            //  'message' => 'Barang Hapus Data dengan id' . $id,
            //     'data' => $stuffStock
            // ],200);
        } catch(\Throwable $th){
            return ApiFormatter::sendResponse(400, false, 'Gagal', $stock);
            // return response()->json([
            // 'success' => false,
            // 'message' => 'Proses gagal! data dengan id '.$id.' tidak ditemukan',
            // ],400);
        }
    }

    public function deleted(){
        try{
            $stock = stuffstock::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, 'Melihat data yang dihapus', $stock);

        } catch(\Throwable $th){
            return ApiFormatter::sendResponse(404, false, 'Proses gagal ', $th->getMessage());
        }

    }

    public function restore($id){
        try{
            $stock = Stuffstock::onlyTrashed()->where('id', $id)->restore();
            $has_stock = Stuffstock::where('stuff_id', $stock->stuff_id)->get();

            if ($has_stock->count() == 1) {
                $message = "Data stok sudah ada, tidak boleh ada duplikat data stok untuk satu barang silahkan update data stok dengan id stock . ". $stock->stuff_id;
            } else{
                $stock->restore();
                $message = "Berhasil mengembalikan data yang telah dihapus";
            }
            if ($stock) {
                $data = stuffstock::find($id);
                return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $stock->stuff_id]);
            } else{
                return ApiFormatter::sendResponse(404, false, 'bad request');
            }


        } catch(\Throwable $th){
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
}

public function restoreAll(){
    try{
        $stock = stuffstock::onlyTrashed()->restore();
        if ($stock) {
            return ApiFormatter::sendResponse(200, true, 'Berhasil mengembalikan semua data yang dihapus');
        } else{
            return ApiFormatter::sendResponse(400, false, 'bad request');

        }

    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
    }
}

public function permanentDel($id){
    try{
        $stock = stuffstock::onlyTrashed()->where('id', $id)->forceDelete();

        if ($stock) {
            $check = stuffstock::onlyTrashed()->where('id', $id)->get();
            return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen data dengan id = '. $id, $check);
        } else{
            return ApiFormatter::sendResponse(200, true, 'Bad request');
        }


    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
    }
}

public function permanentDelAll(){
    try{
        $stock = stuffstockw::onlyTrashed()->forceDelete();
        if ($stock) {
            return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen semua data');
        } else{
            return ApiFormatter::sendResponse(400, false, 'bad request');
        }
    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
    }
}
}
