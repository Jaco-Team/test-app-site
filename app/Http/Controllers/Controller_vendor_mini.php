<?php

namespace App\Http\Controllers;

use App\Models\Model_vendor_mini;
use Illuminate\Http\Request;
use App\Http\Resources\GlobalResource;


class Controller_vendor_mini extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      return new GlobalResource([
        'module_info' => $request->module_info,
        'vendors' => Model_vendor_mini::get_vendors(0),
      ]);
    }

    public function get_vendor_info(Request $request): GlobalResource
    {
      return new GlobalResource([
        'vendor' => Model_vendor_mini::get_vendor($request->data['vendor_id']),
        'mails' => Model_vendor_mini::get_mails($request->data['vendor_id']),
        'items' => Model_vendor_mini::get_items($request->data['vendor_id']),
      ]);
    }
}
