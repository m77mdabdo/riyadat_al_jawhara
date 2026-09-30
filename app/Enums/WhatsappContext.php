<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType as StoneTypeModel;
use Illuminate\Database\Eloquent\Model;

/** Page types a WhatsApp click can be attributed to. */
enum WhatsappContext: string
{
    use EnumHelpers;

    case Service = 'service';
    case StoneType = 'stone_type';
    case Project = 'project';

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::Service => Service::class,
            self::StoneType => StoneTypeModel::class,
            self::Project => Project::class,
        };
    }
}
