<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Support\ImageUpload;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_send_security_headers(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $response->headers->get('Content-Security-Policy');
        foreach (["object-src 'none'", "base-uri 'self'", "form-action 'self'", "frame-ancestors 'self'", 'frame-src https://maps.google.com'] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }
        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_error_pages_and_admin_also_send_headers(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->get('/admin/login')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_hsts_only_on_https_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_session_cookie_is_secure_by_default_in_production_only(): void
    {
        $config = require config_path('session.php');
        $this->assertFalse((bool) $config['secure'], 'testing env: not forced');

        $this->assertStringContainsString("env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')", file_get_contents(config_path('session.php')));
    }

    public function test_admin_upload_rejects_svg(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(CreateProject::class)
            ->fillForm(['title_en' => 'X', 'title_ar' => 'س', 'slug' => 'x', 'cover' => [UploadedFile::fake()->create('evil.svg', 4, 'image/svg+xml')]])
            ->call('create')
            ->assertHasFormErrors(['cover']);

        $this->assertSame(0, Project::count());
    }

    public function test_admin_upload_accepts_webp_and_rejects_oversized(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(CreateProject::class)
            ->fillForm(['title_en' => 'Big', 'title_ar' => 'ك', 'slug' => 'big', 'cover' => [UploadedFile::fake()->image('big.jpg')->size(ImageUpload::MAX_SIZE + 100)]])
            ->call('create')
            ->assertHasFormErrors(['cover']);

        Livewire::test(CreateProject::class)
            ->fillForm(['title_en' => 'Ok', 'title_ar' => 'ت', 'slug' => 'ok', 'cover' => [UploadedFile::fake()->image('ok.webp', 800, 600)]])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNotNull(Project::where('slug', 'ok')->first()->getFirstMedia('cover'));
    }

    public function test_models_refuse_non_image_media_even_outside_the_admin(): void
    {
        $project = Project::create(['title_en' => 'X', 'title_ar' => 'س', 'is_active' => true]);

        $this->expectException(FileUnacceptableForCollection::class);
        $project->addMedia(UploadedFile::fake()->create('evil.svg', 4, 'image/svg+xml'))->toMediaCollection('cover');
    }

    public function test_email_fields_reject_control_characters(): void
    {
        $lead = ['name' => 'X', 'phone' => '0500000000', 'message' => 'm', 'consent' => '1'];

        // The advisory's vectors (fixed in the `email` rule in Laravel 12.60+) plus
        // a NUL byte, which the framework rule still accepts (our not_regex stops it).
        // Embedded ones are rejected; trailing whitespace is removed by TrimStrings
        // first, so either way no control character can ever be stored.
        foreach (["victim@example.test\r\nBcc: spam@example.test", "vic\ttim@example.test", "vic\x00tim@example.test"] as $email) {
            $this->post(route('contact.store'), $lead + ['email' => $email])->assertSessionHasErrors('email');
        }
        foreach (["victim@example.test\n", "victim@example.test\r"] as $email) {
            $this->post(route('contact.store'), $lead + ['email' => $email]);
        }
        $this->assertSame(0, Lead::all()->filter(fn (Lead $l) => preg_match('/[\x00-\x1F\x7F]/', (string) $l->email))->count());

        $post = Post::factory()->create();
        $this->post(route('blog.comments.store', $post), ['name' => 'X', 'body' => 'Hi', 'email' => "a@example.test\nBcc: b@example.test"])
            ->assertSessionHasErrors('email');
    }

    public function test_lead_mail_never_puts_an_invalid_address_in_reply_to(): void
    {
        $lead = Lead::factory()->create(['email' => "evil@example.test\r\nBcc: x@example.test"]);

        $mail = (new NewLeadNotification($lead))->toMail(new AnonymousNotifiable);

        $this->assertSame([], $mail->replyTo);
    }
}
