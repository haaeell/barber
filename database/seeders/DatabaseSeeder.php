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
use App\Models\BookingService;
use App\Models\Review;
use Carbon\Carbon;

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
            ['BUDI',   'budi@barbershop.com',   'Senior Barber', 55000],
            ['DAFI',   'dafi@barbershop.com',   'Barber',        45000],
            ['ABED',   'abed@barbershop.com',   'Barber',        45000],
            ['ADI',    'adi@barbershop.com',    'Barber',        45000],
            ['ARI',    'ari@barbershop.com',    'Barber',        40000],
            ['BIMBIM', 'bimbim@barbershop.com', 'Barber',        40000],
            ['JATI',   'jati@barbershop.com',   'Junior Barber', 25000],
        ];

        foreach ($barbers as $index => $b) {
            $user = User::create([
                'name'     => 'By ' . $b[0],
                'email'    => $b[1],
                'phone'    => '081600000' . ($index + 1),
                'password' => Hash::make('password'),
                'role'     => 'barber',
            ]);

            Barber::create([
                'user_id'    => $user->id,
                'nickname'   => $b[0],
                'speciality' => $b[2],
                'price'      => $b[3],
                'is_active'  => true,
            ]);
        }
    }


    /* -------------------------------------------------------------------------- */
    /*  SERVICES                                                                  */
    /* -------------------------------------------------------------------------- */
    private function createServices()
    {
        $services = [
            [
                'Bleaching Coloring',
                'bleaching.jpg',
                65000,
                45,
                'Pewarnaan rambut (harga per step)',
            ],
            [
                'Hair Creambath',
                'creambath.jpg',
                40000,
                30,
                'Hairmask + pijat kepala 15 menit',
            ],
            [
                'Long Hair',
                'long-hair.jpg',
                20000,
                10,
                'Tambahan untuk rambut lebih dari 15 cm',
            ],
            [
                'Long Trim',
                'long-trim.jpg',
                10000,
                10,
                'Tambahan trim lebih dari 5 cm',
            ],
            [
                'Washing',
                'washing.jpg',
                10000,
                15,
                'Keramas, vitamin, dan styling',
            ],
            [
                'Shaving',
                'shaving.jpg',
                20000,
                20,
                'Jenggot / kumis (mulai 5k – 20k)',
            ],
            [
                'Booking Fee',
                'booking-fee.jpg',
                5000,
                5,
                'Biaya booking',
            ],
            [
                'Home Service',
                'home-service.jpg',
                75000,
                0,
                'Layanan panggilan ke rumah',
            ],
            [
                'Curly Perm',
                'curly-perm.jpg',
                250000,
                90,
                'Keriting rambut',
            ],
            [
                'K-Perm',
                'k-perm.jpg',
                250000,
                90,
                'Rambut bergelombang',
            ],
            [
                'Haircut',
                'k-perm.jpg',
                0,
                30,
                'Pijat kepala',
            ],
        ];

        foreach ($services as $s) {
            Service::create([
                'name'        => $s[0],
                'image'       => $s[1],
                'price'       => $s[2],
                'duration'    => $s[3],
                'description' => $s[4],
            ]);
        }
    }


    private function createBarberWeeklyShifts()
    {
        $barbers = Barber::pluck('id')->values();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $totalBarbers = $barbers->count();

        if ($totalBarbers < 2) {
            return;
        }

        BarberShift::truncate();

        $startTime = '09:00:00';
        $endTime   = '18:00:00';

        /**
         * Generate 4 minggu
         */
        for ($week = 1; $week <= 4; $week++) {

            foreach ($days as $dayIndex => $day) {
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
}
