<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class CustomerController extends Controller
{
    public function getData(Request $request)
    {
        $data = [];
        // $email = $request->email;
        try { 
            $data = DB::table('customers')->paginate(10);
            if(count($data)>0){
                $msg = 'Getting data success';
                $st  = true;
            }else{
                $msg = 'No data available';
                $st  = true;
            }
        } catch(\Illuminate\Database\QueryException $ex){ 
            $msg = "Getting data failed: " . $ex->getMessage();
            $st  = false;
        }
        return response()->json([
            'success' => $st,
            'message' => $msg,
            'data' => $data
        ]);
    }
    public function searchData(Request $request)
    {
        $data = [];
        $search = $request->keyword;
        // var_dump($search);exit;
        try { 
        
            $field = ['name','gender','address','handphone','telephone','email','username'];
            $data = DB::table('customers')->Where(function ($query) use($search, $field) {
                        for ($i = 0; $i < count($field); $i++){
                            $query->orwhere($field[$i], 'like',  '%' . $search .'%');
                        }      
                    })->get();
           
            if(count($data)>0){
                $msg = 'Getting data success';
                $st  = true;
            }else{
                $msg = 'No data available';
                $st  = true;
            }
        } catch(\Illuminate\Database\QueryException $ex){ 
            $msg = "Getting data failed: " . $ex->getMessage();
            $st  = false;
        }
        return response()->json([
            'success' => $st,
            'message' => $msg,
            'data' => $data
        ]);
    }
    public function getByID(Request $request)
    {
        $data = [];
        $criteria = array(
            'id' => $request->id, 
        );

        try { 
            $data = DB::table('customers')
            ->where($criteria)
            ->get();
            if(count($data)>0){
                $msg = 'Getting data success';
                $st  = true;
            }else{
                $msg = 'No data available';
                $st  = true;
            }
        } catch(\Illuminate\Database\QueryException $ex){ 
            $msg = "Get data failed: " . $ex->getMessage();
            $st  = false;
        }
        return response()->json([
            'success' => $st,
            'message' => $msg,
            'data' => $data
        ]);
    }
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:60',
            'gender'       => 'required|string',
            'address'      => 'required|string',
            'telephone'    => 'max:16',
            'handphone'    => 'required|max:16',
            'email'        => 'required|email',
            'username' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        try{
            $user = User::where('username', $request->username)->first();
            if (! $user ) {
                return response()->json([ 'success' => false,'message'=>'Username is invalid/not available.' ], 401);
            }
            $datacust = array(
                'name'         => $request->name,
                'gender'       => $request->gender,
                'address'      => $request->address,
                'telephone'    => $request->telephone,
                'handphone'    => $request->handphone,
                'email'        => $request->email,
                'username'     => $request->username
            );
            if($request->id==0){
                DB::table('customers')->insert($datacust);
                $msg = "Data has been successfully saved";
                $st = true;
            }else{
                $criteria = array('id' => $request->id);
                unset($datacust['username']);
                DB::table('customers')->where($criteria)->update($datacust);
                $msg = "Data has been successfully updated";
                $st = true;
            }
            
        } catch(\Illuminate\Database\QueryException $ex){ 
            $msg = "Save failed: " . $ex->getMessage();
            $st = false;
        }
        return response()->json([
            'success' => $st,
            'message' => $msg
        ]);
    }
    public function delete($id)
    {
      
        $criteria = array(
            'id' => $id
        );
        try { 
            DB::table('customers')
            ->where($criteria)
            ->delete();
            $msg = "Data has been deleted successfully";
            $st  = true;
        } catch(\Illuminate\Database\QueryException $ex){ 
            $msg = "Delete failed: " . $ex->getMessage();
            $st  = false;
        }

        return response()->json([
            'success' => $st,
            'message' => $msg
        ]);
    }
}
