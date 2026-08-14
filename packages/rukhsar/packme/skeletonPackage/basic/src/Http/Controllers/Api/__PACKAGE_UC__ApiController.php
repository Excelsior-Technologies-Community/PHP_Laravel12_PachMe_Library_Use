<?php

namespace :VendorName\:PackageName\Http\Controllers\Api;

use :VendorName\:PackageName\Http\Controllers\Controller;
use Illuminate\Http\Request;

class __PACKAGE_UC__Controller extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Hello from :package_name API']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Created'], 201);
    }
}
