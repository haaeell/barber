<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\BarberShift;
use App\Models\Review;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->createOwner();
        $this->createAdmins();
        $this->createCustomers();
        $this->createBarbers();
        $this->createServices();
        $this->createBarberWeeklyShifts();
        $this->createDummyBookings();
        $this->createDummyReviews();
    }

    /* -------------------------------------------------------------------------- */
    /*  OWNER                                                                     */
    /* -------------------------------------------------------------------------- */
    private function createOwner()
    {
        User::create([
            'name' => 'Barbershop Owner',
            'email' => 'owner@barbershop.com',
            'phone' => '08000000001',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /*  ADMINS                                                                    */
    /* -------------------------------------------------------------------------- */
    private function createAdmins()
    {
        User::create([
            'name' => 'Admin One',
            'email' => 'admin@barbershop.com',
            'phone' => '08000000002',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /*  CUSTOMERS                                                                 */
    /* -------------------------------------------------------------------------- */
    private function createCustomers()
    {
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Customer $i",
                'email' => "customer$i@mail.com",
                'phone' => "0887000000$i",
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  BARBERS (User + Barber table)                                             */
    /* -------------------------------------------------------------------------- */
    private function createBarbers()
    {
        $barbers = [
            ['Evan Barber', 'evan@barbershop.com', 'Fade Specialist', 10000],
            ['Rio Barber', 'rio@barbershop.com', 'Classic Cut', 15000],
            ['Dion Barber', 'dion@barbershop.com', 'Kids Cut', 8000],
        ];

        foreach ($barbers as $index => $b) {
            $user = User::create([
                'name' => $b[0],
                'email' => $b[1],
                'phone' => '0815000000' . ($index + 1),
                'password' => Hash::make('password'),
                'role' => 'barber',
            ]);

            Barber::create([
                'user_id' => $user->id,
                'nickname' => explode(' ', $b[0])[0],
                'speciality' => $b[2],
                'is_active' => true,
                'price' => $b[3], // Sesuai migration
            ]);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  SERVICES                                                                  */
    /* -------------------------------------------------------------------------- */
    private function createServices()
    {
        $services = [
            ['Classic Haircut', 'haircut.jpg', 30000, 30, 'Standard haircut for men'],
            ['Premium Haircut', 'premium.jpg', 50000, 45, 'Premium style haircut'],
            ['Kids Haircut', 'kids.jpg', 25000, 25, 'Haircut for kids under 12'],
            ['Shaving', 'shaving.jpg', 20000, 20, 'Clean shaving service'],
            ['Hair Color', 'color.jpg', 150000, 60, 'Full hair coloring service'],
        ];

        foreach ($services as $s) {
            Service::create([
                'name' => $s[0],
                'image' => $s[1],
                'price' => $s[2],
                'duration' => $s[3],
                'description' => $s[4],
            ]);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  WEEKLY BARBER SHIFTS                                                      */
    /* -------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------- */
    /*  BARBER SHIFTS – 4 WEEK ROLLING                                             */
    /* -------------------------------------------------------------------------- */
    private function createBarberWeeklyShifts()
    {
        $barbers = Barber::pluck('id')->values();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $totalBarbers = $barbers->count();

        if ($totalBarbers < 2) {
            return;
        }

        // bersihin dulu biar aman saat re-seed
        BarberShift::truncate();

        $startTime = '09:00:00';
        $endTime   = '18:00:00';

        /**
         * Generate 4 minggu
         */
        for ($week = 1; $week <= 4; $week++) {

            foreach ($days as $dayIndex => $day) {

                /**
                 * 1 barber libur per hari
                 * Rolling adil tiap minggu
                 */
                $offIndex = ($dayIndex + ($week - 1)) % $totalBarbers;
                $offBarberId = $barbers[$offIndex];

                foreach ($barbers as $barberId) {

                    $isWorking = $barberId != $offBarberId;

                    BarberShift::create([
                        'barber_id'   => $barberId,
                        'week_number' => $week,
                        'day_of_week' => $day,
                        'start_time'  => $isWorking ? $startTime : null,
                        'end_time'    => $isWorking ? $endTime : null,
                        'is_day_off'  => !$isWorking,
                    ]);
                }
            }
        }
    }


    /* -------------------------------------------------------------------------- */
    /*  DUMMY BOOKINGS (Fix untuk kolom baru)                                     */
    /* -------------------------------------------------------------------------- */
    private function createDummyBookings()
    {
        $customers = User::where('role', 'customer')->get();
        $barbers = Barber::all();
        $services = Service::all();

        foreach ($customers as $customer) {

            $barber = $barbers->random();
            $service = $services->random();

            Booking::create([
                'booking_code'   => strtoupper(Str::random(6)),
                'user_id'        => $customer->id,
                'barber_id'      => $barber->id,
                'service_id'     => $service->id,
                'date'           => now()->addDays(rand(0, 3))->format('Y-m-d'),
                'time'           => rand(9, 17) . ':00:00',
                'status'         => 'confirmed',
                'payment_method' => 'cash',
                'payment_status' => 'paid',

                // FIX SESUAI MIGRATION
                'service_price'  => $service->price,
                'barber_price'   => $barber->price,
                'total_price'    => $service->price + $barber->price,
            ]);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*  DUMMY REVIEWS                                                             */
    /* -------------------------------------------------------------------------- */
    private function createDummyReviews()
    {
        $bookings = Booking::all();

        foreach ($bookings as $booking) {
            Review::create([
                'booking_id' => $booking->id,
                'user_id'    => $booking->user_id,
                'barber_id'  => $booking->barber_id,
                'rating'     => rand(4, 5),
                'comment'    => 'Great service! Recommended barber.',
            ]);
        }
    }
}
