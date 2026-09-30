<?php

namespace App\Http\Controllers;

use App\Actions\Leads\RecordWhatsappClick;
use App\Enums\WhatsappContext;
use App\Support\LocalizedUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Beacon endpoint hit (navigator.sendBeacon) when a visitor clicks any
 * WhatsApp button. See RecordWhatsappClick for what is stored.
 */
class WhatsappClickController extends Controller
{
    public function __invoke(Request $request, RecordWhatsappClick $record): Response
    {
        $data = $request->validate([
            'page' => ['nullable', 'string', 'max:2048'],
            'locale' => ['nullable', Rule::in(LocalizedUrl::supported())],
            'context_type' => ['nullable', Rule::in(WhatsappContext::values())],
            'context_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $record->handle($data, $request->session());

        return response()->noContent();
    }
}
