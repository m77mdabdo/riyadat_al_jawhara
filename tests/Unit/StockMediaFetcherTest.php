<?php

namespace Tests\Unit;

use App\Services\StockMediaFetcher;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StockMediaFetcherTest extends TestCase
{
    public function test_it_reads_the_api_key_from_config_not_env(): void
    {
        config(['services.pexels.key' => 'config-key']);
        Http::fake(['api.pexels.com/*' => Http::response(['photos' => []])]);

        $this->assertSame([], app(StockMediaFetcher::class)->fetchPhotos('marble'));

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'config-key'));
    }

    public function test_it_makes_no_request_without_a_key(): void
    {
        config(['services.pexels.key' => null]);
        Http::fake();

        $this->assertSame([], app(StockMediaFetcher::class)->fetchPhotos('marble'));

        Http::assertNothingSent();
    }
}
