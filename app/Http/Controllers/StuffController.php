<?php

namespace App\Http\Controllers;

use App\models\Stuff;
use App\models\stuffStock;
use App\models\lending;
use App\models\inboundStuff;
use App\helpers\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $data = Stuff::with('inboundstuff', 'stuffstock')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat semua barang', $data);
    }


    public function store(Request $request)
    {

        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);

            $data = stuff::create([
                'name' => $request->name,
                'category' => $request->category,
            ]);
            return ApiFormatter::sendResponse(201, true, 'Barang berhasil disimpan', $data);
        } catch (\Throwable $th) {
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input', $th->validator->errors);
            } else {
                return ApiFormatter::sendResponse(201, true, 'Terdapat kesalahan input!!', $th->getMessage());
            }
        }
        //     $validator = Validator::make
        //     ($request->all(), [
        //         'name' => 'required',
        //         'category' => 'required'
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //          'success' => false,
        //          'message' => 'Semua kolom wajib disi!',
        //          'data' => $validator->errors()
        //         ],400);
        // } else {
        //     $stuff = Stuff::create([
        //         'name' => $request->input('name'),
        //         'category' => $request->input('category')
        //     ]);
        // }


        if ($stuff) {
            return ApiFormatter::sendResponse(200, true, 'Barang berhasil ditambahkan', $stuff);
            // return response()->json([
            //   'success' => true,
            //   'message' => 'Barang berhasil ditambahkan',
            //     'data' => $stuff
            // ],200);
        } else {
            return ApiFormatter::sendResponse(400, false, 'Barang gagal ditambahkan', $stuff);
            // return response()->json([
            //   'success' => false,
            //   'message' => 'Barang gagal ditambahkan',
            // ],400);
        }
    }

    public function show($id)
    {
        try {
            $stuff = Stuff::findOrFail($id);
            return ApiFormatter::sendResponse(201, true, 'Barang dengan data ' . $id, $stuff);
            // return response()->json([
            //  'success' => true,
            //  'message' => 'Lihat Barang dengan id $id',
            //     'data' => $stuff
            // ],200);

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(400, false, 'Barang dengan data ' . $id . 'tidak ditemukan', $th->getMessage());
            //     return response()->json([
            //    'success' => false,
            //    'message' => 'Data dengan id $id tidak ditemukan',
            //     ],400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stuff = Stuff::findOrFail($id);
            $name = ($request->name) ? $request->name : $stuff->name;
            $category = ($request->category) ? $request->category : $stuff->category;

            $stuff->update([
                'name' => $name,
                'category' => $category
            ]);

            return ApiFormatter::sendResponse(200, true, 'Barang dengan data ' . $id . 'Berhasil diubah!!', $stuff);
            // if ($stuff) {
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Barang Ubah Data dengan id $id',
            //         'data' => $stuff
            //     ],200);
            // } else{
            // return response()->json([
            //   'success' => false,
            //   'message' => 'Proses gagal',
            // ],400);
            // }


        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(400, false, 'Proses gagal!!', $th->getMessage());
            // return response()->json([
            //   'success' => false,
            //   'message' => 'Proses gagal! data dengan id $id tidak ditemukan',
            // ],400);
        }
    }

    public function destroy($id)
    {
        try {
            $stuffStock = Stuffstock::where('stuff_id', $id)->first();
            $inboundstuff = InboundStuff::where('stuff_id', $id)->first();
            $lending = Lending::where('stuff_id', $id)->first();

            if ($lending) {
                return ApiFormatter::sendResponse(400, false, 'Tidak dapat menghapus data stuff, sudah terdapat data lending!!!', $lending);
            }elseif ($inboundstuff) {
                return ApiFormatter::sendResponse(400, false, 'Tidak dapat menghapus data stuff, sudah terdapat data inbound!!!', $inboundstuff);
            }elseif ($stuffStock) {
                return ApiFormatter::sendResponse(400, false, 'Tidak dapat menghapus data stuff, sudah terdapat data stuffstock!!!', $stuffStock );
            } else{
                $stuff = stuff::findORFail($id);
                $stuff->delete();
                return ApiFormatter::sendResponse(200, true, 'Data stuff dengan id ' . $stuff['id'] . ' berhasil dihapus.', $stuff);
            }



            // return response()->json([
            //  'success' => true,
            //  'message' => 'Barang Hapus Data dengan id $id',
            //     'data' => $stuff
            // ],200);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(400, false, 'Barang dengan data ' . $id . ' gagal dihapus!!', $th->getMessage());
            // return response()->json([
            // 'success' => false,
            // 'message' => 'Proses gagal! data dengan id $id tidak ditemukan',
            // ],400);
        }
    }

    public function deleted()
    {
        try {
            $stuffs = stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, 'Melihat data yang dihapus', $stuffs);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagal ', $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id)->restore();

            if ($stuff) {
                $data = Stuff::find($id);
                return ApiFormatter::sendResponse(200, true, 'Berhasil mengembalikan data yang dihapus dengan id = ' . $id, $data);
            } else {
                return ApiFormatter::sendResponse(404, false, 'bad request');
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $stuff = Stuff::onlyTrashed()->restore();
            if ($stuff) {
                return ApiFormatter::sendResponse(200, true, 'Berhasil mengembalikan semua data yang dihapus');
            } else {
                return ApiFormatter::sendResponse(400, false, 'bad request');
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }

    public function permanentDel($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

            if ($stuff) {
                $check = stuff::onlyTrashed()->where('id', $id)->get();
                return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen data dengan id = ' . $id, $check);
            } else {
                return ApiFormatter::sendResponse(200, true, 'Bad request');
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }

    public function permanentDelAll()
    {
        try {
            $stuff = Stuff::onlyTrashed()->forceDelete();
            if ($stuff) {
                return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen semua data');
            } else {
                return ApiFormatter::sendResponse(400, false, 'bad request');
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }
}
