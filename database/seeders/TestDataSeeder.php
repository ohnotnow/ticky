<?php

namespace Database\Seeders;

use App\Enums\SkillLevel;
use App\Models\Conversation;
use App\Models\MemberSkill;
use App\Models\Message;
use App\Models\Team;
use App\Models\TeamMember;
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
        $this->seedOrgChart();

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

    protected function seedOrgChart(): void
    {
        $orgChart = $this->getOrgChartData();

        foreach ($orgChart as $teamData) {
            $team = Team::create([
                'name' => $teamData['team'],
                'description' => $teamData['description'],
                'route_guidance' => $teamData['route_guidance'] ?? null,
            ]);

            foreach ($teamData['members'] as $memberData) {
                $member = TeamMember::create([
                    'team_id' => $team->id,
                    'name' => $memberData['name'],
                    'route_guidance' => $memberData['guidance'] ?? null,
                ]);

                foreach ($memberData['skills'] as $skillData) {
                    MemberSkill::create([
                        'team_member_id' => $member->id,
                        'name' => $skillData['name'],
                        'level' => $skillData['level'],
                    ]);
                }
            }
        }
    }

    protected function getOrgChartData(): array
    {
        return [
            // College Infrastructure - Deep technical infrastructure, NOT end-user support
            [
                'team' => 'College Infrastructure',
                'description' => 'Core IT infrastructure including Active Directory, Windows/Linux servers, networking, firewalls, and security compliance.',
                'route_guidance' => 'Only route tickets here when they involve infrastructure-level changes (server provisioning, firewall rules, AD architecture). Day-to-day user issues like password resets, VPN problems, and WiFi troubleshooting should go to Service Delivery first.',
                'members' => [
                    [
                        'name' => 'Ewan MacLeod',
                        'guidance' => 'Strategic AD architecture and Azure federation projects. Day-to-day password resets and account lockouts go to Service Delivery.',
                        'skills' => [
                            ['name' => 'Team lead', 'level' => SkillLevel::High],
                            ['name' => 'Active Directory', 'level' => SkillLevel::High],
                            ['name' => 'Group Policy', 'level' => SkillLevel::High],
                            ['name' => 'Azure AD', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Fiona Drummond',
                        'guidance' => 'Production server provisioning and automation. Development VMs and researcher test environments should be requested through standard service catalogue.',
                        'skills' => [
                            ['name' => 'Linux servers', 'level' => SkillLevel::High],
                            ['name' => 'VMware', 'level' => SkillLevel::High],
                            ['name' => 'Ansible automation', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Callum Reid',
                        'guidance' => 'Network infrastructure changes only - switch configs, firewall rules, VLAN setup. WiFi issues, VPN client problems, and "internet slow" tickets go to Service Delivery.',
                        'skills' => [
                            ['name' => 'Networking', 'level' => SkillLevel::High],
                            ['name' => 'Cisco', 'level' => SkillLevel::High],
                            ['name' => 'VLANs', 'level' => SkillLevel::High],
                            ['name' => 'Firewall rules', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Isla Henderson',
                        'guidance' => 'Enterprise patching and SCCM package deployment for servers. Individual laptop updates and desktop software go to Service Delivery.',
                        'skills' => [
                            ['name' => 'Windows Server', 'level' => SkillLevel::High],
                            ['name' => 'SCCM', 'level' => SkillLevel::High],
                            ['name' => 'Patch management', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Graeme Sinclair',
                        'guidance' => 'Security assessments, vulnerability remediation, compliance audits. Suspected phishing or compromised accounts - Service Delivery handles immediate lockout, then escalate to me for investigation.',
                        'skills' => [
                            ['name' => 'Security', 'level' => SkillLevel::High],
                            ['name' => 'Vulnerability scanning', 'level' => SkillLevel::High],
                            ['name' => 'Penetration testing', 'level' => SkillLevel::Medium],
                        ],
                    ],
                ],
            ],

            // Research Computing - Specialist HPC/research support, NOT general IT
            [
                'team' => 'Research Computing',
                'description' => 'High-performance computing, research storage, scientific software, and support for computational research projects.',
                'route_guidance' => 'Only for HPC cluster issues, scientific software, research data storage, and computational research support. General IT queries from researchers (laptop issues, email problems) should still go to Service Delivery.',
                'members' => [
                    [
                        'name' => 'Dr Alistair Kerr',
                        'guidance' => 'HPC strategy, grant consultations, and capacity planning. Day-to-day job failures and queue issues go to Rory or Niamh first.',
                        'skills' => [
                            ['name' => 'Team lead', 'level' => SkillLevel::High],
                            ['name' => 'HPC cluster management', 'level' => SkillLevel::High],
                            ['name' => 'Slurm', 'level' => SkillLevel::High],
                            ['name' => 'PBS', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Morag Campbell',
                        'guidance' => 'Storage allocations and research data architecture. Basic file permission issues and "can\'t access my files" queries start with Ailsa.',
                        'skills' => [
                            ['name' => 'Research storage', 'level' => SkillLevel::High],
                            ['name' => 'Lustre', 'level' => SkillLevel::High],
                            ['name' => 'NFS', 'level' => SkillLevel::High],
                            ['name' => 'Data management', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => "Niamh O'Brien",
                        'guidance' => 'Complex compilation, container builds, and module conflicts. Standard "module load" questions - check our wiki first, then contact me.',
                        'skills' => [
                            ['name' => 'Scientific software', 'level' => SkillLevel::High],
                            ['name' => 'Modules', 'level' => SkillLevel::High],
                            ['name' => 'Compilation', 'level' => SkillLevel::High],
                            ['name' => 'Containers', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Rory Johnstone',
                        'guidance' => 'GPU node issues, CUDA debugging, and ML framework setup. General HPC job submission questions start with Niamh.',
                        'skills' => [
                            ['name' => 'GPU computing', 'level' => SkillLevel::High],
                            ['name' => 'CUDA', 'level' => SkillLevel::High],
                            ['name' => 'Machine learning infrastructure', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Ailsa Grant',
                        'guidance' => 'Data management planning and long-term archiving. For storage quota increases, Morag handles allocations.',
                        'skills' => [
                            ['name' => 'Research data management', 'level' => SkillLevel::High],
                            ['name' => 'FAIR principles', 'level' => SkillLevel::High],
                            ['name' => 'Archiving', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Hamish Baxter',
                        'guidance' => 'Simulation license issues and complex multi-physics setup. Standard ANSYS/COMSOL usage - refer to training materials first.',
                        'skills' => [
                            ['name' => 'Simulation software', 'level' => SkillLevel::High],
                            ['name' => 'ANSYS', 'level' => SkillLevel::High],
                            ['name' => 'COMSOL', 'level' => SkillLevel::High],
                            ['name' => 'Licensing', 'level' => SkillLevel::Medium],
                        ],
                    ],
                ],
            ],

            // Applications & Data - Bespoke development, NOT WordPress or standard tools
            [
                'team' => 'Applications & Data',
                'description' => 'In-house software development, web applications, scripting, database management, and data integrations.',
                'members' => [
                    [
                        'name' => 'Beth Paterson',
                        'guidance' => 'In-house application development and major system integrations. WordPress updates, simple web edits, and CMS content changes go to Service Delivery.',
                        'skills' => [
                            ['name' => 'Team lead', 'level' => SkillLevel::High],
                            ['name' => 'Laravel', 'level' => SkillLevel::High],
                            ['name' => 'PHP', 'level' => SkillLevel::High],
                            ['name' => 'System integrations', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Kieran Moffat',
                        'guidance' => 'Automated data feeds and API integrations. One-off data exports - check if existing dashboards or reports can help first.',
                        'skills' => [
                            ['name' => 'Python', 'level' => SkillLevel::High],
                            ['name' => 'Data pipelines', 'level' => SkillLevel::High],
                            ['name' => 'APIs', 'level' => SkillLevel::High],
                            ['name' => 'Automation scripts', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Eilidh Crawford',
                        'guidance' => 'Frontend development and database schema work. Standard report requests - try self-service reporting tools first.',
                        'skills' => [
                            ['name' => 'Frontend', 'level' => SkillLevel::High],
                            ['name' => 'Vue.js', 'level' => SkillLevel::High],
                            ['name' => 'Laravel', 'level' => SkillLevel::Medium],
                            ['name' => 'Databases', 'level' => SkillLevel::High],
                            ['name' => 'Reporting', 'level' => SkillLevel::Medium],
                        ],
                    ],
                ],
            ],

            // Service Resilience - Business continuity, NOT daily operations
            [
                'team' => 'Service Resilience',
                'description' => 'Backup systems, disaster recovery planning, business continuity, license management, and service monitoring.',
                'members' => [
                    [
                        'name' => 'Douglas Fleming',
                        'guidance' => 'Disaster recovery planning and major incident coordination. Routine backup questions go to Karen.',
                        'skills' => [
                            ['name' => 'Team lead', 'level' => SkillLevel::High],
                            ['name' => 'Disaster recovery', 'level' => SkillLevel::High],
                            ['name' => 'Business continuity planning', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Karen Whyte',
                        'guidance' => 'Backup configuration and restore requests. Please include exact file paths and approximate date when requesting restores.',
                        'skills' => [
                            ['name' => 'Veeam', 'level' => SkillLevel::High],
                            ['name' => 'Backup systems', 'level' => SkillLevel::High],
                            ['name' => 'Restore testing', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Stuart Gillespie',
                        'guidance' => 'Enterprise licensing agreements and compliance audits. Individual software installation requests go to Service Delivery.',
                        'skills' => [
                            ['name' => 'License management', 'level' => SkillLevel::High],
                            ['name' => 'Software asset management', 'level' => SkillLevel::High],
                            ['name' => 'Compliance', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Lynne Murray',
                        'guidance' => 'Monitoring configuration and SLA reporting. For current service outages, check the status page first.',
                        'skills' => [
                            ['name' => 'Monitoring', 'level' => SkillLevel::High],
                            ['name' => 'Nagios', 'level' => SkillLevel::High],
                            ['name' => 'Alerting', 'level' => SkillLevel::High],
                            ['name' => 'SLA reporting', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Fraser Donnelly',
                        'guidance' => 'DR test coordination and runbook maintenance. Knowledge base suggestions welcome - submit via Service Delivery.',
                        'skills' => [
                            ['name' => 'Documentation', 'level' => SkillLevel::High],
                            ['name' => 'Runbooks', 'level' => SkillLevel::High],
                            ['name' => 'DR testing coordination', 'level' => SkillLevel::Medium],
                        ],
                    ],
                ],
            ],

            // Central IT - University-wide services managed centrally, not by the College
            [
                'team' => 'Central IT',
                'description' => 'University-wide IT services managed by central IT, not the College. Includes core systems, email infrastructure, identity management, and enterprise applications.',
                'members' => [
                    [
                        'name' => 'Central IT',
                        'guidance' => 'Route here when the issue sounds like a centrally-managed university service rather than something our local College IT team handles.',
                        'skills' => [
                            ['name' => 'University systems', 'level' => SkillLevel::High],
                            ['name' => 'Core infrastructure', 'level' => SkillLevel::High],
                            ['name' => 'Enterprise applications', 'level' => SkillLevel::High],
                        ],
                    ],
                ],
            ],

            // Service Delivery - Front-line support, the DEFAULT destination for ambiguous tickets
            [
                'team' => 'Service Delivery',
                'description' => 'Front-line IT support, device deployment, end-user assistance, AV equipment, and day-to-day technical queries across the College.',
                'route_guidance' => 'This is the DEFAULT team for most tickets. When in doubt, route here. They handle initial triage and will escalate to specialist teams if needed. Most end-user issues should come here first.',
                'members' => [
                    [
                        'name' => 'Shona MacKenzie',
                        'guidance' => 'Complex escalations and service desk coordination. Most initial queries should go to front-line staff first.',
                        'skills' => [
                            ['name' => 'Team lead', 'level' => SkillLevel::High],
                            ['name' => 'Service desk management', 'level' => SkillLevel::High],
                            ['name' => 'Escalation handling', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Craig Wallace',
                        'guidance' => 'Device provisioning, imaging, and warranty repairs. Software-only issues on existing devices go to Laura.',
                        'skills' => [
                            ['name' => 'Laptop deployment', 'level' => SkillLevel::High],
                            ['name' => 'Imaging', 'level' => SkillLevel::High],
                            ['name' => 'Hardware repairs', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Jennifer Thomson',
                        'guidance' => 'First contact for O365 issues, password resets, and account lockouts. Complex AD/Azure issues may need Infrastructure.',
                        'skills' => [
                            ['name' => 'End-user support', 'level' => SkillLevel::High],
                            ['name' => 'Office 365', 'level' => SkillLevel::High],
                            ['name' => 'Password resets', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Mark Patterson',
                        'guidance' => 'Lecture theatre AV and video conferencing. Book support in advance for important presentations.',
                        'skills' => [
                            ['name' => 'AV equipment', 'level' => SkillLevel::High],
                            ['name' => 'Lecture theatre support', 'level' => SkillLevel::High],
                            ['name' => 'Video conferencing', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Laura Cunningham',
                        'guidance' => 'Standard software installation and print queue issues. Specialist research software may need Research Computing.',
                        'skills' => [
                            ['name' => 'Software installation', 'level' => SkillLevel::High],
                            ['name' => 'Printing', 'level' => SkillLevel::High],
                            ['name' => 'General queries', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Ryan McAllister',
                        'guidance' => 'Peripheral issues and hardware diagnosis. Confirmed hardware failures needing replacement go to Craig.',
                        'skills' => [
                            ['name' => 'Hardware troubleshooting', 'level' => SkillLevel::High],
                            ['name' => 'Peripherals', 'level' => SkillLevel::High],
                            ['name' => 'Mobile devices', 'level' => SkillLevel::Medium],
                        ],
                    ],
                    [
                        'name' => 'Emma Ferguson',
                        'guidance' => 'Individual new starter setup and inductions. Bulk account creation (>10 users) needs Infrastructure involvement.',
                        'skills' => [
                            ['name' => 'New starter setups', 'level' => SkillLevel::High],
                            ['name' => 'Account provisioning', 'level' => SkillLevel::High],
                            ['name' => 'Inductions', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'David Millar',
                        'guidance' => 'Initial ticket logging and phone support. Will triage and route appropriately.',
                        'skills' => [
                            ['name' => 'Phone support', 'level' => SkillLevel::High],
                            ['name' => 'Ticket logging', 'level' => SkillLevel::High],
                            ['name' => 'First-line triage', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Claire Robertson',
                        'guidance' => 'Training sessions and user guides. For urgent issues, contact front-line support instead.',
                        'skills' => [
                            ['name' => 'Staff training', 'level' => SkillLevel::High],
                            ['name' => 'Documentation', 'level' => SkillLevel::High],
                            ['name' => 'How-to guides', 'level' => SkillLevel::High],
                        ],
                    ],
                    [
                        'name' => 'Aiden Stewart',
                        'guidance' => 'Walk-in desk-side support and equipment loans. Book ahead for office moves.',
                        'skills' => [
                            ['name' => 'Walk-in support', 'level' => SkillLevel::High],
                            ['name' => 'Loan equipment', 'level' => SkillLevel::High],
                            ['name' => 'Desk moves', 'level' => SkillLevel::Medium],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getSampleConversations(): array
    {
        return [
            // Ticket 1: VPN issue - goes to Service Delivery, NOT networking expert
            [
                'user_message' => "VPN won't connect from home, getting timeout errors when I try to login.",
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Jennifer Thomson',
                        'confidence' => 9,
                        'reasoning' => 'VPN client issues are handled by Service Delivery. Jennifer handles O365 and connectivity issues for end users.',
                    ],
                    [
                        'team' => 'Service Delivery',
                        'person' => 'David Millar',
                        'confidence' => 7,
                        'reasoning' => 'First-line support can help troubleshoot basic VPN connectivity before escalating if needed.',
                    ],
                ],
            ],

            // Ticket 2: Password reset - goes to Service Delivery, NOT AD expert
            [
                'user_message' => 'Need to reset my password urgently - locked out of my account after too many failed attempts.',
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Jennifer Thomson',
                        'confidence' => 9,
                        'reasoning' => 'Password resets and account lockouts are standard Service Delivery tasks. Jennifer specialises in this.',
                    ],
                ],
            ],

            // Ticket 3: WiFi issues - Service Delivery first, NOT networking
            [
                'user_message' => 'WiFi keeps dropping in the Chemistry building, multiple staff affected.',
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'David Millar',
                        'confidence' => 8,
                        'reasoning' => 'Initial diagnosis of WiFi issues starts with Service Delivery to gather details and confirm scope.',
                    ],
                    [
                        'team' => 'College Infrastructure',
                        'person' => 'Callum Reid',
                        'confidence' => 6,
                        'reasoning' => 'May need to escalate to Callum if this turns out to be an infrastructure issue with access points or switches.',
                    ],
                ],
            ],

            // Ticket 4: HPC job failure - goes to day-to-day support, NOT team lead
            [
                'user_message' => 'My HPC job keeps running out of memory after 2 hours, even though I requested 64GB.',
                'recommendations' => [
                    [
                        'team' => 'Research Computing',
                        'person' => 'Rory Johnstone',
                        'confidence' => 8,
                        'reasoning' => 'Day-to-day job failures go to Rory or Niamh. Rory handles GPU and memory-intensive workloads.',
                    ],
                    [
                        'team' => 'Research Computing',
                        'person' => "Niamh O'Brien",
                        'confidence' => 7,
                        'reasoning' => 'Niamh can help with job configuration and resource allocation issues.',
                    ],
                ],
            ],

            // Ticket 5: Phishing - Service Delivery first for lockout, THEN security
            [
                'user_message' => 'I received a suspicious email asking me to verify my account details - I think I might have clicked the link.',
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Jennifer Thomson',
                        'confidence' => 9,
                        'reasoning' => 'Immediate lockout and password reset handled by Service Delivery first.',
                    ],
                    [
                        'team' => 'College Infrastructure',
                        'person' => 'Graeme Sinclair',
                        'confidence' => 7,
                        'reasoning' => 'After immediate lockout, escalate to Graeme for security investigation and follow-up.',
                    ],
                ],
            ],

            // Ticket 6: WordPress update - Service Delivery, NOT dev team
            [
                'user_message' => 'Need the department WordPress blog updated with new staff announcements and photos.',
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Laura Cunningham',
                        'confidence' => 8,
                        'reasoning' => 'Simple CMS content updates are handled by Service Delivery, not the development team.',
                    ],
                ],
            ],

            // Ticket 7: Firewall rule - THIS IS infrastructure level (correct routing)
            [
                'user_message' => 'Requesting firewall rule to allow new lab equipment to phone home for firmware updates on port 8443.',
                'recommendations' => [
                    [
                        'team' => 'College Infrastructure',
                        'person' => 'Callum Reid',
                        'confidence' => 9,
                        'reasoning' => 'Firewall rule changes are infrastructure-level networking - exactly what Callum handles.',
                    ],
                ],
            ],

            // Ticket 8: Data export - check self-service first
            [
                'user_message' => 'Can I get a data export of all students enrolled in Chemistry modules last semester?',
                'recommendations' => [
                    [
                        'team' => 'Applications & Data',
                        'person' => 'Kieran Moffat',
                        'confidence' => 7,
                        'reasoning' => 'Kieran handles data exports, but recommend checking existing dashboards or self-service reports first.',
                    ],
                    [
                        'team' => 'Applications & Data',
                        'person' => 'Eilidh Crawford',
                        'confidence' => 6,
                        'reasoning' => 'Eilidh may be able to help if this can be added to existing reporting tools.',
                    ],
                ],
            ],

            // Ticket 9: New starter - Service Delivery handles this holistically
            [
                'user_message' => 'New PhD student starting Monday, needs account setup, laptop, and building access sorted.',
                'recommendations' => [
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Emma Ferguson',
                        'confidence' => 9,
                        'reasoning' => 'New starter setups are Emma\'s speciality - she handles account provisioning and inductions.',
                    ],
                    [
                        'team' => 'Service Delivery',
                        'person' => 'Craig Wallace',
                        'confidence' => 7,
                        'reasoning' => 'Craig can prepare the laptop for the new starter.',
                    ],
                ],
            ],

            // Ticket 10: License server down - THIS IS research computing (correct routing)
            [
                'user_message' => 'The ANSYS license server seems to be down - none of our simulation jobs can start.',
                'recommendations' => [
                    [
                        'team' => 'Research Computing',
                        'person' => 'Hamish Baxter',
                        'confidence' => 9,
                        'reasoning' => 'Simulation license issues are Hamish\'s domain - he manages ANSYS and COMSOL licensing.',
                    ],
                ],
            ],

            // Ticket 11: File restore - Service Resilience with details
            [
                'user_message' => 'I accidentally deleted an important file last week from the shared drive, can it be recovered? It was in S:/Research/Grants/2024/',
                'recommendations' => [
                    [
                        'team' => 'Service Resilience',
                        'person' => 'Karen Whyte',
                        'confidence' => 9,
                        'reasoning' => 'File restores are Karen\'s area. Good that the user provided the path and approximate date.',
                    ],
                ],
            ],

            // Ticket 12: Azure AD sync - THIS IS infrastructure level (correct routing)
            [
                'user_message' => "Our team's Azure AD group isn't syncing properly with the HR system - new starters aren't appearing.",
                'recommendations' => [
                    [
                        'team' => 'College Infrastructure',
                        'person' => 'Ewan MacLeod',
                        'confidence' => 9,
                        'reasoning' => 'Azure AD federation and HR system integration is strategic infrastructure work - Ewan\'s area.',
                    ],
                    [
                        'team' => 'Applications & Data',
                        'person' => 'Beth Paterson',
                        'confidence' => 6,
                        'reasoning' => 'May need involvement from Applications team if the integration code needs debugging.',
                    ],
                ],
            ],
        ];
    }

    protected function seedConversations(User $user, ?int $conversationTotal = null): void
    {
        $samples = $this->getSampleConversations();

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
