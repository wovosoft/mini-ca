<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DevCertificateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dev/certificate', [DevCertificateController::class, 'getCertificate']);
Route::get('/dev/private-key', [DevCertificateController::class, 'getPrivateKey']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

Route::resource('certificates', CertificateController::class)->middleware(['auth', 'verified']);
Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
Route::get('certificates/{certificate}/download-private-key', [CertificateController::class, 'downloadPrivateKey'])->name('certificates.download-private-key');
