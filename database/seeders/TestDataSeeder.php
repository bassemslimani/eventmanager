<?php

namespace Database\Seeders;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test event
        $event = Event::create([
            'name' => 'Tech Summit 2025',
            'name_ar' => 'قمة التقنية 2025',
            'date' => now()->addDays(30),
            'location' => 'Riyadh Convention Center',
            'location_ar' => 'مركز الرياض للمؤتمرات',
            'description' => 'Annual technology summit featuring latest innovations',
            'description_ar' => 'القمة التقنية السنوية التي تعرض أحدث الابتكارات',
            'status' => 'active',
        ]);

        // Create test attendees - Exhibitors
        $exhibitors = [
            [
                'type' => 'exhibitor',
                'name' => 'Ahmed Al-Saud',
                'name_ar' => 'أحمد السعود',
                'email' => 'ahmed@techcorp.sa',
                'mobile' => '+966501234567',
                'company' => 'Tech Corp Saudi',
                'company_ar' => 'شركة التقنية السعودية',
                'category' => 'company',
            ],
            [
                'type' => 'exhibitor',
                'name' => 'Fatima Al-Otaibi',
                'name_ar' => 'فاطمة العتيبي',
                'email' => 'fatima@innovate.sa',
                'mobile' => '+966502345678',
                'company' => 'Innovate Solutions',
                'company_ar' => 'حلول الابتكار',
                'category' => 'company',
            ],
            [
                'type' => 'exhibitor',
                'name' => 'Mohammed Al-Harbi',
                'name_ar' => 'محمد الحربي',
                'email' => 'mohammed.harbi@freelancer.sa',
                'mobile' => '+966503456789',
                'company' => 'Independent Developer',
                'company_ar' => 'مطور مستقل',
                'category' => 'freelancer',
            ],
        ];

        // Create test attendees - Guests
        $guests = [
            [
                'type' => 'guest',
                'name' => 'Sarah Johnson',
                'name_ar' => 'سارة جونسون',
                'email' => 'sarah@example.com',
                'mobile' => '+966504567890',
                'company' => 'Design Studio Pro',
                'company_ar' => 'استوديو التصميم المحترف',
            ],
            [
                'type' => 'guest',
                'name' => 'Omar Al-Zahrani',
                'name_ar' => 'عمر الزهراني',
                'email' => 'omar@example.com',
                'mobile' => '+966505678901',
                'company' => 'Consulting Group',
                'company_ar' => 'مجموعة الاستشارات',
            ],
        ];

        // Create test attendees - Organizers
        $organizers = [
            [
                'type' => 'organizer',
                'name' => 'Layla Al-Mutairi',
                'name_ar' => 'ليلى المطيري',
                'email' => 'layla@eventmanager.sa',
                'mobile' => '+966506789012',
                'role' => 'Event Coordinator',
                'department' => 'Operations',
            ],
            [
                'type' => 'organizer',
                'name' => 'Khalid Al-Dosari',
                'name_ar' => 'خالد الدوسري',
                'email' => 'khalid@eventmanager.sa',
                'mobile' => '+966507890123',
                'role' => 'Technical Lead',
                'department' => 'IT',
            ],
        ];

        // Create all attendees
        foreach ($exhibitors as $data) {
            $data['event_id'] = $event->id;
            Attendee::create($data);
        }

        foreach ($guests as $data) {
            $data['event_id'] = $event->id;
            Attendee::create($data);
        }

        foreach ($organizers as $data) {
            $data['event_id'] = $event->id;
            Attendee::create($data);
        }

        $this->command->info('✅ Created test event and 7 attendees:');
        $this->command->info('   - 3 Exhibitors (2 companies, 1 freelancer)');
        $this->command->info('   - 2 Guests');
        $this->command->info('   - 2 Organizers');
    }
}
