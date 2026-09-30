<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\FollowUpDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

/**
 * Daily follow-up digest (scheduled in routes/console.php). Each assignee gets
 * their own due leads; unassigned due leads go to every admin. Nothing is sent
 * when nothing is due.
 */
class SendFollowUpDigest extends Command
{
    protected $signature = 'leads:follow-up-digest';

    protected $description = 'Email and notify staff about leads whose follow-up date has come';

    public function handle(): int
    {
        $due = Lead::followUpDue()->with('assignee.roles')->orderBy('follow_up_at')->get();

        if ($due->isEmpty()) {
            $this->line('No follow-ups due.');

            return self::SUCCESS;
        }

        $admins = User::query()->whereHas('roles', fn ($q) => $q->where('name', UserRole::Admin->value))->get();

        /** @var array<int, array{user: User, leads: list<Lead>}> $byRecipient */
        $byRecipient = [];
        foreach ($due as $lead) {
            $assignee = $lead->assignee;
            $recipients = $assignee && $assignee->hasAnyRole(UserRole::values()) ? [$assignee] : $admins->all();

            foreach ($recipients as $user) {
                $byRecipient[$user->id]['user'] = $user;
                $byRecipient[$user->id]['leads'][] = $lead;
            }
        }

        foreach ($byRecipient as ['user' => $user, 'leads' => $leads]) {
            $user->notify(new FollowUpDigestNotification(new Collection($leads)));
            $this->line(sprintf('%s: %d lead(s)', $user->email, count($leads)));
        }

        return self::SUCCESS;
    }
}
