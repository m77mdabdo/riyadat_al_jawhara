<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichTextSanitizationTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = '<p>Safe <strong>text</strong></p>'
        .'<script>alert("xss")</script>'
        .'<img src="x" onerror="alert(1)">'
        .'<a href="javascript:alert(2)">bad link</a>'
        .'<a href="https://example.com">good link</a>';

    private function assertSanitized(string $html): void
    {
        $this->assertStringContainsString('<strong>text</strong>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringNotContainsString('<script>alert("xss")', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('javascript:alert(2)', $html);
    }

    public function test_privacy_policy_html_is_sanitized(): void
    {
        Setting::current()->update(['privacy_policy_en' => self::PAYLOAD]);

        $this->assertSanitized($this->get(route('legal.privacy'))->assertOk()->getContent());
    }

    public function test_terms_html_is_sanitized(): void
    {
        Setting::current()->update(['terms_en' => self::PAYLOAD]);

        $this->assertSanitized($this->get(route('legal.terms'))->assertOk()->getContent());
    }

    public function test_blog_body_html_is_sanitized(): void
    {
        $post = Post::create([
            'title_en' => 'Post',
            'title_ar' => 'مقال',
            'body_en' => self::PAYLOAD,
            'body_ar' => self::PAYLOAD,
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        $this->assertSanitized($this->get(route('blog.show', $post))->assertOk()->getContent());
    }
}
