<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class ExtractEvents extends Command
{
    protected $signature = 'events:extract';
    protected $description = 'Extracts event data from URLs using local llama.cpp';

    public function handle()
    {
        $path = 'crawler/links.txt';

        // Check if the file exists within storage/app/
        if (!Storage::disk('local')->exists($path)) {
            $this->error("links.txt not found in storage/app/crawler/");
            return;
        }
    
        // Get the file content as an array of lines
        $fileContent = Storage::disk('local')->get($path);
        $urls = array_unique(array_filter(explode("\n", $fileContent)));

        foreach ($urls as $url) {
            $this->info("Processing: $url");

            // 1. Fetch the page content
            try {
                $html = Http::get($url)->body();
                $cleanText = $this->cleanHtml($html);
            } catch (\Exception $e) {
                $this->error("Failed to fetch $url: " . $e->getMessage());
                continue;
            }

            // 2. Call llama.cpp with Grammars/JSON Schema
            $extractedData = $this->askLlama($cleanText);

            if (empty($extractedData['events'])) {
                $this->warn("No events found on page.");
                continue;
            }

            // 3. Process and De-duplicate
            foreach ($extractedData['events'] as $eventData) {
                $this->insertEventIfUnique($eventData);
            }
        }

        $this->info('Extraction complete!');
    }

    private function cleanHtml($text)
    {
        $removeWithContent = ['script', 'style', 'header', 'footer'];
        foreach ($removeWithContent as $tag) {
            $text = preg_replace('/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/is', "", $text);
        }
        $text = preg_replace('/<(br|p)\W*>/is', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
        return trim($text);
    }

    private function askLlama($text)
    {
        // Truncate text if it's too long for your model's context
        $truncatedText = substr($text, 0, 8000); 

        $response = Http::timeout(120)->post('http://localhost:8080/v1/chat/completions', [
            'model' => 'local-model', // llama.cpp accepts any string here
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a data extraction assistant. Extract all event information from the text. Always return dates in YYYY-MM-DD HH:MM:SS format.'
                ],
                [
                    'role' => 'user',
                    'content' => "Extract events from this text:\n\n" . $truncatedText
                ]
            ],
            // This forces llama.cpp to output precisely the JSON structure we want
            'response_format' => [
                'type' => 'json_object',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'events' => [
                          'type' => 'array',
                          'items' => [
                            'type' => 'object',
                            'properties' => [
                              'title' => ['type' => 'string'],
                              'start' => ['type' => 'string'],
                              'location' => ['type' => 'string']
                            ],
                            'required' => ['title', 'start', 'location']
                          ]
                        ]
                    ],
                    'required' => ['events']
                ]
            ],
            'temperature' => 0.1, // Low temperature for factual extraction
        ]);

        $result = $response->json();
        
        // The result will be a JSON string inside the message content
        $content = $result['choices'][0]['message']['content'] ?? '{}';
        return json_decode($content, true);
    }

    private function insertEventIfUnique($eventData)
    {
        try {
            $startTime = Carbon::parse($eventData['start'])->format('Y-m-y H:i:s');
        } catch (\Exception $e) {
            $this->error("Invalid date format returned: " . $eventData['start']);
            return;
        }

        // De-duplication Logic: Check if an event at the same location and time already exists
        $exists = DB::table('events')
            ->where('start', $startTime)
            ->where('location', 'LIKE', '%' . trim($eventData['location']) . '%')
            ->exists();

        if (!$exists) {
            DB::table('events')->insert([
                'title' => $eventData['title'],
                'start' => $startTime,
                'location' => $eventData['location'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("Added event: " . $eventData['title']);
        } else {
            $this->comment("Duplicate skipped: " . $eventData['title']);
        }
    }
}