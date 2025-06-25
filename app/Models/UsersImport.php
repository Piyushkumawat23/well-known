<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            User::create([
                'referred_by' => $row['referred_by'] ?? null,
                'provider_id' => $row['provider_id'] ?? null,
                'user_type' => $row['user_type'] ?? 'customer',
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['password'] ?? 'password'),
                'phone' => $row['phone'] ?? null,
                'address' => $row['address'] ?? null,
                'country' => $row['country'] ?? null,
                'state' => $row['state'] ?? null,
                'city' => $row['city'] ?? null,
                'postal_code' => $row['postal_code'] ?? null,
                'referral_code' => $row['referral_code'] ?? null,
                'tax_id' => $row['tax_id'] ?? null,
                'business_name' => $row['business_name'] ?? null,
                'instagram' => $row['instagram'] ?? null,
                'twitter' => $row['twitter'] ?? null,
                'facebook' => $row['facebook'] ?? null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string',
        ];
    }
}
