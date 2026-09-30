<?php

namespace Tests\Unit;

use App\Services\StockCandidateFinder;
use App\Services\StockMedia\PexelsProvider;
use App\Services\StockMedia\PixabayProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StockMediaProvidersTest extends TestCase
{
    public function test_pixabay_maps_orientation_and_limits_the_query(): void
    {
        config(['services.pixabay.key' => 'k']);
        Http::fake(['pixabay.com/*' => Http::response(['hits' => [['id' => 7, 'largeImageURL' => 'https://cdn.test/a.jpg', 'user' => 'u', 'user_id' => 1]]])]);

        $results = app(PixabayProvider::class)->searchPhotos(str_repeat('marble ', 30), 1, 'landscape');

        $this->assertSame('pixabay-7', $results[0]['id']);
        Http::assertSent(fn (Request $r) => $r['orientation'] === 'horizontal' && mb_strlen($r['q']) <= 95 && $r['per_page'] === 3);
    }

    public function test_unconfigured_providers_make_no_requests(): void
    {
        config(['services.pexels.key' => null, 'services.pixabay.key' => null]);
        Http::fake();

        $this->assertSame([], app(StockCandidateFinder::class)->search('marble'));
        $this->assertSame([], app(PexelsProvider::class)->searchVideoUrls('marble', 1));
        Http::assertNothingSent();
    }

    public function test_candidate_finder_asks_the_primary_provider_first(): void
    {
        config(['services.pexels.key' => 'p', 'services.pixabay.key' => 'x']);
        Http::fake([
            'api.pexels.com/*' => Http::response(['photos' => [['id' => 1, 'src' => ['large2x' => 'https://cdn.test/p.jpg']]]]),
            'pixabay.com/*' => Http::response(['hits' => [['id' => 2, 'largeImageURL' => 'https://cdn.test/x.jpg']]]),
        ]);

        $ids = array_column(app(StockCandidateFinder::class)->search('marble', 5), 'id');

        $this->assertSame(['pexels-1', 'pixabay-2'], $ids);
    }
}
