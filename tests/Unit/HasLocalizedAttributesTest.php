<?php

namespace Tests\Unit;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class HasLocalizedAttributesTest extends TestCase
{
    private function model(array $attributes): Model
    {
        $model = new class extends Model
        {
            use HasLocalizedAttributes;

            /** @var array<string, string|null> */
            protected array $localized = ['name' => '', 'description' => null];
        };
        $model->setRawAttributes($attributes);

        return $model;
    }

    public function test_returns_the_current_locale(): void
    {
        $m = $this->model(['name_ar' => 'رخام', 'name_en' => 'Marble']);

        app()->setLocale('ar');
        $this->assertSame('رخام', $m->name);
        app()->setLocale('en');
        $this->assertSame('Marble', $m->name);
    }

    public function test_falls_back_to_the_other_language_when_empty(): void
    {
        app()->setLocale('ar');
        $this->assertSame('Marble', $this->model(['name_ar' => '', 'name_en' => 'Marble'])->name);
        $this->assertSame('Marble', $this->model(['name_ar' => '   ', 'name_en' => 'Marble'])->name, 'whitespace counts as empty');
        $this->assertSame('Marble', $this->model(['name_ar' => null, 'name_en' => 'Marble'])->name);

        app()->setLocale('en');
        $this->assertSame('رخام', $this->model(['name_ar' => 'رخام', 'name_en' => null])->name);
    }

    public function test_both_empty_returns_the_declared_default(): void
    {
        $m = $this->model(['name_ar' => null, 'name_en' => '', 'description_ar' => null, 'description_en' => null]);

        $this->assertSame('', $m->name);
        $this->assertNull($m->description);
        $this->assertFalse(isset($m->description));
        $this->assertTrue(isset($m->name));
    }

    public function test_other_attributes_are_untouched(): void
    {
        $m = $this->model(['name_ar' => 'رخام', 'name_en' => 'Marble', 'slug' => 'marble']);

        $this->assertSame('marble', $m->slug);
        $this->assertSame('Marble', $m->name_en);
        $this->assertSame(['name', 'description'], $m->localizedFields());
    }
}
