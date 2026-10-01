<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\TeamMemberResource;
use Filament\Resources\Pages\EditRecord;

class EditTeamMember extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = TeamMemberResource::class;
}
