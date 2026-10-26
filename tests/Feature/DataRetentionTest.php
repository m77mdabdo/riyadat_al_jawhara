<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Lead;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataRetentionTest extends TestCase
{
    use RefreshDatabase;

    private function records(int $daysAgo): array
    {
        $at = now()->subDays($daysAgo);
        $personal = ['ip_address' => '203.0.113.7', 'user_agent' => 'Mozilla/5.0 Test'];

        $lead = Lead::factory()->create($personal);
        $comment = Comment::factory()->create($personal);
        Lead::whereKey($lead->id)->update(['created_at' => $at]);
        Comment::whereKey($comment->id)->update(['created_at' => $at]);

        return [$lead, $comment];
    }

    public function test_clears_ip_and_user_agent_older_than_the_window_and_keeps_rows(): void
    {
        [$oldLead, $oldComment] = $this->records(200);
        [$newLead, $newComment] = $this->records(10);

        $this->artisan('privacy:prune')->assertSuccessful();

        foreach ([$oldLead, $oldComment] as $model) {
            $fresh = $model->fresh();
            $this->assertNotNull($fresh, 'row must be kept');
            $this->assertNull($fresh->ip_address);
            $this->assertNull($fresh->user_agent);
        }
        foreach ([$newLead, $newComment] as $model) {
            $this->assertSame('203.0.113.7', $model->fresh()->ip_address);
            $this->assertSame('Mozilla/5.0 Test', $model->fresh()->user_agent);
        }
        $this->assertSame($oldLead->name, $oldLead->fresh()->name);
    }

    public function test_window_is_configurable(): void
    {
        [$lead] = $this->records(40);

        config(['privacy.retention_days' => 30]);
        $this->artisan('privacy:prune')->assertSuccessful();

        $this->assertNull($lead->fresh()->ip_address);
    }

    public function test_dry_run_changes_nothing(): void
    {
        [$lead, $comment] = $this->records(365);

        $this->artisan('privacy:prune', ['--dry-run' => true])
            ->expectsOutput('comments: 1 would be cleared')
            ->expectsOutput('leads: 1 would be cleared')
            ->assertSuccessful();

        $this->assertSame('203.0.113.7', $lead->fresh()->ip_address);
        $this->assertSame('203.0.113.7', $comment->fresh()->ip_address);
    }

    public function test_rejects_a_zero_window(): void
    {
        $this->records(1);

        $this->artisan('privacy:prune', ['--days' => 0])->assertFailed();
        $this->assertSame(2, Lead::whereNotNull('ip_address')->count() + Comment::whereNotNull('ip_address')->count());
    }

    public function test_is_scheduled_daily(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'privacy:prune'));

        $this->assertCount(1, $events);
        $this->assertSame('15 3 * * *', $events->first()->expression);
    }
}
