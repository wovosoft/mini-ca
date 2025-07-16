<?php

namespace App\Http\Controllers;

use App\Models\RootCa;
use App\Services\OpenSslService;
use App\Services\PhpseclibX509Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Random\RandomException;

class RootCaController extends Controller
{
//    private OpenSslService $openSslService;
    private PhpseclibX509Service $openSslService;

    public function __construct()
    {
//        $this->openSslService = new OpenSslService();
        $this->openSslService = new PhpseclibX509Service();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('root-ca/Index', [
            'rootCas' => RootCa::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('root-ca/Create');
    }

    /**
     * Store a newly created resource in storage.
     * @throws RandomException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'domain'      => 'required|string|max:255',
            'description' => 'nullable|string',
            'passphrase'  => 'nullable|string',
        ]);

        $ca = $this
            ->openSslService
            ->generateRootCa(
                $validated['name'],
                $validated['domain'],
                $validated['passphrase']
            );

        RootCa::query()->create([
            'name'        => $validated['name'],
            'domain'      => $validated['domain'],
            'description' => $validated['description'],
            'private_key' => $ca['private_key'],
            'public_key'  => $ca['public_key'],
            'certificate' => $ca['certificate'],
            'passphrase'  => $validated['passphrase'],
        ]);

        return redirect()
            ->route('root-cas.index')
            ->with('success', 'Root CA created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RootCa $rootCa)
    {
        return Inertia::render('root-ca/Show', [
            'rootCa' => $rootCa,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RootCa $rootCa)
    {
        return Inertia::render('root-ca/Edit', [
            'rootCa' => $rootCa,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RootCa $rootCa)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'domain'      => 'required|string|max:255',
            'description' => 'nullable|string',
            'passphrase'  => 'nullable|string',
        ]);

        $rootCa->update($validated);

        return redirect()
            ->route('root-cas.index')
            ->with('success', 'Root CA updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RootCa $rootCa)
    {
        $rootCa->delete();

        return redirect()
            ->route('root-cas.index')
            ->with('success', 'Root CA deleted successfully.');
    }

    public function download(RootCa $rootCa, string $type)
    {
        $validTypes = ['private-key', 'public-key', 'certificate'];

        if (!in_array($type, $validTypes)) {
            abort(404, 'Invalid file type requested.');
        }

        $mimeMap = [
            'private-key' => 'application/x-pem-file',
            'public-key'  => 'application/x-pem-file',
            'certificate' => 'application/x-x509-ca-cert',
        ];

        $headers = [
            'Content-Type'        => $mimeMap[$type],
            'Content-Disposition' => 'attachment; filename="root-ca-' . $type . '.pem"',
        ];

        return response()->streamDownload(function () use ($type, $rootCa) {
            echo match ($type) {
                "private-key" => $rootCa->private_key,
                "public-key"  => $rootCa->public_key,
                "certificate" => $rootCa->certificate,
            };
        }, null, $headers);
    }
}
