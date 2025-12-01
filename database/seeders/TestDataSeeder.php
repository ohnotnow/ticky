<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'admin2x',
            'email' => 'admin2x@example.com',
            'surname' => 'Admin',
            'forenames' => 'Admin',
            'password' => Hash::make('secret'),
        ]);

        $this->seedConversations($admin);

        $userCount = random_int(10, 15);

        User::factory($userCount)->create()->each(function (User $user): void {
            $this->seedConversations($user, random_int(0, 20));
        });
    }

    protected function seedConversations(User $user, ?int $conversationTotal = null): void
    {
        $samples = [
            [
                'user_message' => 'Staff need MATLAB installed on a new Linux server for a research project. Who should handle this?',
                'recommendations' => [
                    [
                        'team' => 'College Infrastructure',
                        'person' => 'Fiona Drummond',
                        'confidence' => 9,
                        'reasoning' => 'Linux server provisioning and configuration belongs with infrastructure; Fiona owns Linux servers.',
                    ],
                    [
                        'team' => 'Research Computing',
                        'person' => 'Hamish Baxter',
                        'confidence' => 7,
                        'reasoning' => 'After the server is ready, Hamish can assist with simulation software licensing like MATLAB.',
                    ],
                ],
            ],
            [
                'user_message' => 'The HPC GPU node keeps failing CUDA jobs overnight.',
                'recommendations' => [
                    [
                        'team' => 'Research Computing',
                        'person' => 'Rory Johnstone',
                        'confidence' => 9,
                        'reasoning' => 'GPU and CUDA troubleshooting sits with research computing; Rory focuses on GPU infrastructure.',
                    ],
                    [
                        'team' => 'Research Computing',
                        'person' => 'Dr Alistair Kerr',
                        'confidence' => 7,
                        'reasoning' => 'Cluster management oversight; Alistair can coordinate broader HPC fixes.',
                    ],
                ],
            ],
            [
                'user_message' => 'Need to create a new VLAN and firewall rules for a lab subnet.',
                'recommendations' => [
                    [
                        'team' => 'College Infrastructure',
                        'person' => 'Callum Reid',
                        'confidence' => 9,
                        'reasoning' => 'Networking and firewall changes are handled by infrastructure; Callum leads on networking.',
                    ],
                ],
            ],
            [
                'user_message' => 'Requesting a daily backup policy and restore test for the teaching file server.',
                'recommendations' => [
                    [
                        'team' => 'Service Resilience',
                        'person' => 'Karen Whyte',
                        'confidence' => 8,
                        'reasoning' => 'Backup schedules and restore testing are in service resilience; Karen manages Veeam and restores.',
                    ],
                    [
                        'team' => 'Service Resilience',
                        'person' => 'Fraser Donnelly',
                        'confidence' => 6,
                        'reasoning' => 'Documentation and DR testing coordination can be supported by Fraser.',
                    ],
                ],
            ],
            [
                'user_message' => 'New starter needs a laptop imaged, O365 setup, and induction tomorrow.',
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Craig Wallace',
                        'confidence' => 8,
                        'reasoning' => 'Device imaging and deployment are handled by service delivery; Craig handles hardware prep.',
                    ],
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Emma Ferguson',
                        'confidence' => 7,
                        'reasoning' => 'New starter setups and account provisioning fit Emma’s responsibilities.',
                    ],
                ],
            ],
            [
                'user_message' => 'API integration needed between the student portal and CRM for nightly syncs.',
                'recommendations' => [
                    [
                        'team' => 'Applications & Data',
                        'person' => 'Beth Paterson',
                        'confidence' => 8,
                        'reasoning' => 'In-house integrations and Laravel/PHP work belong with applications; Beth leads dev efforts.',
                    ],
                    [
                        'team' => 'Applications & Data',
                        'person' => 'Kieran Moffat',
                        'confidence' => 7,
                        'reasoning' => 'Data pipelines and APIs are part of Kieran’s remit.',
                    ],
                ],
            ],
        ];

        $conversationsToCreate = $conversationTotal ?? count($samples);

        for ($i = 0; $i < $conversationsToCreate; $i++) {
            $sample = $samples[array_rand($samples)];

            $createdAt = Carbon::now()->subDays(random_int(0, 300))->setTime(random_int(7, 18), random_int(0, 59));

            $userMessageContent = $sample['user_message'].' '.Str::of(fake()->sentence())->rtrim('.');

            $conversation = Conversation::create([
                'user_id' => $user->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $userMessageContent,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => json_encode(['recommendations' => $sample['recommendations']]),
                'model' => config('ticky.llm_model'),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
