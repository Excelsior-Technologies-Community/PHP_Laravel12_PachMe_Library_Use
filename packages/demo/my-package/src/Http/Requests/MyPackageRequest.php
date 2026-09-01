<?php

namespace Demo\MyPackage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class __PACKAGE_UC__Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            //
        ];
    }
}
