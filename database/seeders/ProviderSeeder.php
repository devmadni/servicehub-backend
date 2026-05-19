<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $acCat = Category::where('slug', 'ac-technician')->first()->id;
        $plumbCat = Category::where('slug', 'plumber')->first()->id;
        $elecCat = Category::where('slug', 'electrician')->first()->id;
        $cleanCat = Category::where('slug', 'cleaning-service')->first()->id;
        $tutorCat = Category::where('slug', 'tutor')->first()->id;

        $providers = [
            ['name' => 'Ali AC Services', 'area' => 'Gulshan-e-Iqbal', 'lat' => 24.9260, 'lng' => 67.0930,
                'rating_avg' => 4.8, 'on_time_score' => 94, 'cancel_rate' => 3, 'experience_years' => 8,
                'specializations' => ['Inverter', 'Split AC', 'Commercial'], 'price_min' => 1200,
                'capacity_current' => 1, 'risk_score' => 0.05, 'category_id' => $acCat],

            ['name' => 'Cool Breeze HVAC', 'area' => 'North Karachi', 'lat' => 24.9876, 'lng' => 67.0700,
                'rating_avg' => 4.5, 'on_time_score' => 79, 'cancel_rate' => 11, 'experience_years' => 4,
                'specializations' => ['Split AC', 'Window AC'], 'price_min' => 1050,
                'capacity_current' => 2, 'risk_score' => 0.18, 'category_id' => $acCat],

            ['name' => 'FrostMaster AC', 'area' => 'Clifton', 'lat' => 24.8172, 'lng' => 67.0320,
                'rating_avg' => 4.9, 'on_time_score' => 97, 'cancel_rate' => 1, 'experience_years' => 12,
                'specializations' => ['Inverter', 'Commercial HVAC', 'Split AC'], 'price_min' => 1500,
                'capacity_current' => 0, 'risk_score' => 0.02, 'category_id' => $acCat],

            ['name' => 'KHI Plumbers', 'area' => 'PECHS', 'lat' => 24.8697, 'lng' => 67.0647,
                'rating_avg' => 4.5, 'on_time_score' => 88, 'cancel_rate' => 7, 'experience_years' => 10,
                'specializations' => ['Drain', 'Pipe repair', 'Leak detection'], 'price_min' => 800,
                'capacity_current' => 0, 'risk_score' => 0.08, 'category_id' => $plumbCat],

            ['name' => 'Quick Plumb', 'area' => 'Malir', 'lat' => 24.8940, 'lng' => 67.1760,
                'rating_avg' => 3.8, 'on_time_score' => 71, 'cancel_rate' => 18, 'experience_years' => 2,
                'specializations' => ['Basic repairs'], 'price_min' => 600,
                'capacity_current' => 1, 'risk_score' => 0.35, 'category_id' => $plumbCat],

            ['name' => 'AquaFix Karachi', 'area' => 'DHA Phase 2', 'lat' => 24.8250, 'lng' => 67.0400,
                'rating_avg' => 4.7, 'on_time_score' => 91, 'cancel_rate' => 4, 'experience_years' => 7,
                'specializations' => ['Drain', 'Pipe repair', 'Waterproofing'], 'price_min' => 900,
                'capacity_current' => 0, 'risk_score' => 0.06, 'category_id' => $plumbCat],

            ['name' => 'Bright Sparks', 'area' => 'Scheme 33', 'lat' => 24.9481, 'lng' => 67.1294,
                'rating_avg' => 4.9, 'on_time_score' => 96, 'cancel_rate' => 2, 'experience_years' => 12,
                'specializations' => ['Wiring', 'Industrial', 'Solar'], 'price_min' => 950,
                'capacity_current' => 0, 'risk_score' => 0.03, 'category_id' => $elecCat],

            ['name' => 'Pak Electric Works', 'area' => 'DHA Phase 6', 'lat' => 24.8118, 'lng' => 67.0681,
                'rating_avg' => 4.7, 'on_time_score' => 91, 'cancel_rate' => 4, 'experience_years' => 9,
                'specializations' => ['Industrial', 'CCTV', 'Wiring'], 'price_min' => 900,
                'capacity_current' => 3, 'risk_score' => 0.06, 'category_id' => $elecCat],

            ['name' => 'Voltage Kings', 'area' => 'Gulshan-e-Iqbal', 'lat' => 24.9300, 'lng' => 67.1000,
                'rating_avg' => 4.3, 'on_time_score' => 82, 'cancel_rate' => 9, 'experience_years' => 5,
                'specializations' => ['Residential wiring', 'UPS installation'], 'price_min' => 750,
                'capacity_current' => 1, 'risk_score' => 0.12, 'category_id' => $elecCat],

            ['name' => 'HomeClean Pro', 'area' => 'Clifton', 'lat' => 24.8172, 'lng' => 67.0320,
                'rating_avg' => 4.3, 'on_time_score' => 82, 'cancel_rate' => 9, 'experience_years' => 5,
                'specializations' => ['Deep clean', 'Post-construction'], 'price_min' => 1500,
                'capacity_current' => 1, 'risk_score' => 0.12, 'category_id' => $cleanCat],

            ['name' => 'Sparkle Clean', 'area' => 'North Nazimabad', 'lat' => 24.9490, 'lng' => 67.0500,
                'rating_avg' => 4.6, 'on_time_score' => 89, 'cancel_rate' => 5, 'experience_years' => 3,
                'specializations' => ['Home cleaning', 'Office cleaning'], 'price_min' => 1200,
                'capacity_current' => 0, 'risk_score' => 0.07, 'category_id' => $cleanCat],

            ['name' => 'Karachi Tutors', 'area' => 'Gulistan-e-Johar', 'lat' => 24.9061, 'lng' => 67.1229,
                'rating_avg' => 4.7, 'on_time_score' => 93, 'cancel_rate' => 5, 'experience_years' => 7,
                'specializations' => ['Math', 'Physics', 'Chemistry'], 'price_min' => 600,
                'capacity_current' => 2, 'risk_score' => 0.07, 'category_id' => $tutorCat],

            ['name' => 'EduFirst Academy', 'area' => 'PECHS', 'lat' => 24.8700, 'lng' => 67.0650,
                'rating_avg' => 4.4, 'on_time_score' => 85, 'cancel_rate' => 8, 'experience_years' => 4,
                'specializations' => ['English', 'Biology', 'Math'], 'price_min' => 500,
                'capacity_current' => 1, 'risk_score' => 0.10, 'category_id' => $tutorCat],

            // Blacklisted provider — for dispute stress-test demo
            ['name' => 'Rogue Services', 'area' => 'Korangi', 'lat' => 24.8380, 'lng' => 67.1310,
                'rating_avg' => 2.1, 'on_time_score' => 45, 'cancel_rate' => 40, 'experience_years' => 1,
                'specializations' => ['General'], 'price_min' => 400,
                'capacity_current' => 0, 'risk_score' => 0.95, 'status' => 'blacklisted', 'warning_count' => 3,
                'category_id' => $acCat],
        ];

        foreach ($providers as $data) {
            $status = $data['status'] ?? 'active';
            $warningCount = $data['warning_count'] ?? 0;

            $user = User::firstOrCreate(
                ['email' => Str::slug($data['name']).'@provider.test'],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'provider',
                ]
            );

            Provider::firstOrCreate(
                ['user_id' => $user->id],
                array_merge(
                    collect($data)->except(['status', 'warning_count'])->toArray(),
                    ['status' => $status, 'warning_count' => $warningCount]
                )
            );
        }
    }
}
