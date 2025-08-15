<?php

namespace Database\Seeders;

use App\Models\Recording;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RecordingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create recordings directory if it doesn't exist
        if (!Storage::disk('public')->exists('recordings')) {
            Storage::disk('public')->makeDirectory('recordings');
        }

        // Get some users to assign recordings to
        $users = User::all();
        
        if ($users->isEmpty()) {
            // Create a default user if none exist
            $users = collect([
                User::create([
                    'name' => 'Demo User',
                    'email' => 'demo@example.com',
                    'password' => bcrypt('password'),
                ])
            ]);
        }

        $sampleRecordings = [
            [
                'title' => 'Meeting Notes - Q1 Planning',
                'description' => 'Discussion about Q1 goals and project priorities',
                'duration' => 1845, // 30:45
            ],
            [
                'title' => 'Interview - John Smith',
                'description' => 'Technical interview for Senior Developer position',
                'duration' => 2760, // 46:00
            ],
            [
                'title' => 'Team Standup - Monday',
                'description' => 'Daily standup meeting with development team',
                'duration' => 912, // 15:12
            ],
            [
                'title' => 'Client Call - ABC Corp',
                'description' => 'Requirements gathering for new project',
                'duration' => 3240, // 54:00
            ],
            [
                'title' => 'Brainstorming Session',
                'description' => 'Ideas for improving user experience',
                'duration' => 1680, // 28:00
            ],
            [
                'title' => 'Code Review Discussion',
                'description' => 'Review of authentication module implementation',
                'duration' => 1320, // 22:00
            ],
            [
                'title' => 'Customer Feedback Session',
                'description' => 'User feedback on latest product features',
                'duration' => 2100, // 35:00
            ],
            [
                'title' => 'Architecture Planning',
                'description' => 'Discussion on microservices migration strategy',
                'duration' => 3600, // 60:00
            ],
            [
                'title' => 'Bug Triage Meeting',
                'description' => 'Weekly bug review and prioritization',
                'duration' => 1080, // 18:00
            ],
            [
                'title' => 'Demo Rehearsal',
                'description' => 'Practice run for client presentation',
                'duration' => 900, // 15:00
            ],
            [
                'title' => 'Performance Review - Sarah',
                'description' => 'Quarterly performance discussion',
                'duration' => 2400, // 40:00
            ],
            [
                'title' => 'Training Session - React Hooks',
                'description' => 'Internal training on advanced React concepts',
                'duration' => 4500, // 75:00
            ],
            [
                'title' => 'Security Audit Discussion',
                'description' => 'Review of security findings and remediation',
                'duration' => 1800, // 30:00
            ],
            [
                'title' => 'Product Roadmap Meeting',
                'description' => 'Planning features for next quarter',
                'duration' => 2700, // 45:00
            ],
            [
                'title' => 'Vendor Evaluation Call',
                'description' => 'Assessment of new cloud provider options',
                'duration' => 1560, // 26:00
            ],
            [
                'title' => 'User Story Refinement',
                'description' => 'Breaking down epic into manageable stories',
                'duration' => 1440, // 24:00
            ],
            [
                'title' => 'API Design Review',
                'description' => 'Review of REST API specifications',
                'duration' => 1980, // 33:00
            ],
            [
                'title' => 'Database Migration Planning',
                'description' => 'Strategy for migrating legacy data',
                'duration' => 2160, // 36:00
            ],
            [
                'title' => 'Mobile App Discussion',
                'description' => 'Requirements for iOS and Android apps',
                'duration' => 3120, // 52:00
            ],
            [
                'title' => 'DevOps Pipeline Review',
                'description' => 'Optimization of CI/CD processes',
                'duration' => 1740, // 29:00
            ],
            [
                'title' => 'Quality Assurance Strategy',
                'description' => 'Improving testing processes and coverage',
                'duration' => 2040, // 34:00
            ],
            [
                'title' => 'Accessibility Audit',
                'description' => 'Review of WCAG compliance and improvements',
                'duration' => 1620, // 27:00
            ],
            [
                'title' => 'Data Analytics Review',
                'description' => 'Analysis of user behavior and metrics',
                'duration' => 2520, // 42:00
            ],
            [
                'title' => 'Emergency Response Planning',
                'description' => 'Disaster recovery and incident response procedures',
                'duration' => 2280, // 38:00
            ]
        ];

        foreach ($sampleRecordings as $index => $recordingData) {
            // Create a dummy audio file (just empty content for demo)
            $filename = 'sample_' . ($index + 1) . '.webm';
            $filePath = 'recordings/' . $filename;
            
            // Create an empty file as placeholder
            Storage::disk('public')->put($filePath, '');
            
            Recording::create([
                'title' => $recordingData['title'],
                'description' => $recordingData['description'],
                'filename' => $filename,
                'file_path' => $filePath,
                'duration' => $recordingData['duration'],
                'mime_type' => 'audio/webm',
                'file_size' => rand(50000, 500000), // Random file size between 50KB-500KB
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
