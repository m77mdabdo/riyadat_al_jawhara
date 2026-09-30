<?php

namespace App\Http\Controllers;

use App\Actions\Catalogue\GenerateCatalogue;
use App\Models\StoneType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogueController extends Controller
{
    public function catalogue(GenerateCatalogue $generate): BinaryFileResponse
    {
        return $this->pdf($generate->handle(app()->getLocale()));
    }

    public function specSheet(GenerateCatalogue $generate, StoneType $stoneType): BinaryFileResponse
    {
        abort_unless($stoneType->is_active, 404);

        return $this->pdf($generate->handle(app()->getLocale(), $stoneType));
    }

    /**
     * @param  array{path: string, filename: string}  $file
     */
    private function pdf(array $file): BinaryFileResponse
    {
        return response()->file($file['path'], [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$file['filename'].'"',
        ]);
    }
}
