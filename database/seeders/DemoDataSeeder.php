<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\PricingQuote;
use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $aliProvider     = Provider::whereHas('user', fn ($q) => $q->where('email', 'ali-ac-services@provider.test'))->first();
        $frostProvider   = Provider::whereHas('user', fn ($q) => $q->where('email', 'frostmaster-ac@provider.test'))->first();
        $coolProvider    = Provider::whereHas('user', fn ($q) => $q->where('email', 'cool-breeze-hvac@provider.test'))->first();
        $khiPlumber      = Provider::where('name', 'KHI Plumbers')->first();
        $brightSparks    = Provider::where('name', 'Bright Sparks')->first();
        $homeClean       = Provider::where('name', 'HomeClean Pro')->first();

        // ── 1. Completed booking (with feedback path available) ───────────────
        $sr1 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'AC not cooling properly, need technician at Gulshan',
            'detected_lang'     => 'english',
            'service_type'      => 'AC Technician',
            'location'          => 'Gulshan-e-Iqbal',
            'user_lat'          => 24.9260,
            'user_lng'          => 67.0930,
            'urgency'           => 'normal',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->subDays(10)->format('Y-m-d 10:00:00'),
            'issue_severity'    => 'medium',
            'confidence'        => 0.94,
            'complexity'        => 'basic',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q1 = PricingQuote::create([
            'provider_id'      => $aliProvider->id,
            'base_rate'        => 1200,
            'visit_fee'        => 150,
            'distance_cost'    => 63,
            'urgency_adj'      => 0,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 0,
            'total'            => 1413,
            'provider_net'     => 1201,
            'is_budget_tier'   => false,
        ]);

        $b1 = Booking::create([
            'booking_ref'        => 'BK-' . strtoupper(Str::random(5)),
            'user_id'            => $user->id,
            'provider_id'        => $aliProvider->id,
            'service_request_id' => $sr1->id,
            'pricing_quote_id'   => $q1->id,
            'slot_datetime'      => now()->subDays(10)->setTime(10, 0),
            'slot_end_datetime'  => now()->subDays(10)->setTime(11, 0),
            'complexity'         => 'basic',
            'status'             => 'completed',
            'simulated'          => true,
            'confirmed_at'       => now()->subDays(10),
            'completed_at'       => now()->subDays(10)->setTime(11, 0),
            'agent_run_id'       => $sr1->agent_run_id,
        ]);

        $q1->update(['booking_id' => $b1->id]);
        $user->increment('booking_count');

        // ── 2. Confirmed booking (upcoming) ──────────────────────────────────
        $sr2 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'Mujhe kal subah Clifton mein AC technician chahiye',
            'detected_lang'     => 'roman_urdu',
            'service_type'      => 'AC Technician',
            'location'          => 'Clifton',
            'user_lat'          => 24.8172,
            'user_lng'          => 67.0320,
            'urgency'           => 'normal',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->addDays(2)->format('Y-m-d 09:00:00'),
            'issue_severity'    => 'low',
            'confidence'        => 0.91,
            'complexity'        => 'intermediate',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q2 = PricingQuote::create([
            'provider_id'      => $frostProvider->id,
            'base_rate'        => 1500,
            'visit_fee'        => 150,
            'distance_cost'    => 45,
            'urgency_adj'      => 0,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 0,
            'total'            => 1695,
            'provider_net'     => 1441,
            'is_budget_tier'   => false,
        ]);

        $b2 = Booking::create([
            'booking_ref'        => 'BK-' . strtoupper(Str::random(5)),
            'user_id'            => $user->id,
            'provider_id'        => $frostProvider->id,
            'service_request_id' => $sr2->id,
            'pricing_quote_id'   => $q2->id,
            'slot_datetime'      => now()->addDays(2)->setTime(9, 0),
            'slot_end_datetime'  => now()->addDays(2)->setTime(10, 0),
            'complexity'         => 'intermediate',
            'status'             => 'confirmed',
            'simulated'          => true,
            'confirmed_at'       => now()->subHours(3),
            'agent_run_id'       => $sr2->agent_run_id,
        ]);

        $q2->update(['booking_id' => $b2->id]);
        $user->increment('booking_count');

        // ── 3. Pending booking ────────────────────────────────────────────────
        $sr3 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'I need a plumber in PECHS urgently, pipe leaking',
            'detected_lang'     => 'english',
            'service_type'      => 'Plumber',
            'location'          => 'PECHS',
            'user_lat'          => 24.8697,
            'user_lng'          => 67.0647,
            'urgency'           => 'high',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->addDays(1)->format('Y-m-d 08:00:00'),
            'issue_severity'    => 'high',
            'confidence'        => 0.89,
            'complexity'        => 'basic',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q3 = PricingQuote::create([
            'provider_id'      => $khiPlumber->id,
            'base_rate'        => 800,
            'visit_fee'        => 150,
            'distance_cost'    => 30,
            'urgency_adj'      => 500,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 0,
            'total'            => 1480,
            'provider_net'     => 1258,
            'is_budget_tier'   => false,
        ]);

        $b3 = Booking::create([
            'booking_ref'        => 'BK-' . strtoupper(Str::random(5)),
            'user_id'            => $user->id,
            'provider_id'        => $khiPlumber->id,
            'service_request_id' => $sr3->id,
            'pricing_quote_id'   => $q3->id,
            'slot_datetime'      => now()->addDays(1)->setTime(8, 0),
            'slot_end_datetime'  => now()->addDays(1)->setTime(9, 0),
            'complexity'         => 'basic',
            'status'             => 'pending',
            'simulated'          => true,
            'agent_run_id'       => $sr3->agent_run_id,
        ]);

        $q3->update(['booking_id' => $b3->id]);
        $user->increment('booking_count');

        // ── 4. En Route booking (today) ───────────────────────────────────────
        $sr4 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'Bijli ki wiring check karni hai, Scheme 33',
            'detected_lang'     => 'roman_urdu',
            'service_type'      => 'Electrician',
            'location'          => 'Scheme 33',
            'user_lat'          => 24.9481,
            'user_lng'          => 67.1294,
            'urgency'           => 'normal',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->format('Y-m-d 14:00:00'),
            'issue_severity'    => 'medium',
            'confidence'        => 0.87,
            'complexity'        => 'intermediate',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q4 = PricingQuote::create([
            'provider_id'      => $brightSparks->id,
            'base_rate'        => 950,
            'visit_fee'        => 150,
            'distance_cost'    => 20,
            'urgency_adj'      => 0,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 0,
            'total'            => 1120,
            'provider_net'     => 952,
            'is_budget_tier'   => false,
        ]);

        $b4 = Booking::create([
            'booking_ref'        => 'BK-' . strtoupper(Str::random(5)),
            'user_id'            => $user->id,
            'provider_id'        => $brightSparks->id,
            'service_request_id' => $sr4->id,
            'pricing_quote_id'   => $q4->id,
            'slot_datetime'      => now()->setTime(14, 0),
            'slot_end_datetime'  => now()->setTime(15, 0),
            'complexity'         => 'intermediate',
            'status'             => 'en_route',
            'simulated'          => true,
            'confirmed_at'       => now()->subHours(1),
            'agent_run_id'       => $sr4->agent_run_id,
        ]);

        $q4->update(['booking_id' => $b4->id]);
        $user->increment('booking_count');

        // ── 5. Disputed booking ───────────────────────────────────────────────
        $sr5 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'Home deep cleaning needed in Clifton',
            'detected_lang'     => 'english',
            'service_type'      => 'Cleaning Service',
            'location'          => 'Clifton',
            'user_lat'          => 24.8172,
            'user_lng'          => 67.0320,
            'urgency'           => 'normal',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->subDays(5)->format('Y-m-d 11:00:00'),
            'issue_severity'    => 'low',
            'confidence'        => 0.92,
            'complexity'        => 'basic',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q5 = PricingQuote::create([
            'provider_id'      => $homeClean->id,
            'base_rate'        => 1500,
            'visit_fee'        => 150,
            'distance_cost'    => 50,
            'urgency_adj'      => 0,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 0,
            'total'            => 1700,
            'provider_net'     => 1445,
            'is_budget_tier'   => false,
        ]);

        $b5 = Booking::create([
            'booking_ref'        => 'BK-' . strtoupper(Str::random(5)),
            'user_id'            => $user->id,
            'provider_id'        => $homeClean->id,
            'service_request_id' => $sr5->id,
            'pricing_quote_id'   => $q5->id,
            'slot_datetime'      => now()->subDays(5)->setTime(11, 0),
            'slot_end_datetime'  => now()->subDays(5)->setTime(12, 0),
            'complexity'         => 'basic',
            'status'             => 'disputed',
            'simulated'          => true,
            'confirmed_at'       => now()->subDays(5),
            'completed_at'       => now()->subDays(5)->setTime(12, 0),
            'agent_run_id'       => $sr5->agent_run_id,
        ]);

        $q5->update(['booking_id' => $b5->id]);
        $user->increment('booking_count');

        Dispute::create([
            'booking_id'       => $b5->id,
            'user_id'          => $user->id,
            'trigger_type'     => 'quality',
            'description'      => 'Cleaner did not clean the kitchen properly and left early without finishing the job.',
            'stage'            => 1,
            'resolution_offer' => 'We can offer a 20% refund or a free re-service within 48 hours.',
            'outcome'          => null,
            'human_flag'       => false,
        ]);

        // ── 6. Cancelled booking ──────────────────────────────────────────────
        $sr6 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'Ghar mein carpenter chahiye, almari banana hai',
            'detected_lang'     => 'roman_urdu',
            'service_type'      => 'Carpenter',
            'location'          => 'North Karachi',
            'user_lat'          => 24.9876,
            'user_lng'          => 67.0700,
            'urgency'           => 'normal',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->subDays(3)->format('Y-m-d 10:00:00'),
            'issue_severity'    => 'low',
            'confidence'        => 0.88,
            'complexity'        => 'basic',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q6 = PricingQuote::create([
            'provider_id'      => $coolProvider->id,
            'base_rate'        => 1050,
            'visit_fee'        => 150,
            'distance_cost'    => 40,
            'urgency_adj'      => 0,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 0,
            'total'            => 1240,
            'provider_net'     => 1054,
            'is_budget_tier'   => false,
        ]);

        $b6 = Booking::create([
            'booking_ref'           => 'BK-' . strtoupper(Str::random(5)),
            'user_id'               => $user->id,
            'provider_id'           => $coolProvider->id,
            'service_request_id'    => $sr6->id,
            'pricing_quote_id'      => $q6->id,
            'slot_datetime'         => now()->subDays(3)->setTime(10, 0),
            'slot_end_datetime'     => now()->subDays(3)->setTime(11, 0),
            'complexity'            => 'basic',
            'status'                => 'cancelled',
            'simulated'             => true,
            'cancellation_reason'   => 'Changed my mind, found another provider.',
            'agent_run_id'          => $sr6->agent_run_id,
        ]);

        $q6->update(['booking_id' => $b6->id]);

        // ── 7. Completed booking (loyalty discount example) ───────────────────
        $sr7 = ServiceRequest::create([
            'user_id'           => $user->id,
            'raw_input'         => 'AC gas refill needed in Gulshan block 7',
            'detected_lang'     => 'english',
            'service_type'      => 'AC Technician',
            'location'          => 'Gulshan-e-Iqbal',
            'user_lat'          => 24.9260,
            'user_lng'          => 67.0930,
            'urgency'           => 'normal',
            'budget_sensitivity'=> 'normal',
            'requested_datetime'=> now()->subDays(20)->format('Y-m-d 09:00:00'),
            'issue_severity'    => 'medium',
            'confidence'        => 0.96,
            'complexity'        => 'basic',
            'agent_run_id'      => Str::uuid(),
        ]);

        $q7 = PricingQuote::create([
            'provider_id'      => $aliProvider->id,
            'base_rate'        => 1200,
            'visit_fee'        => 150,
            'distance_cost'    => 63,
            'urgency_adj'      => 0,
            'surge_factor'     => 1.0,
            'loyalty_discount' => 70,
            'total'            => 1343,
            'provider_net'     => 1141,
            'is_budget_tier'   => false,
        ]);

        $b7 = Booking::create([
            'booking_ref'        => 'BK-' . strtoupper(Str::random(5)),
            'user_id'            => $user->id,
            'provider_id'        => $aliProvider->id,
            'service_request_id' => $sr7->id,
            'pricing_quote_id'   => $q7->id,
            'slot_datetime'      => now()->subDays(20)->setTime(9, 0),
            'slot_end_datetime'  => now()->subDays(20)->setTime(10, 0),
            'complexity'         => 'basic',
            'status'             => 'completed',
            'simulated'          => true,
            'confirmed_at'       => now()->subDays(20),
            'completed_at'       => now()->subDays(20)->setTime(10, 0),
            'agent_run_id'       => $sr7->agent_run_id,
        ]);

        $q7->update(['booking_id' => $b7->id]);
        $user->increment('booking_count');

        $this->command->info('Demo data seeded: 7 bookings (completed ×2, confirmed, pending, en_route, disputed, cancelled) + 1 dispute.');
    }
}
