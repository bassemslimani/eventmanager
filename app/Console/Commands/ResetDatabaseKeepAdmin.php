<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\BadgeTemplate;
use App\Models\CheckIn;
use App\Models\ImportLog;
use App\Models\EmailCampaign;
use App\Models\CampaignRecipient;

class ResetDatabaseKeepAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-keep-admin {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database and keep only admin user (lemasbba@gmail.com)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will delete ALL data except the admin user. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting database reset...');

        // Delete all data from tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->info('Deleting campaigns...');
        CampaignRecipient::truncate();
        EmailCampaign::truncate();

        $this->info('Deleting check-ins...');
        CheckIn::truncate();

        $this->info('Deleting import logs...');
        ImportLog::truncate();

        $this->info('Deleting badge templates...');
        BadgeTemplate::truncate();

        $this->info('Deleting attendees...');
        Attendee::truncate();

        $this->info('Deleting events...');
        Event::truncate();

        $this->info('Deleting event_user pivot...');
        DB::table('event_user')->truncate();

        $this->info('Deleting all users...');
        User::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user
        $this->info('Creating admin user...');
        User::create([
            'name' => 'Admin',
            'email' => 'lemasbba@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->info('✅ Database reset complete!');
        $this->info('Admin user created:');
        $this->info('Email: lemasbba@gmail.com');
        $this->info('Password: 123456789');

        return 0;
    }
}
