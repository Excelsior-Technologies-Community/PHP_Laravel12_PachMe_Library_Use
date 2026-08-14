<?php

namespace :VendorName\:PackageName\Http\Requests;

use :VendorName\:PackageName\Http\Requests\__PACKAGE_UC__Request;

class Update__PACKAGE_UC__Request extends __PACKAGE_UC__Request
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
