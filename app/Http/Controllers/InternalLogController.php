<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class InternalLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            
            // Basic regex to parse Laravel logs
            // Pattern: [timestamp] environment.LEVEL: message {context} [stacktrace]
            $pattern = '/^\[(?P<date>.*)\]\s(?P<env>\w+)\.(?P<level>\w+):(?P<message>.*)/m';
            
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach (array_reverse($matches) as $match) {
                    $logs[] = [
                        'date' => $match['date'],
                        'level' => strtoupper($match['level']),
                        'message' => trim($match['message']),
                        'is_error' => in_array(strtoupper($match['level']), ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY']),
                    ];
                    
                    // Limit to last 200 logs for performance
                    if (count($logs) >= 200) break;
                }
            }
        }

        return Inertia::render('Logs/Index', [
            'logs' => $logs,
            'secret' => $request->route('secret')
        ]);
    }
}
