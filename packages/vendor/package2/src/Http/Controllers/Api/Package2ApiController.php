<?php

namespace Vendor\Package2\Http\Controllers\Api;

use Vendor\Package2\Http\Controllers\Controller;
use Illuminate\Http\Request;

class __PACKAGE_UC__Controller extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Hello from package2 API']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Created'], 201);
    }
}
