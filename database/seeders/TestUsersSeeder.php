<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing test data
        echo "Clearing existing test data...\n";

        // Create Admin User
        echo "Creating Admin user...\n";
        $admin = User::firstOrCreate(
            ['email' => 'admin@qrmh.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        echo "✓ Admin created: admin@qrmh.test / password\n";

        // Create Events
        echo "\nCreating test events...\n";
        $event1 = Event::firstOrCreate(
            ['name' => 'Tech Conference 2025'],
            [
                'name_ar' => 'مؤتمر التقنية 2025',
                'date' => now()->addDays(30),
                'location' => 'Riyadh Convention Center',
                'location_ar' => 'مركز الرياض للمؤتمرات',
                'description' => 'Annual technology conference featuring the latest innovations',
                'description_ar' => 'المؤتمر التقني السنوي يعرض أحدث الابتكارات',
                'status' => 'active',
                'theme_settings' => [
                    'primary_color' => '#10B981',
                    'secondary_color' => '#14B8A6',
                ],
            ]
        );
        echo "✓ Event 1: Tech Conference 2025\n";

        $event2 = Event::firstOrCreate(
            ['name' => 'Business Expo 2025'],
            [
                'name_ar' => 'معرض الأعمال 2025',
                'date' => now()->addDays(60),
                'location' => 'Jeddah Exhibition Center',
                'location_ar' => 'مركز جدة للمعارض',
                'description' => 'International business and trade exhibition',
                'description_ar' => 'معرض الأعمال والتجارة الدولي',
                'status' => 'active',
                'theme_settings' => [
                    'primary_color' => '#3B82F6',
                    'secondary_color' => '#06B6D4',
                ],
            ]
        );
        echo "✓ Event 2: Business Expo 2025\n";

        $event3 = Event::firstOrCreate(
            ['name' => 'Healthcare Summit 2025'],
            [
                'name_ar' => 'قمة الرعاية الصحية 2025',
                'date' => now()->addDays(90),
                'location' => 'Dammam Medical City',
                'location_ar' => 'مدينة الدمام الطبية',
                'description' => 'Annual healthcare professionals summit',
                'description_ar' => 'القمة السنوية لمحترفي الرعاية الصحية',
                'status' => 'active',
                'theme_settings' => [
                    'primary_color' => '#EAB308',
                    'secondary_color' => '#F59E0B',
                ],
            ]
        );
        echo "✓ Event 3: Healthcare Summit 2025\n";

        // Create Event Manager 1 (manages Event 1 and 2)
        echo "\nCreating Event Managers...\n";
        $eventManager1 = User::firstOrCreate(
            ['email' => 'manager1@qrmh.test'],
            [
                'name' => 'Sarah Manager',
                'password' => Hash::make('password'),
                'role' => 'event_manager',
                'email_verified_at' => now(),
            ]
        );

        // Attach to events
        $eventManager1->events()->syncWithoutDetaching([
            $event1->id => ['role' => 'event_manager'],
            $event2->id => ['role' => 'event_manager'],
        ]);
        echo "✓ Event Manager 1: manager1@qrmh.test / password (manages Tech Conference & Business Expo)\n";

        // Create Event Manager 2 (manages Event 3)
        $eventManager2 = User::firstOrCreate(
            ['email' => 'manager2@qrmh.test'],
            [
                'name' => 'Ahmed Manager',
                'password' => Hash::make('password'),
                'role' => 'event_manager',
                'email_verified_at' => now(),
            ]
        );

        $eventManager2->events()->syncWithoutDetaching([
            $event3->id => ['role' => 'event_manager'],
        ]);
        echo "✓ Event Manager 2: manager2@qrmh.test / password (manages Healthcare Summit)\n";

        // Create Agents
        echo "\nCreating Agents...\n";
        $agent1 = User::firstOrCreate(
            ['email' => 'agent1@qrmh.test'],
            [
                'name' => 'John Scanner',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ]
        );

        $agent1->events()->syncWithoutDetaching([
            $event1->id => ['role' => 'agent'],
        ]);
        echo "✓ Agent 1: agent1@qrmh.test / password (Tech Conference)\n";

        $agent2 = User::firstOrCreate(
            ['email' => 'agent2@qrmh.test'],
            [
                'name' => 'Maria Scanner',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ]
        );

        $agent2->events()->syncWithoutDetaching([
            $event1->id => ['role' => 'agent'],
            $event2->id => ['role' => 'agent'],
        ]);
        echo "✓ Agent 2: agent2@qrmh.test / password (Tech Conference & Business Expo)\n";

        $agent3 = User::firstOrCreate(
            ['email' => 'agent3@qrmh.test'],
            [
                'name' => 'Ali Scanner',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ]
        );

        $agent3->events()->syncWithoutDetaching([
            $event3->id => ['role' => 'agent'],
        ]);
        echo "✓ Agent 3: agent3@qrmh.test / password (Healthcare Summit)\n";

        // Create sample attendees for Event 1
        echo "\nCreating sample attendees for Tech Conference...\n";
        $attendees = [
            [
                'type' => 'exhibitor',
                'name' => 'TechCorp Solutions',
                'name_ar' => 'شركة تك كورب للحلول',
                'email' => 'exhibitor1@techcorp.test',
                'mobile' => '+966501234567',
                'company' => 'TechCorp Inc.',
                'company_ar' => 'شركة تك كورب',
                'category' => 'company',
            ],
            [
                'type' => 'exhibitor',
                'name' => 'Ahmed Al-Mansour',
                'name_ar' => 'أحمد المنصور',
                'email' => 'exhibitor2@freelancer.test',
                'mobile' => '+966501234568',
                'company' => 'Freelance Developer',
                'company_ar' => 'مطور مستقل',
                'category' => 'freelancer',
            ],
            [
                'type' => 'guest',
                'name' => 'Sarah Johnson',
                'name_ar' => 'سارة جونسون',
                'email' => 'guest1@company.test',
                'mobile' => '+966501234569',
                'company' => 'Innovation Labs',
                'company_ar' => 'مختبرات الابتكار',
                'category' => 'company',
            ],
            [
                'type' => 'guest',
                'name' => 'Mohammed Ali',
                'name_ar' => 'محمد علي',
                'email' => 'guest2@example.test',
                'mobile' => '+966501234570',
                'company' => 'Digital Solutions',
                'company_ar' => 'الحلول الرقمية',
                'category' => 'company',
            ],
            [
                'type' => 'organizer',
                'name' => 'Fatima Hassan',
                'name_ar' => 'فاطمة حسن',
                'email' => 'organizer1@qrmh.test',
                'mobile' => '+966501234571',
                'role' => 'Event Coordinator',
                'department' => 'Operations',
            ],
        ];

        foreach ($attendees as $attendeeData) {
            $attendeeData['event_id'] = $event1->id;
            Attendee::firstOrCreate(
                ['email' => $attendeeData['email']],
                $attendeeData
            );
        }
        echo "✓ Created 5 sample attendees (2 exhibitors, 2 guests, 1 organizer)\n";

        // Summary
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "DATABASE SEEDING COMPLETE!\n";
        echo str_repeat("=", 60) . "\n\n";

        echo "TEST USERS CREATED:\n";
        echo "─────────────────────────────────────────────────────────\n";
        echo "ADMIN:\n";
        echo "  Email: admin@qrmh.test\n";
        echo "  Password: password\n";
        echo "  Access: Full system access\n\n";

        echo "EVENT MANAGERS:\n";
        echo "  1. manager1@qrmh.test / password\n";
        echo "     Events: Tech Conference, Business Expo\n";
        echo "  2. manager2@qrmh.test / password\n";
        echo "     Events: Healthcare Summit\n\n";

        echo "AGENTS (Scanner Only):\n";
        echo "  1. agent1@qrmh.test / password (Tech Conference)\n";
        echo "  2. agent2@qrmh.test / password (Tech Conference & Business Expo)\n";
        echo "  3. agent3@qrmh.test / password (Healthcare Summit)\n\n";

        echo "EVENTS CREATED:\n";
        echo "  1. Tech Conference 2025\n";
        echo "  2. Business Expo 2025\n";
        echo "  3. Healthcare Summit 2025\n\n";

        echo "ATTENDEES: 5 sample attendees in Tech Conference\n\n";
        echo "─────────────────────────────────────────────────────────\n";
        echo "You can now login at: http://localhost:8000/login\n";
        echo "─────────────────────────────────────────────────────────\n\n";
    }
}
