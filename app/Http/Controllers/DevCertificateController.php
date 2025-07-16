<?php

namespace App\Http\Controllers;

use App\Models\RootCa;
use Illuminate\Http\Request;

class DevCertificateController extends Controller
{
    public function getCertificate(Request $request)
    {
        $rootCa = RootCa::query()->latest()->first();

        if (!$rootCa) {
            return response()->json(['error' => 'No root CA found.'], 404);
        }

        return response($rootCa->certificate, 200, ['Content-Type' => 'application/x-x509-ca-cert']);
    }

    public function getPrivateKey(Request $request)
    {
        $rootCa = RootCa::query()->latest()->first();

        if (!$rootCa) {
            return response()->json(['error' => 'No root CA found.'], 404);
        }

        return response($rootCa->private_key, 200, ['Content-Type' => 'application/x-pem-file']);
    }
}
