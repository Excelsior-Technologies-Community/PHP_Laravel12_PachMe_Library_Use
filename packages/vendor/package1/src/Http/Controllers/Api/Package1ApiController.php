<?php

namespace Vendor\Package1\Http\Controllers\Api;

use Vendor\Package1\Http\Controllers\Controller;
use Illuminate\Http\Request;

class __PACKAGE_UC__Controller extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Hello from package1 API']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Created'], 201);
    }
}
