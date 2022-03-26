<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Validator;
use App\Models\User;

class OrderController extends Controller
{
    public function getData(Request $request)
    {
        $data = [];
        // $email = $request->email;
        try { 
            $data = DB::table('v_order_header')->paginate(10);
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
    public function getByID(Request $request)
    {
        $data = [];
        $criteria = array(
            'order_id' => $request->id, 
        );

        try { 
            $data = DB::table('v_order_header')
            ->where($criteria)
            ->get();
            
            if(count($data)>0){
                $msg = 'Getting data success';
                $st  = true;
                $datadtl = DB::table('orders_detail')
                ->where($criteria)
                ->get();
                if(count($datadtl)>0){
                    $data->order_detail=$datadtl;
                }else{
                    $data->order_detail=[];
                }
                $datalog = DB::table('orders_log')
                ->where($criteria)
                ->get();
                if(count($datalog)>0){
                    $data->order_log=$datalog;
                }else{
                    $data->order_log=[];
                }
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
        $order_init="TR".date('ymd');
        //     var_dump($order_init);exit;
        try{
            // var_dump($request->data_detail);exit;
            $user = User::where('username', $request->username)->first();
            // var_dump($user);
            if (! $user ) {
                return response()->json([ 'success' => false,'message'=>'Username is invalid/not available.' ], 401);
            }
            
            $sql = "SELECT max(order_id) as order_id from orders where order_id like '%".$order_init."%'";
            $checkrsvno = DB::select($sql);
            if(!empty($checkrsvno)){
                $orderid = $checkrsvno[0]->order_id;
                // var_dump($package_id);
                $next = (int)substr($orderid,-5);
                // var_dump($next);
                $next = $next+1;
                $next = str_pad($next, 5, "0", STR_PAD_LEFT); 
                $order_id = $order_init.$next;
            }else{
                $order_id = $order_init."00001";
            }
            $datahdr = array(
                'order_id'     => $order_id,
                'order_date'   => date('Y-m-d H:i:s'),
                'status'        => 'N',
                'total_item_qty'      => $request->total_item_qty,
                'total_amount'    => $request->total_amount,
                'payment_method'    => $request->payment_method,
                'courier_name'        => $request->courier_name,
                'courier_receipt'        => $request->courier_receipt,
                'username'     => $request->username
            );
            if(empty($request->order_id)){
                $insert = DB::table('orders')->insert($datahdr);
                if($insert){
                    $datadtl =[];
                    foreach ($request->data_detail as $key) {
                        $datadtl[] = array(
                            'order_id'     => $order_id,
                            'item_name'   => $key['item_name'],
                            'price'   => $key['price'],
                            'item_qty'      => $key['item_qty'],
                            'amount'    => $key['amount']
                        );
                    }
                    $insert = DB::table('orders_detail')->insert($datadtl);
                    
                    $datalog = array(
                        'order_id'     => $order_id,
                        'status'        =>'N',
                        'order_log_descs'   => 'Waktu Pemesanan',
                        'order_log_date'   => date('Y-m-d H:i:s')
                    );
                    $insert = DB::table('orders_log')->insert($datalog);
                    $msg = "Data has been successfully saved";
                    $st = true;
                }else{
                    $msg = "Save failed";
                    $st = false;
                }
                
            }else{
                $criteria = array('order_id' => $request->order_id);
                unset($datahdr['username']);
                unset($datahdr['order_id']);
                $update = DB::table('orders')->where($criteria)->update($datahdr);
                if($update){
                    DB::table('orders_detail')->where($criteria)->delete();
                    $datadtl =[];
                    foreach ($request->data_detail as $key) {
                        $datadtl[] = array(
                            'order_id'     => $request->order_id,
                            'item_name'   => $key['item_name'],
                            'price'   => $key['price'],
                            'item_qty'      => $key['item_qty'],
                            'amount'    => $key['amount']
                        );
                    }
                    $insert = DB::table('orders_detail')->insert($datadtl);

                    $datalog = array(                
                        'status'        =>'N',
                        'order_log_descs'   => 'Waktu Pemesanan',
                        'order_log_date'   => date('Y-m-d H:i:s')
                    );
                    $insert = DB::table('orders_log')->where($criteria)->update($datalog);
                    $msg = "Data has been successfully updated";
                    $st = true;
                }else{
                    $msg = "Save failed";
                    $st = false;
                }
                
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
    public function searchData(Request $request)
    {
        $data = [];
        $search = $request->keyword;
        // var_dump($search);exit;
        try { 
        
            $field = ['customer_name','customer_address','customer_handphone','order_id','payment_method','courier_name','courier_receipt','username'];
            $data = DB::table('v_order_header')->Where(function ($query) use($search, $field) {
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
    public function delete($id)
    {
      
        $criteria = array(
            'order_id' => $id
        );
        try { 
            DB::table('orders')->where($criteria)->delete();
            DB::table('orders_detail')->where($criteria)->delete();
            DB::table('orders_log')->where($criteria)->delete();
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
