<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;



class CustomServices
{
    public static function customResponse($datatype, $data, $code, $options=[]){
        return response()->json([
           $datatype => $data
        ], $code);

}
}
