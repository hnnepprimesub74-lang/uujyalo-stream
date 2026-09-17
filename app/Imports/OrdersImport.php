<?php

namespace App\Imports;

use App\Models\Plan;
use App\Models\Product;
use App\Models\SaleSource;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrdersImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    /** @var array<int, array{row: int, reason: string}> */
    public array $skipped = [];

    public function collection(SupportCollection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // account for heading row + 0-index

            try {
                $this->importRow($row, $rowNumber);
            } catch (\Throwable $e) {
                $this->skipped[] = ['row' => $rowNumber, 'reason' => $e->getMessage()];
            }
        }
    }

    protected function importRow(SupportCollection $row, int $rowNumber): void
    {
        $name = $this->value($row, ['name', 'customer_name']);
        $email = $this->value($row, ['email', 'customer_email']);
        $phone = $this->value($row, ['number', 'phone', 'customer_number', 'customer_phone']);
        $productName = $this->value($row, ['product']);
        $planName = $this->value($row, ['plan', 'plan_name']);
        $accountEmail = $this->value($row, ['account_email']) ?: $email;
        $accountPassword = $this->value($row, ['password', 'account_password']);
        $subscriptionDate = $this->date($this->value($row, ['subscription_date', 'start_date']));
        $expiryDate = $this->date($this->value($row, ['expiry_date', 'end_date']));
        $totalDays = $this->value($row, ['total_days', 'recharge_added_in_days', 'days']);
        $status = strtolower((string) ($this->value($row, ['status']) ?: 'active'));
        $sourceName = $this->value($row, ['source', 'sale_source', 'source_of_sale']);

        if (empty($name) || empty($email)) {
            throw new \RuntimeException('Missing required Name or Email.');
        }

        if (empty($productName)) {
            throw new \RuntimeException('Missing Product.');
        }

        if (! $subscriptionDate) {
            $subscriptionDate = Carbon::today();
        }

        if (! $expiryDate && $totalDays) {
            $expiryDate = $subscriptionDate->copy()->addDays((int) $totalDays);
        }

        if (! $totalDays && $expiryDate) {
            $totalDays = $subscriptionDate->diffInDays($expiryDate);
        }

        if (! $expiryDate) {
            throw new \RuntimeException('Missing Expiry Date (or Total Days to derive it).');
        }

        $product = Product::firstOrCreate(
            ['slug' => Str::slug($productName)],
            ['name' => $productName, 'is_active' => true]
        );

        $planName = $planName ?: 'Standard';
        $plan = Plan::firstOrCreate(
            ['product_id' => $product->id, 'slug' => Str::slug($productName.'-'.$planName)],
            [
                'name' => $planName,
                'duration_days' => (int) $totalDays,
                'price' => 0,
                'monthly_cost' => 0,
                'is_active' => true,
            ]
        );

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make(Str::random(16)),
                'role' => User::ROLE_CUSTOMER,
            ]
        );

        if ($phone && empty($user->phone)) {
            $user->update(['phone' => $phone]);
        }

        $saleSource = $sourceName
            ? SaleSource::firstOrCreate(['name' => $sourceName], ['is_active' => true])
            : null;

        $isSharedAccount = (bool) $product->is_shared_account;

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'sale_source_id' => $saleSource?->id,
            'status' => in_array($status, [
                Subscription::STATUS_ACTIVE, Subscription::STATUS_EXPIRED,
                Subscription::STATUS_CANCELLED, Subscription::STATUS_PENDING,
            ]) ? $status : Subscription::STATUS_ACTIVE,
            'starts_at' => $subscriptionDate,
            'expires_at' => $expiryDate,
            'amount' => 0,
            'account_email' => $accountEmail,
            'account_password' => $accountPassword,
            'total_days' => (int) $totalDays,
            'days_recharged' => $isSharedAccount ? (int) $totalDays : 0,
            'next_recharge_date' => $isSharedAccount ? null : $subscriptionDate->toDateString(),
        ]);

        $this->imported++;
    }

    protected function value(SupportCollection $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if ($row->has($key) && filled($row->get($key))) {
                return trim((string) $row->get($key));
            }
        }

        return null;
    }

    protected function date(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
