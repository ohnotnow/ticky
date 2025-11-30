<?php

return [
    'api_keys' => [
        "openai" => env('OPENAI_API_KEY'),
        "anthropic" => env('ANTHROPIC_API_KEY'),
    ],
    'max_tokens' => [
        'default' => 100000,
        'small' => 10000,
    ],
    'llm_model' => env('LLM_MODEL', 'openai/gpt-5.1'),
    'model_choices' => [
        'openai' => [
            'gpt-5.1' => 'GPT-5.1',
            'gpt-5.1-mini' => 'GPT-5.1 Mini',
        ],
        'anthropic' => [
            'claude-sonnet-4-5' => 'Claude Sonnet 4.5',
            'claude-haiku-4-5' => 'Claude Haiku 4.5',
            'claude-opus-4.5' => 'Claude Opus 4.5',
        ],
    ],
    "org_chart" => [
        'organisation_name' => 'University of Somewhere - College of Science & Engineering IT',
        'teams' => [
            [
                'name' => 'College Infrastructure',
                'description' => 'Core IT infrastructure including Active Directory, Windows/Linux servers, networking, firewalls, and security compliance.',
                'members' => [
                    ['name' => 'Ewan MacLeod', 'skills' => 'Team lead, Active Directory, Group Policy, Azure AD'],
                    ['name' => 'Fiona Drummond', 'skills' => 'Linux servers, VMware, Ansible automation'],
                    ['name' => 'Callum Reid', 'skills' => 'Networking, Cisco, VLANs, firewall rules'],
                    ['name' => 'Isla Henderson', 'skills' => 'Windows Server, SCCM, patch management'],
                    ['name' => 'Graeme Sinclair', 'skills' => 'Security, vulnerability scanning, penetration testing'],
                ],
            ],
            [
                'name' => 'Research Computing',
                'description' => 'High-performance computing, research storage, scientific software, and support for computational research projects.',
                'members' => [
                    ['name' => 'Dr Alistair Kerr', 'skills' => 'Team lead, HPC cluster management, Slurm, PBS'],
                    ['name' => 'Morag Campbell', 'skills' => 'Research storage, Lustre, NFS, data management'],
                    ['name' => 'Niamh O\'Brien', 'skills' => 'Scientific software, modules, compilation, containers'],
                    ['name' => 'Rory Johnstone', 'skills' => 'GPU computing, CUDA, machine learning infrastructure'],
                    ['name' => 'Ailsa Grant', 'skills' => 'Research data management, FAIR principles, archiving'],
                    ['name' => 'Hamish Baxter', 'skills' => 'Simulation software, ANSYS, COMSOL, licensing'],
                ],
            ],
            [
                'name' => 'Applications & Data',
                'description' => 'In-house software development, web applications, scripting, database management, and data integrations.',
                'members' => [
                    ['name' => 'Beth Paterson', 'skills' => 'Team lead, Laravel, PHP, system integrations'],
                    ['name' => 'Kieran Moffat', 'skills' => 'Python, data pipelines, APIs, automation scripts'],
                    ['name' => 'Eilidh Crawford', 'skills' => 'Frontend, Vue.js, Laravel, databases, reporting'],
                ],
            ],
            [
                'name' => 'Service Resilience',
                'description' => 'Backup systems, disaster recovery planning, business continuity, license management, and service monitoring.',
                'members' => [
                    ['name' => 'Douglas Fleming', 'skills' => 'Team lead, disaster recovery, business continuity planning'],
                    ['name' => 'Karen Whyte', 'skills' => 'Veeam, backup systems, restore testing'],
                    ['name' => 'Stuart Gillespie', 'skills' => 'License management, software asset management, compliance'],
                    ['name' => 'Lynne Murray', 'skills' => 'Monitoring, Nagios, alerting, SLA reporting'],
                    ['name' => 'Fraser Donnelly', 'skills' => 'Documentation, runbooks, DR testing coordination'],
                ],
            ],
            [
                'name' => 'Service Delivery',
                'description' => 'Front-line IT support, device deployment, end-user assistance, AV equipment, and day-to-day technical queries across the College.',
                'members' => [
                    ['name' => 'Shona MacKenzie', 'skills' => 'Team lead, service desk management, escalation handling'],
                    ['name' => 'Craig Wallace', 'skills' => 'Laptop deployment, imaging, hardware repairs'],
                    ['name' => 'Jennifer Thomson', 'skills' => 'End-user support, Office 365, password resets'],
                    ['name' => 'Mark Patterson', 'skills' => 'AV equipment, lecture theatre support, video conferencing'],
                    ['name' => 'Laura Cunningham', 'skills' => 'Software installation, printing, general queries'],
                    ['name' => 'Ryan McAllister', 'skills' => 'Hardware troubleshooting, peripherals, mobile devices'],
                    ['name' => 'Emma Ferguson', 'skills' => 'New starter setups, account provisioning, inductions'],
                    ['name' => 'David Millar', 'skills' => 'Phone support, ticket logging, first-line triage'],
                    ['name' => 'Claire Robertson', 'skills' => 'Staff training, documentation, how-to guides'],
                    ['name' => 'Aiden Stewart', 'skills' => 'Walk-in support, loan equipment, desk moves'],
                ],
            ],
        ],
    ],
];
