<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\lending;
use App\models\restoration;
use Illuminate\Support\Facades\Hash;
use App\models\user;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiFormatter;

class userController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(){
        $user = user::all();

        return ApiFormatter::sendResponse(200, true, 'Lihat semua barang', $user);

    }

    public function store(Request $request){
        $validator = Validator::make
        ($request->all(), [
            'username' => 'required|min:3',
            'email' => 'required',
            'password' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return ApiFormatter::sendResponse(400, false, 'Semua kolom wajib diisi', $validator->errors());
    } else {
        $user = user::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
        ]);
    }


    if ($user) {
        return ApiFormatter::sendResponse(200, true, 'Barang berhasil ditambahkan', $user);
    } else{
        return ApiFormatter::sendResponse(400, false, 'Barang gagal ditambahkan');
        // return response()->json([
        //   'success' => false,
        //   'message' => 'Barang gagal ditambahkan',
        // ],400);
    }
    }

    public function show($id){
        try{
            $user = user::findOrFail($id);
        return ApiFormatter::sendResponse(200, true, 'Lihat barang dengan id' . $id, $user);

    } catch(Exception $th) {
        return ApiFormatter::sendResponse(400, false, 'Data dengan id' . $id . ' tidak ditemukan', $user);
    //     return response()->json([
    //    'success' => false,
    //    'message' => 'Data dengan id $id tidak ditemukan',
    //     ],400);
    }
}

public function update(Request $request, $id){
    try{
        $user = user::findOrFail($id);
        $username = ($request->username) ? $request->username : $user->username;
        $email = ($request->email)? $request->email : $user->email;
        $password = ($request->password)? $request->password : $user->password;
        $role = ($request->role)? $request->role : $user->role;

        if ($user) {
            $user->update([
                'username' => $username,
                'email,' => $email,
                'password,' => $password,
                'role,' => $role,
            ]);

            return ApiFormatter::sendResponse(200, true, 'Barang diubah dengan id' . $id, $user);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Barang Ubah Data dengan id $id',
            //         'data' => $user
            //     ],200);
        } else{
            return ApiFormatter::sendResponse(400, false, 'GAGAL!!');
            // return response()->json([
            //   'success' => false,
            //   'message' => 'Proses gagal',
            // ],400);
        }


    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(200, true, 'Proses gagal!! data dengan id .' . $id . 'tidak ditemukan');
        // return response()->json([
        //   'success' => false,
        //   'message' => 'Proses gagal! data dengan id $id tidak ditemukan',
        // ],400);
    }

}

public function destroy($id){
    try{
        $user = user::findOrFail($id);

        $user->delete();

        return ApiFormatter::sendResponse(200, true, 'User dihapus dengan id ' . $id, $user);
        // return response()->json([
        //  'success' => true,
        //  'message' => 'User dihapus Data dengan id $id',
        //     'data' => $user
        // ],200);
    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(400, false, 'Proses gagal!!' . $id, $user);
        // return response()->json([
        // 'success' => false,
        // 'message' => 'Proses gagal! data dengan id $id tidak ditemukan',
        // ],400);
    }
}


public function deleted(){
    try{
        $users = user::onlyTrashed()->get();

        return ApiFormatter::sendResponse(200, true, 'Melihat data yang dihapus', $users);

    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagal ', $th->getMessage());
    }

}

public function restore($id){
    try{
        $user = user::onlyTrashed()->where('id', $id)->restore();

        if ($user) {
            $data = user::find($id);
            return ApiFormatter::sendResponse(200, true, 'Berhasil mengembalikan data yang dihapus dengan id = ' . $id, $data);
        } else{
            return ApiFormatter::sendResponse(404, false, 'bad request');
        }


    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
    }
}

public function restoreAll(){
    try{
        $user = user::onlyTrashed()->restore();
        if ($user) {
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
        $user = user::onlyTrashed()->where('id', $id)->forceDelete();

        if ($user) {
            $check = user::onlyTrashed()->where('id', $id)->get();
            return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen data dengan id = '. $id, $check);
        } else {
            return ApiFormatter::sendResponse(200, true, 'Bad request');
        }


    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
    }
}

public function permanentDelAll(){
    try{
        $user = user::onlyTrashed()->forceDelete();
        if ($user) {
            return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen semua data');
        } else{
            return ApiFormatter::sendResponse(400, false, 'bad request');
        }
    } catch(\Throwable $th){
        return ApiFormatter::sendResponse(404, false, 'Proses gagall', $th->getMessage());
    }
}
}
