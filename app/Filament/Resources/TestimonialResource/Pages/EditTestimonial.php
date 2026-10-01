<?php

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\TestimonialResource;
use Filament\Resources\Pages\EditRecord;

class EditTestimonial extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = TestimonialResource::class;
}
