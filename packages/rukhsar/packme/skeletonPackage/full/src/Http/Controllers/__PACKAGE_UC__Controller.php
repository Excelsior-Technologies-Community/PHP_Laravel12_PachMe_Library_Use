<?php

namespace :VendorName\:PackageName\Http\Controllers;

use :VendorName\:PackageName\Http\Controllers\Controller;
use Illuminate\Http\Request;

class __PACKAGE_UC__Controller extends Controller
{
    public function index()
    {
        return view(':package_name::index');
    }

    public function create()
    {
        return view(':package_name::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(__PACKAGE_UC__ $__PACKAGE_LOWER__)
    {
        return view(':package_name::show', compact('__PACKAGE_LOWER__'));
    }

    public function edit(__PACKAGE_UC__ $__PACKAGE_LOWER__)
    {
        return view(':package_name::edit', compact('__PACKAGE_LOWER__'));
    }

    public function update(Request $request, __PACKAGE_UC__ $__PACKAGE_LOWER__)
    {
        //
    }

    public function destroy(__PACKAGE_UC__ $__PACKAGE_LOWER__)
    {
        //
    }
}
