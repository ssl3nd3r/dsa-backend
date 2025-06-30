<?php

namespace Database\Seeders;

use Illuminate\Console\Command;

class PropertySeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-properties {--count=100 : Number of properties to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with properties';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = $this->option('count');
        
        $this->info("Seeding {$count} properties...");
        
        $seeder = new PropertySeeder();
        $seeder->run();
        
        $this->info('Properties seeded successfully!');
        
        return Command::SUCCESS;
    }
} 