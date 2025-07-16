<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\RootCa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Services\OpenSslService;

class CertificateController extends Controller
{
    private OpenSslService $openSslService;

    public function __construct()
    {
        $this->openSslService = new OpenSslService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Certificates/Index', [
            'certificates' => Certificate::query()
                ->with('rootCa')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Certificates/Create', [
            'rootCas' => RootCa::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'root_ca_id' => 'required|exists:root_cas,id',
            'name'       => 'required|string|max:255',
            'domain'     => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-zA-Z0-9.-]+|\d{1,3}(\.\d{1,3}){3})$/'
            ],
            'passphrase' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $rootCa = RootCa::findOrFail($validated['root_ca_id']);

        $cert = $this
            ->openSslService
            ->generateCertificate(
                $validated['name'],
                $validated['domain'],
                $rootCa->private_key,
                $rootCa->certificate,
                $rootCa->passphrase,      // rootCaPassphrase
                $validated['passphrase'], // certificatePassphrase
                $validated['expires_at'] ? now()->diffInDays($validated['expires_at']) : 365
            );

        Certificate::create(array_merge($validated, [
            'private_key' => $cert['private_key'],
            'public_key'  => $cert['public_key'],
            'certificate' => $cert['certificate'],
            'valid_from'  => now(),
        ]));

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Certificate issued successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate)
    {
        $certificate->load('rootCa');
        $certificate->private_key = (string)$certificate->private_key;

        return Inertia::render('Certificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate)
    {
        return Inertia::render('Certificates/Edit', [
            'certificate' => $certificate->load('rootCa'),
            'rootCas'     => RootCa::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'root_ca_id' => 'required|exists:root_cas,id',
            'name'       => 'required|string|max:255',
            'domain'     => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-zA-Z0-9.-]+|\d{1,3}(\.\d{1,3}){3})$/'
            ],
            'passphrase' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $certificate->update($validated);

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Certificate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    public function download(Certificate $certificate)
    {
        $headers = [
            'Content-Type'        => 'application/x-x509-ca-cert',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($certificate->name) . '_cert.crt"',
        ];

        return response()->streamDownload(function () use ($certificate) {
            echo $certificate->certificate;
        }, null, $headers);
    }

    public function downloadPrivateKey(Certificate $certificate)
    {
        $headers = [
            'Content-Type'        => 'application/x-pem-file',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($certificate->name) . '_key.pem"',
        ];

        return response()->streamDownload(function () use ($certificate) {
            echo $certificate->private_key;
        }, null, $headers);
    }
}
