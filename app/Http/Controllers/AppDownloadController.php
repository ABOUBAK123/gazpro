<?php

namespace App\Http\Controllers;

use App\Models\Store;

class AppDownloadController extends Controller
{
    public function show(Store $store)
    {
        abort_unless($store->status === 'active', 404);

        return view('public.app-download', [
            'store'          => $store,
            'clipboardToken' => "GAZPRO_STORE:{$store->id}",
            'apkUrl'         => asset('downloads/GazProClient.apk'),
        ]);
    }
}
