<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Broadcast;
use App\Jobs\Facebook\BroadcastJob;

class TriggerBroadcastSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pixybots:trigger-broadcast-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add schdule broadcast to job list';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $localDate = new \DateTime();
        $localDate->setTimezone(new \DateTimeZone('Asia/Yangon'));
        $localDate->setTimestamp(time());
        $dateTime = [
            'day' => $localDate->format('N'),
            'date' => $localDate->format('d'),
            'month' => $localDate->format('m'),
            'year' => $localDate->format('Y'),
            'time' => $localDate->format('Hi'),
        ];

        $broadcast = Broadcast::query();
        $broadcast->where('status', 1);
        $broadcast->where('broadcast_type', 3);
        $broadcast->where('complete', 0);
        $broadcast->where(function($query) use ($dateTime) {
            //Exact match
            $query->where(function($query) use ($dateTime) {
                $query->where('interval_type', 1);
                $query->where('day', (int) $dateTime['date']);
                $query->where('month', (int) $dateTime['month']);
                $query->where('year', $dateTime['year']);
            });

            // Daily
            $query->orWhere('interval_type', 2);
            
            // Weekend
            if(in_array($dateTime['day'], [6, 7])) {
                $query->orWhere('interval_type', 3);
            }

            // Every month
            $query->orWhere(function($query) use ($dateTime) {
                $query->where('interval_type', 4);
                $query->where('day', $dateTime['date']);
            });

            // Weekday
            if(!in_array($dateTime['day'], [6, 7])) {
                $query->orWhere('interval_type', 5);
            }

            // Yearly
            $query->orWhere(function($query) use ($dateTime) {
                $query->where('interval_type', 6);
                $query->where('day', $dateTime['date']);
                $query->where('month', $dateTime['month']);
            });

            // custom
            $query->orWhere(function($query) use ($dateTime) {
                $query->where('interval_type', 7);
                $query->whereHas('weekday', function($query) use ($dateTime) {
                    $query->where('days', (int) $dateTime['day']);
                    $query->where('status', 1);
                });
            });
        });

        $broadcast->where('time', $dateTime['time']);
        
        $broadcast->whereHas('project', function($query) {
            $query->whereHas('page', function($query) {
                $query->where('publish', 1);
            });
        });

        $broadcast = $broadcast->get();

        if(!empty($broadcast)) {
            foreach($broadcast as $b) {
                BroadcastJob::dispatch($b->id);
            }
        }

        print_r($dateTime);
        echo "\n";
        print_r('totol schedule broadcast'.$broadcast->count());
        echo "\n";
        print_r($broadcast->toArray());
        echo "\n";
    }
}
