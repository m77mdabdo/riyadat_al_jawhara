<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ActivityResource;
use App\Models\Comment;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function user(string $role): User
    {
        return tap(User::factory()->create())->assignRole($role);
    }

    public function test_content_changes_are_logged_with_the_causer_and_changed_fields_only(): void
    {
        $editor = $this->user('editor');
        $this->actingAs($editor);

        $project = Project::factory()->create(['title_en' => 'Old title']);
        $project->update(['title_en' => 'New title']);
        $project->delete();

        $events = Activity::where('subject_type', Project::class)->orderBy('id')->get();
        $this->assertSame(['created', 'updated', 'deleted'], $events->pluck('event')->all());
        $this->assertTrue($events->every(fn ($a) => $a->causer_id === $editor->id));

        $update = $events[1];
        // Only what changed: the title and the slug regenerated from it.
        $this->assertSame(['slug', 'title_en'], collect($update->properties['attributes'])->keys()->sort()->values()->all());
        $this->assertSame('New title', $update->properties['attributes']['title_en']);
        $this->assertSame('Old title', $update->properties['old']['title_en']);
    }

    public function test_settings_changes_are_logged(): void
    {
        $this->actingAs($this->user('admin'));

        Setting::editable()->update(['phone' => '+966500000001']);

        $activity = Activity::where('subject_type', Setting::class)->latest('id')->first();
        $this->assertNotNull($activity);
        $this->assertSame('+966500000001', $activity->properties['attributes']['phone']);
    }

    public function test_leads_log_only_workflow_fields_and_not_public_submissions(): void
    {
        $lead = Lead::factory()->create(['ip_address' => '203.0.113.9', 'user_agent' => 'UA']);
        $this->assertSame(0, Activity::where('subject_type', Lead::class)->count(), 'public submissions are not audited');

        $this->actingAs($this->user('admin'));
        $lead->update(['status' => 'contacted', 'message' => 'edited', 'ip_address' => '198.51.100.1']);

        $activity = Activity::where('subject_type', Lead::class)->sole();
        $this->assertSame(['status' => 'contacted'], $activity->properties['attributes']);
        $this->assertStringNotContainsString('203.0.113.9', json_encode($activity->properties));
    }

    public function test_comment_moderation_is_logged_but_not_the_visitor_data(): void
    {
        $comment = Comment::factory()->pending()->create(['ip_address' => '203.0.113.9']);
        $this->assertSame(0, Activity::where('subject_type', Comment::class)->count());

        $comment->update(['status' => 'approved']);

        $activity = Activity::where('subject_type', Comment::class)->sole();
        $this->assertSame(['status' => 'approved'], $activity->properties['attributes']);
        $this->assertStringNotContainsString('203.0.113.9', json_encode($activity->properties));
    }

    public function test_public_view_counter_does_not_create_log_entries(): void
    {
        $post = Post::factory()->create();
        $before = Activity::count();

        $post->increment('views_count');

        $this->assertSame($before, Activity::count());
    }

    public function test_user_passwords_are_never_logged(): void
    {
        $user = User::factory()->create();
        $user->update(['password' => 'a-new-password-123']);

        $this->assertStringNotContainsString('password', Activity::all()->toJson());
    }

    public function test_viewer_is_admin_only_and_read_only(): void
    {
        $this->actingAs($this->user('admin'));
        $project = Project::factory()->create();
        $activity = Activity::latest('id')->first();

        $this->get('/admin/activities')->assertOk()->assertSee('Project #'.$project->id);
        $this->get("/admin/activities/{$activity->id}")->assertOk()->assertSee('title_en');
        $this->assertFalse(ActivityResource::canCreate());
        $this->assertFalse(ActivityResource::canEdit($activity));
        $this->assertFalse(ActivityResource::canDelete($activity));

        $this->actingAs($this->user('editor'));
        $this->get('/admin/activities')->assertForbidden();
        $this->get("/admin/activities/{$activity->id}")->assertForbidden();
    }

    public function test_old_entries_are_cleaned_weekly(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'activitylog:clean'));

        $this->assertCount(1, $events);
    }
}
