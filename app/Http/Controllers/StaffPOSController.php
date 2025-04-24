<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;

class StaffPOSController extends Controller
{
    public function index()
    {
        $brands = Brand::where('status', 'active')->get();
        return view('staff.content.POSView', compact('brands'));
    }
}
