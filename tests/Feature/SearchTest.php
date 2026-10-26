<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private function titles(string $q): array
    {
        return collect($this->get(route('search', ['q' => $q]))->assertOk()->viewData('results')->items())->pluck('title')->all();
    }

    public function test_searches_every_content_type_in_both_languages(): void
    {
        Service::factory()->create(['name_en' => 'Marble Installation', 'name_ar' => 'تركيب الرخام']);
        StoneType::factory()->create(['name_en' => 'Carrara', 'description_en' => 'Italian marble', 'name_ar' => 'كرارا']);
        Project::factory()->create(['title_en' => 'Villa Lobby', 'description_en' => 'Book-matched marble walls', 'title_ar' => 'ردهة فيلا']);
        Post::factory()->create(['title_en' => 'Caring for marble', 'title_ar' => 'العناية بالرخام']);
        Faq::factory()->create(['question_en' => 'Do you polish marble?', 'question_ar' => 'هل تلمعون الرخام؟']);

        $this->assertCount(5, $this->titles('marble'));

        $this->useLocale('ar');
        $this->assertCount(3, $this->titles('الرخام'));
    }

    public function test_hidden_content_is_never_returned(): void
    {
        Service::factory()->inactive()->create(['name_en' => 'Secret Marble Service']);
        Project::factory()->inactive()->create(['title_en' => 'Secret Marble Project']);
        Post::factory()->draft()->create(['title_en' => 'Secret Marble Draft']);
        Post::factory()->scheduled()->create(['title_en' => 'Secret Marble Future']);
        Faq::factory()->inactive()->create(['question_en' => 'Secret marble?']);

        $this->assertSame([], $this->titles('Secret'));
    }

    public function test_wildcards_are_escaped_and_input_is_bound(): void
    {
        Service::factory()->create(['name_en' => 'Stone Supply']);

        $this->assertSame([], $this->titles('%'));
        $this->assertSame([], $this->titles('__'));
        $this->assertSame([], $this->titles("' OR 1=1 --"));
        $this->assertSame(['Stone Supply'], $this->titles('Stone'));
    }

    public function test_short_or_empty_queries_show_guidance(): void
    {
        $this->get(route('search'))->assertOk()->assertSee(__('site.search.hint'));
        $this->get(route('search', ['q' => 'a']))->assertOk()->assertSee(__('site.search.too_short'));
    }

    public function test_results_are_paginated_noindex_and_link_to_the_content(): void
    {
        Service::factory()->count(15)->create(['description_en' => 'granite work']);
        $faq = Faq::factory()->create(['question_en' => 'Granite care?']);

        $response = $this->get(route('search', ['q' => 'granite']));
        $response->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('16 results for “granite”', false);
        $this->assertCount(12, $response->viewData('results')->items());
        $this->assertCount(4, $this->get(route('search', ['q' => 'granite', 'page' => 2]))->viewData('results')->items());

        $this->get(route('search', ['q' => 'Granite care']))->assertSee(route('faq').'#faq-'.$faq->id, false);
        $this->get(route('faq'))->assertSee('id="faq-'.$faq->id.'"', false);
    }

    public function test_header_has_a_search_link_and_search_is_not_in_the_sitemap(): void
    {
        $this->get(route('home'))->assertSee('href="'.route('search').'"', false);
        $this->get('/sitemap.xml')->assertDontSee('/search', false);
    }
}
