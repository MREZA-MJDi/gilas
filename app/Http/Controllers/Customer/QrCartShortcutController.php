<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\TableQrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrCartShortcutController extends Controller
{
    public function __invoke(Request $request, TableQrCodeService $qrCodes): RedirectResponse
    {
        $token = is_string($request->cookie('gilas_table_token'))
            ? trim($request->cookie('gilas_table_token'))
            : '';

        if ($token !== '' && $qrCodes->resolve($token)) {
            return redirect()->to(route('table.menu', ['token' => $token]) . '?openCart=1');
        }

        return redirect()->route('menu.index')
            ->with('status', 'برای ثبت سفارش، QR کد میز را اسکن کن تا سفارش به میز درست متصل شود.');
    }
}
