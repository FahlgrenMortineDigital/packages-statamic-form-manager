<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}