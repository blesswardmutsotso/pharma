<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventReminder;
use Google\Service\Calendar\EventReminders;

class DemoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'company'       => 'required|string',
            'email'         => 'required|email',
            'address'       => 'required|string',
            'contact'       => 'required|string',
            'contact_phone' => 'required|string',
            'primary'       => 'required|string',
            'whatsapp'      => 'required|string',
            'date'          => 'required|date',
            'time'          => 'required|string',
        ]);

        try {
            $client = new GoogleClient();
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->addScope(Calendar::CALENDAR_EVENTS);
            $client->setAccessType('offline');

            // Load stored token
            $tokenPath = storage_path('app/google-token.json');
            if (file_exists($tokenPath)) {
                $token = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($token);
            }

            // Refresh if expired
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }

            $service = new Calendar($client);

            // Build start/end times (30-min slot)
            $startDt = new \DateTime("{$data['date']} {$data['time']}");
            $endDt   = (clone $startDt)->modify('+30 minutes');

            $event = new Event([
                'summary'     => "FiscTech Demo — {$data['company']}",
                'description' => implode("\n", [
                    "Company: {$data['company']}",
                    "Address: {$data['address']}",
                    "Contact: {$data['contact']}",
                    "Phone: {$data['contact_phone']}",
                    "Primary: {$data['primary']}",
                    "WhatsApp: {$data['whatsapp']}",
                ]),
                'start' => new EventDateTime([
                    'dateTime' => $startDt->format(\DateTime::RFC3339),
                    'timeZone' => 'Africa/Harare',
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $endDt->format(\DateTime::RFC3339),
                    'timeZone' => 'Africa/Harare',
                ]),
                'attendees' => [
                    new EventAttendee(['email' => $data['email']]),
                ],
                'reminders' => new EventReminders([
                    'useDefault' => false,
                    'overrides'  => [
                        new EventReminder(['method' => 'popup', 'minutes' => 15]),
                        new EventReminder(['method' => 'email',  'minutes' => 15]),
                    ],
                ]),
                'sendUpdates' => 'all', // sends invite email to attendees
            ]);

            $service->events->insert('primary', $event, ['sendUpdates' => 'all']);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Google Calendar booking failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}