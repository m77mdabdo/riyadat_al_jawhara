<?php

namespace Tests\Feature\Actions;

use App\Actions\Catalogue\GenerateCatalogue;
use App\Actions\Comments\StoreComment;
use App\Actions\Leads\CreateLead;
use App\Actions\Leads\RecordWhatsappClick;
use App\Contracts\CatalogueGenerator;
use App\Enums\CommentStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\WhatsappContext;
use App\Models\Post;
use App\Models\Setting;
use App\Models\StoneType;
use App\Notifications\NewLeadNotification;
use App\Support\VisitorContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ActionsTest extends TestCase
{
    use RefreshDatabase;

    private function visitor(?string $userAgent = 'UA/1.0'): VisitorContext
    {
        return new VisitorContext('203.0.113.5', $userAgent, 'https://example.test/en/stones', 'en');
    }

    public function test_create_lead_stores_context_and_notifies(): void
    {
        Notification::fake();
        Setting::editable()->update(['email' => 'office@example.test']);

        $lead = app(CreateLead::class)->handle(['name' => 'A', 'phone' => '0500000000', 'message' => 'm', 'consent' => '1'], $this->visitor());

        $this->assertNotNull($lead);
        $this->assertSame(LeadStatus::New->value, $lead->status);
        $this->assertSame(LeadSource::Website->value, $lead->source);
        $this->assertSame('203.0.113.5', $lead->ip_address);
        $this->assertNotNull($lead->consented_at);
        Notification::assertSentOnDemand(NewLeadNotification::class);
    }

    public function test_create_lead_honeypot_stores_nothing(): void
    {
        Notification::fake();

        $this->assertNull(app(CreateLead::class)->handle(['name' => 'Bot', 'phone' => '1', 'message' => 'x', 'website' => 'http://spam'], $this->visitor()));
        $this->assertDatabaseCount('leads', 0);
        Notification::assertNothingSent();
    }

    public function test_store_comment_truncates_long_user_agents_to_the_column_size(): void
    {
        // Regression: comments.user_agent is VARCHAR(255); MySQL strict mode
        // rejected longer values with a 500 on comment submission.
        $post = Post::factory()->create();

        $comment = app(StoreComment::class)->handle($post, ['name' => 'N', 'email' => 'n@example.test', 'body' => 'B'], $this->visitor(str_repeat('Mozilla/5.0 ', 60)));

        $this->assertSame(CommentStatus::Pending->value, $comment->status);
        $this->assertSame(255, mb_strlen($comment->fresh()->user_agent));
    }

    public function test_store_comment_marks_honeypot_as_spam(): void
    {
        $comment = app(StoreComment::class)->handle(Post::factory()->create(), ['name' => 'N', 'email' => 'n@example.test', 'body' => 'B', 'website' => 'x'], $this->visitor());

        $this->assertSame(CommentStatus::Spam->value, $comment->status);
    }

    public function test_whatsapp_click_creates_one_lead_per_session_and_context(): void
    {
        $stone = StoneType::factory()->create();
        $session = new Store('test', new ArraySessionHandler(10));
        $data = ['page' => '/en/stones/'.$stone->slug, 'locale' => 'en', 'context_type' => WhatsappContext::StoneType->value, 'context_id' => $stone->id];

        $first = app(RecordWhatsappClick::class)->handle($data, $session);
        $second = app(RecordWhatsappClick::class)->handle($data, $session);

        $this->assertNotNull($first->lead_id);
        $this->assertSame($first->lead_id, $second->lead_id);
        $this->assertSame($stone->id, $first->lead->stone_type_id);
    }

    public function test_generate_catalogue_uses_the_bound_generator(): void
    {
        $fake = new class implements CatalogueGenerator
        {
            public function catalogue(string $locale): string
            {
                return "/tmp/catalogue-{$locale}.pdf";
            }

            public function specSheet(StoneType $stone, string $locale): string
            {
                return "/tmp/spec-{$stone->id}-{$locale}.pdf";
            }

            public function flush(): void {}
        };
        $this->app->instance(CatalogueGenerator::class, $fake);
        $stone = StoneType::factory()->create(['slug' => 'carrara']);

        $this->assertSame(['path' => '/tmp/catalogue-ar.pdf', 'filename' => 'jra-stone-catalogue-ar.pdf'], app(GenerateCatalogue::class)->handle('ar'));
        $this->assertSame(['path' => "/tmp/spec-{$stone->id}-en.pdf", 'filename' => 'carrara-en.pdf'], app(GenerateCatalogue::class)->handle('en', $stone));
    }
}
