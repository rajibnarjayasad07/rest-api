<?php

namespace App\Http\Controllers;

use App\models\Stuff;
use Illuminate\Support\Facades\File;
use App\models\StuffStock;
use App\models\InboundStuff;
use Illuminate\Http\Request;
use App\helpers\ApiFormatter;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class InboundStuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        $inboundStuff = InboundStuff::all();

        return response()->json([
            'success' => true,
            'message' => 'Lihat semua barang',
            'data' => $inboundStuff
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stuff_id' => 'required',
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust file validation as needed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua kolom wajib disi!',
                'data' => $validator->errors()
            ], 400);
        } else {
            $file = $request->file('proff_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(app()->basePath('public/uploads'), $filename); // Access public directory using app()->basePath('public')

            $result = StuffStock::where('stuff_id', $request->input('stuff_id'))->pluck('total_available')->first();
            $result2 = $result + $request->input('total');

            $stuffStock = StuffStock::where('stuff_id', $request->input('stuff_id'))->update(['total_available' => $result2]);

            $inboundStuff = InboundStuff::create([
                'stuff_id' => $request->input('stuff_id'),
                'total' => $request->input('total'),
                'date' => $request->input('date'),
                'proff_file' => $filename,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data' => $inboundStuff
            ], 201);
        }



        if ($inboundStuff) {

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan',
                'data' => $inboundStuff
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Barang gagal ditambahkan',
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $inboundStuff = inboundStuff::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Lihat Barang dengan id $id',
                'data' => $inboundStuff
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data dengan id $id tidak ditemukan',
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $inboundStuff = inboundStuff::findOrFail($id);
            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $inboundStuff->stuff_id;
            $total = ($request->total) ? $request->total : $inboundStuff->total;
            $date = ($request->date) ? $request->date : $inboundStuff->date;
            $proff_file = ($request->proff_file) ? $request->proff_file : $inboundStuff->proff_file;

            if ($request->file('proff_file') !== NULL) {
                $file = $request->file('proff_file');
                $filename = $stuff_id . ' - ' . strtotime($date) . strtotime(date('H:i') . '.') . $file->getClientOriginalExtension();
                $file->move('uploads', $filename); // Access public directory using app()->basePath('public')

            } else {
                $filename = $inboundStuff->proff_file;
            }

            $stock = StuffStock::where('stuff_id', $inboundStuff->stuff_id)->first();
            $stock->update([
                'total_available' => $total,
            ]);


            if ($inboundStuff) {
                $inboundStuff->update([
                    'stuff_id' => $stuff_id,
                    'total' => $total,
                    'date' => $date,
                    'proff_file' => $proff_file,
                ]);



                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil di Ubah Data dengan id $id',
                    'data' => $inboundStuff
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Proses gagal',
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Proses gagal! data dengan id ' . $id . 'tidak ditemukan',
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $inboundStuff =  inboundStuff::findOrFail($id);
            $stock = StuffStock::where('stuff_id', $inboundStuff->stuff_id)->first();


            if ((int)$stock->total_available < (int)$inboundStuff->total) {
                return ApiFormatter::sendResponse(400, false, 'jumlah total inbound yang akan dihapus lebih besar dari total available stuff saat ini');
            } else {
                $available_min = $stock->total_available - $inboundStuff->total;
                $available = ($available_min < 0) ? 0 : $available_min;
                $defect = ($available_min < 0) ? $stock->total_defect + ($available * -1) : $stock->total_defect;

                $stock->update([
                    'total_available' => $available,
                    'total_defect' => $defect
                ]);

                $inboundStuff->delete();

                return ApiFormatter::sendResponse(200, true, 'barang dihapus dengan id' . $id, $inboundStuff);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Proses gagal! data dengan id ' . $id . ' tidak ditemukan',
            ], 400);
        }
    }

    public function deleted()
    {
        try {
            $inbound = inboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, 'Melihat data yang dihapus', $inbound);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagal ', $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $inboundStuff = inboundStuff::onlyTrashed()->where('id', $id)->first();
            $stock = StuffStock::where('stuff_id', $inboundStuff->stuff_id)->first();
            $result = $stock->total_available + $inboundStuff->total;
            $stock->update([
                'total_available' => $result
            ]);
            $inboundStuff->restore();

            if ($inboundStuff) {

                $data = inboundStuff::find($id);
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
            $inboundStuff = inboundStuff::onlyTrashed()->restore();
            if ($inboundStuff) {
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
            $inboundStuff = inboundStuff::onlyTrashed()->where('id', $id)->first();



            if ($inboundStuff) {
                $imageName = $inboundStuff->proff_file;
                $check = inboundStuff::onlyTrashed()->where('id', $id)->get();
                File::delete('uploads/', $imageName);
                $inboundStuff->forceDelete();
                return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen data dengan id = ' . $id . 'dan berhasil menghapus semua data permanent dengan file name: ' . $imageName, $check);
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
            $inboundStuff = inboundStuff::onlyTrashed()->forceDelete();
            if ($inboundStuff) {
                return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen semua data');
            } else {
                return ApiFormatter::sendResponse(400, false, 'bad request');
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
        }
    }
}
