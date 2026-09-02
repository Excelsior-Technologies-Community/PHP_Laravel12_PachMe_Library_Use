<?php

namespace Vendor\Package1\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class __PACKAGE_UC__Resource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
        ];
    }
}
