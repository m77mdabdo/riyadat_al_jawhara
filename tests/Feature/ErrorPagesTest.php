<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function codes(): array
    {
        return [['403'], ['404'], ['419'], ['429'], ['500'], ['503']];
    }

    #[DataProvider('codes')]
    public function test_every_error_page_is_branded_in_both_languages(string $code): void
    {
        foreach (['en', 'ar'] as $locale) {
            app()->setLocale($locale);
            $html = view("errors.{$code}")->render();

            $this->assertStringContainsString(e(__("site.errors.{$code}_heading")), $html);
            $this->assertStringContainsString('<meta name="robots" content="noindex">', $html);
            $this->assertStringContainsString('dir="'.($locale === 'ar' ? 'rtl' : 'ltr').'"', $html);
        }
    }

    public function test_comment_throttle_shows_the_branded_429_page(): void
    {
        $post = Post::factory()->create();
        $data = ['name' => 'R', 'email' => 'r@example.test', 'body' => 'Hi'];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('blog.comments.store', $post), $data);
        }

        $this->post(route('blog.comments.store', $post), $data)
            ->assertStatus(429)
            ->assertSee(__('site.errors.429_heading'));
    }

    public function test_admin_without_role_gets_the_branded_403_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden()
            ->assertSee(__('site.errors.403_heading'));
    }
}
