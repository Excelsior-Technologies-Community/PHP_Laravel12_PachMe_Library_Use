<?php

namespace :VendorName\:PackageName\Http\Requests;

use :VendorName\:PackageName\Http\Requests\__PACKAGE_UC__Request;

class Store__PACKAGE_UC__Request extends __PACKAGE_UC__Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
