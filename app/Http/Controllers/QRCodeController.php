<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function showForm()
    {
        return view('qr-code');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'number' => 'required|numeric'
        ]);

        $qrCode = QrCode::size(200)->generate($request->number);
        
        return back()->with('qr', $qrCode);
    }
}