<?php

namespace App\Http\Controllers;

use App\Models\StoneType;
use App\Services\CataloguePdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogueController extends Controller
{
    public function catalogue(CataloguePdf $pdf): BinaryFileResponse
    {
        $locale = app()->getLocale();

        return response()->file($pdf->catalogue($locale), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="jra-stone-catalogue-'.$locale.'.pdf"',
        ]);
    }

    public function specSheet(CataloguePdf $pdf, StoneType $stoneType): BinaryFileResponse
    {
        abort_unless($stoneType->is_active, 404);
        $locale = app()->getLocale();

        return response()->file($pdf->specSheet($stoneType, $locale), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$stoneType->slug.'-'.$locale.'.pdf"',
        ]);
    }
}
