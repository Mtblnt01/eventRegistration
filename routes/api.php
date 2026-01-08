<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;


//Without authentication
Route::get('/ping', function() {return response()->json(['message' => 'API működik']);});
Route::post('/register', function() {return response()->json(['message' => 'API működik']);});


//Authenticated routes

//CRUD

//CRUD only Admin

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
