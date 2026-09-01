<?php

namespace Demo\MyPackage\Http\Controllers\Api;

use Demo\MyPackage\Http\Controllers\Controller;
use Illuminate\Http\Request;

class __PACKAGE_UC__Controller extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Hello from my-package API']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Created'], 201);
    }
}
