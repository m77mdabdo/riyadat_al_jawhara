<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::published()->orderBy('published_at')->get()->values();

        if ($posts->isEmpty()) {
            return;
        }

        $commentSets = [
            [
                'post' => $posts->get(0),
                'comments' => [
                    ['name' => 'Faisal Al-Otaibi', 'email' => 'faisal.o@example.com', 'body' => 'Really helpful guide, thank you! This answered a question I had for weeks.', 'status' => 'approved'],
                    ['name' => 'Sara Al-Ghamdi', 'email' => 'sara.g@example.com', 'body' => 'Do you also offer maintenance packages after installation?', 'status' => 'approved',
                        'replies' => [
                            ['name' => 'JRA Team', 'email' => 'info@jra-stone.sa', 'body' => 'Yes, we offer ongoing maintenance packages — feel free to reach out via the contact page for details.', 'status' => 'approved'],
                        ],
                    ],
                    ['name' => 'Anonymous', 'email' => 'test@example.com', 'body' => 'Check out my site for cheap watches!! www.spam-example.com', 'status' => 'spam'],
                ],
            ],
            [
                'post' => $posts->get(1) ?? $posts->get(0),
                'comments' => [
                    ['name' => 'Mohammed Al-Harbi', 'email' => 'm.harbi@example.com', 'body' => 'Loved seeing the Hashemi stone mentioned — it deserves more attention.', 'status' => 'approved'],
                    ['name' => 'Layla Hassan', 'email' => 'layla.h@example.com', 'body' => 'Waiting for approval on this one, just submitted my thoughts.', 'status' => 'pending'],
                ],
            ],
            [
                'post' => $posts->get(2) ?? $posts->get(0),
                'comments' => [
                    ['name' => 'Abdullah Al-Qahtani', 'email' => 'a.qahtani@example.com', 'body' => 'My marble floor has exactly signs #2 and #4 from this list. Booking a consultation now.', 'status' => 'approved'],
                    ['name' => 'Noura Al-Dosari', 'email' => 'noura.d@example.com', 'body' => 'Great breakdown, very practical tips.', 'status' => 'approved'],
                    ['name' => 'Khalid Ibrahim', 'email' => 'khalid.i@example.com', 'body' => 'Still deciding, will comment again once I speak with the team.', 'status' => 'pending'],
                ],
            ],
            [
                'post' => $posts->get(3) ?? $posts->get(0),
                'comments' => [
                    ['name' => 'Reem Al-Shehri', 'email' => 'reem.s@example.com', 'body' => 'We went with quartz for exactly the reasons in this article — no regrets.', 'status' => 'approved'],
                    ['name' => 'Yousef Al-Amri', 'email' => 'yousef.a@example.com', 'body' => 'Granite still wins for heat resistance in my opinion, but great comparison either way.', 'status' => 'approved'],
                    ['name' => 'Fatima Al-Anazi', 'email' => 'fatima.a@example.com', 'body' => 'Would you recommend quartz for a bathroom vanity too?', 'status' => 'approved'],
                ],
            ],
            [
                'post' => $posts->get(4) ?? $posts->get(0),
                'comments' => [
                    ['name' => 'Hind Al-Zahrani', 'email' => 'hind.z@example.com', 'body' => 'Beautiful work! Would love to see more before/after shots from this project.', 'status' => 'approved'],
                    ['name' => 'Omar Al-Faraj', 'email' => 'omar.f@example.com', 'body' => 'What stone type was used in the entryway?', 'status' => 'approved',
                        'replies' => [
                            ['name' => 'JRA Team', 'email' => 'info@jra-stone.sa', 'body' => 'The entryway features Carrara marble flooring with a honed finish.', 'status' => 'approved'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($commentSets as $set) {
            /** @var Post|null $post */
            $post = $set['post'];

            if (! $post) {
                continue;
            }

            foreach ($set['comments'] as $comment) {
                $replies = $comment['replies'] ?? [];
                unset($comment['replies']);

                $record = Comment::updateOrCreate(
                    ['post_id' => $post->id, 'email' => $comment['email'], 'body' => $comment['body']],
                    $comment + [
                        'post_id' => $post->id,
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'Mozilla/5.0 (Seeder)',
                    ]
                );

                foreach ($replies as $reply) {
                    Comment::updateOrCreate(
                        ['post_id' => $post->id, 'parent_id' => $record->id, 'email' => $reply['email']],
                        $reply + [
                            'post_id' => $post->id,
                            'parent_id' => $record->id,
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Mozilla/5.0 (Seeder)',
                        ]
                    );
                }
            }
        }
    }
}
