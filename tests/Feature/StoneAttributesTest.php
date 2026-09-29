<?php

namespace Tests\Feature;

use App\Filament\Resources\StoneAttributeResource\Pages\CreateStoneAttribute;
use App\Filament\Resources\StoneTypeResource\Pages\CreateStoneType;
use App\Filament\Resources\StoneTypeResource\Pages\EditStoneType;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\StoneAttributeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoneAttributesTest extends TestCase
{
    use RefreshDatabase;

    private StoneCategory $marble;

    private StoneCategory $granite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marble = StoneCategory::create(['name_en' => 'Marble', 'name_ar' => 'رخام']);
        $this->granite = StoneCategory::create(['name_en' => 'Granite', 'name_ar' => 'جرانيت']);
    }

    private function stone(string $name, StoneCategory $category, array $attributes = [], ?string $origin = null, bool $active = true): StoneType
    {
        $stone = StoneType::create([
            'stone_category_id' => $category->id,
            'name_en' => $name,
            'name_ar' => $name.' ع',
            'origin_type' => $origin,
            'is_active' => $active,
        ]);
        $stone->stoneAttributes()->attach(collect($attributes)->pluck('id'));

        return $stone;
    }

    private function attr(string $type, string $name, array $extra = []): StoneAttribute
    {
        return StoneAttribute::create($extra + ['type' => $type, 'name_en' => $name, 'name_ar' => $name.' ع']);
    }

    public function test_detail_page_shows_grouped_active_specifications(): void
    {
        $polished = $this->attr(StoneAttribute::FINISH, 'Polished');
        $white = $this->attr(StoneAttribute::COLOUR, 'White', ['hex_color' => '#F2F0EB']);
        $hidden = $this->attr(StoneAttribute::APPLICATION, 'Secret Use', ['is_active' => false]);
        $stone = $this->stone('Carrara', $this->marble, [$polished, $white, $hidden], StoneType::ORIGIN_IMPORTED);

        $this->get(route('stones.show', $stone))
            ->assertOk()
            ->assertSee(__('site.stones.specifications'))
            ->assertSee('Polished')
            ->assertSee('White')
            ->assertSee('background-color: #F2F0EB', false)
            ->assertSee(__('site.common.imported'))
            ->assertDontSee('Secret Use');
    }

    public function test_detail_page_in_arabic_uses_arabic_names(): void
    {
        $stone = $this->stone('Carrara', $this->marble, [$this->attr(StoneAttribute::FINISH, 'Honed')]);

        $this->get('/lang/ar');
        $this->get(route('stones.show', $stone))->assertSee('Honed ع')->assertSee('التشطيب');
    }

    public function test_invalid_swatch_is_never_written_to_style(): void
    {
        $bad = $this->attr(StoneAttribute::COLOUR, 'Weird', ['hex_color' => 'red;background:url(x)']);
        $stone = $this->stone('Carrara', $this->marble, [$bad]);

        $this->get(route('stones.show', $stone))->assertOk()->assertSee('Weird')->assertDontSee('url(x)', false);
    }

    public function test_stone_without_specs_shows_no_spec_block(): void
    {
        $stone = $this->stone('Plain', $this->marble);

        $this->get(route('stones.show', $stone))->assertOk()->assertDontSee(__('site.stones.specifications'));
    }

    public function test_index_filters_by_attributes_origin_and_category(): void
    {
        $polished = $this->attr(StoneAttribute::FINISH, 'Polished');
        $flamed = $this->attr(StoneAttribute::FINISH, 'Flamed');
        $white = $this->attr(StoneAttribute::COLOUR, 'White');
        $exterior = $this->attr(StoneAttribute::APPLICATION, 'Exterior');

        $this->stone('Carrara', $this->marble, [$polished, $white], StoneType::ORIGIN_IMPORTED);
        $this->stone('Black Granite', $this->granite, [$polished, $flamed, $exterior], StoneType::ORIGIN_LOCAL);
        $this->stone('Hidden', $this->marble, [$polished], active: false);

        $names = fn (array $query) => $this->get(route('stones.index', $query))->assertOk()->viewData('stoneTypes')->pluck('name_en')->all();

        $this->assertEqualsCanonicalizing(['Carrara', 'Black Granite'], $names(['finish' => 'polished']));
        $this->assertSame(['Black Granite'], $names(['finish' => 'flamed']));
        $this->assertSame(['Carrara'], $names(['colour' => 'white']));
        $this->assertSame(['Black Granite'], $names(['application' => 'exterior']));
        $this->assertSame(['Black Granite'], $names(['origin' => 'local']));
        $this->assertSame(['Carrara'], $names(['finish' => 'polished', 'stone_category' => $this->marble->slug]));
        $this->assertSame([], $names(['finish' => 'flamed', 'colour' => 'white']));
        $this->assertEqualsCanonicalizing(['Carrara', 'Black Granite'], $names(['origin' => 'mars']));
    }

    public function test_inactive_attribute_cannot_be_used_as_filter(): void
    {
        $retired = $this->attr(StoneAttribute::FINISH, 'Retired', ['is_active' => false]);
        $this->stone('Carrara', $this->marble, [$retired]);

        $this->get(route('stones.index', ['finish' => 'retired']))->assertOk()->assertViewHas('stoneTypes', fn ($stones) => $stones->isEmpty());
    }

    public function test_filter_options_only_list_values_used_by_visible_stones(): void
    {
        $used = $this->attr(StoneAttribute::FINISH, 'Polished');
        $unused = $this->attr(StoneAttribute::FINISH, 'Leathered');
        $onlyHidden = $this->attr(StoneAttribute::FINISH, 'Bush Hammered');
        $this->stone('Carrara', $this->marble, [$used]);
        $this->stone('Hidden', $this->marble, [$onlyHidden], active: false);

        $this->get(route('stones.index'))
            ->assertOk()
            ->assertSee('<option value="polished"', false)
            ->assertDontSee('<option value="leathered"', false)
            ->assertDontSee('<option value="bush-hammered"', false);
    }

    public function test_index_without_any_attributes_shows_no_filter_form(): void
    {
        $this->stone('Carrara', $this->marble);

        $this->get(route('stones.index'))->assertOk()->assertDontSee('id="filter-finish"', false);
    }

    public function test_admin_can_set_specs_and_editing_one_type_keeps_the_others(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        $polished = $this->attr(StoneAttribute::FINISH, 'Polished');
        $honed = $this->attr(StoneAttribute::FINISH, 'Honed');
        $white = $this->attr(StoneAttribute::COLOUR, 'White');
        $interior = $this->attr(StoneAttribute::APPLICATION, 'Interior');

        Livewire::test(CreateStoneType::class)
            ->fillForm([
                'stone_category_id' => $this->marble->id,
                'name_en' => 'Carrara',
                'name_ar' => 'كرارا',
                'slug' => 'carrara',
                'origin_type' => StoneType::ORIGIN_IMPORTED,
                'finishes' => [$polished->id],
                'colours' => [$white->id],
                'applications' => [$interior->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $stone = StoneType::where('slug', 'carrara')->firstOrFail();
        $this->assertEqualsCanonicalizing([$polished->id, $white->id, $interior->id], $stone->stoneAttributes()->pluck('stone_attributes.id')->all());
        $this->assertSame(StoneType::ORIGIN_IMPORTED, $stone->origin_type);

        Livewire::test(EditStoneType::class, ['record' => $stone->getRouteKey()])
            ->assertFormSet(['finishes' => [(string) $polished->id], 'colours' => [(string) $white->id]])
            ->fillForm(['finishes' => [$honed->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing([$honed->id, $white->id, $interior->id], $stone->fresh()->stoneAttributes()->pluck('stone_attributes.id')->all());
    }

    public function test_admin_rejects_unknown_origin_type(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(CreateStoneType::class)
            ->fillForm(['stone_category_id' => $this->marble->id, 'name_en' => 'X', 'name_ar' => 'س', 'slug' => 'x', 'origin_type' => 'mars'])
            ->call('create')
            ->assertHasFormErrors(['origin_type']);
    }

    public function test_attribute_slugs_are_unique_per_type_only(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));
        $this->attr(StoneAttribute::COLOUR, 'Natural');

        Livewire::test(CreateStoneAttribute::class)
            ->fillForm(['type' => StoneAttribute::FINISH, 'name_en' => 'Natural', 'name_ar' => 'طبيعي', 'slug' => 'natural'])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateStoneAttribute::class)
            ->fillForm(['type' => StoneAttribute::COLOUR, 'name_en' => 'Natural 2', 'name_ar' => 'طبيعي', 'slug' => 'natural'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        $this->assertSame(2, StoneAttribute::where('slug', 'natural')->count());
    }

    public function test_demo_seeder_creates_the_standard_options(): void
    {
        $this->seed(DemoSeeder::class);

        foreach (StoneAttributeSeeder::OPTIONS as $type => $options) {
            $this->assertSame(count($options), StoneAttribute::ofType($type)->count(), $type);
        }
        $this->assertGreaterThan(0, StoneType::whereHas('stoneAttributes')->count());
        $this->get(route('stones.index'))->assertOk()->assertSee('id="filter-finish"', false);
    }
}
