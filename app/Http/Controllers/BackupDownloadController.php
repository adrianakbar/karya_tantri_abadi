<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BackupDownloadController extends Controller
{
    public function download($fileName, Request $request)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            abort(403, 'Unauthorized access');
        }

        // Cek validasi session dari Livewire component
        $sessionKey = 'backup_download_validated_' . $fileName;
        if (!session($sessionKey)) {
            abort(403, 'Download not validated. Please verify your password first.');
        }

        // Hapus session setelah digunakan untuk security
        session()->forget($sessionKey);

        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
            $backupName = config('backup.backup.name');
            $filePath = $backupName . '/' . $fileName;
            
            if (!$disk->exists($filePath)) {
                abort(404, 'Backup file not found');
            }

            // Log download activity
            Log::info("Backup file downloaded via route by user: " . Auth::user()->email . " - File: " . $fileName);

            // Return download response
            return Response::download(
                $disk->path($filePath),
                $fileName,
                [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                ]
            );

        } catch (\Exception $e) {
            Log::error("Backup download failed: " . $e->getMessage());
            abort(500, 'Download failed: ' . $e->getMessage());
        }
    }
}