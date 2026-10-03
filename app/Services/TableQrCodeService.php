<?php

namespace App\Services;

use App\Models\RestaurantTable;
use App\Models\TableQrCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class TableQrCodeService
{
    public function issue(RestaurantTable $table, bool $regenerate = false): TableQrCode
    {
        return \DB::transaction(function () use ($table, $regenerate) {
            $lockedTable = RestaurantTable::query()
                ->lockForUpdate()
                ->findOrFail($table->id);

            $current = $lockedTable->qrCode()->where('is_active', true)->first();

            if ($current && !$regenerate) {
                return $current;
            }

            $lockedTable->qrCode()->where('is_active', true)->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

            for ($attempt = 0; $attempt < 3; $attempt++) {
                try {
                    return $lockedTable->qrCode()->create([
                        'token' => Str::random(48),
                        'is_active' => true,
                        'generated_at' => now(),
                    ]);
                } catch (QueryException $exception) {
                    if (!$this->looksLikeTokenCollision($exception) || $attempt === 2) {
                        throw $exception;
                    }
                }
            }

            throw new \RuntimeException('Unable to generate a unique table QR token.');
        });
    }

    public function resolve(string $token): ?TableQrCode
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        return TableQrCode::query()
            ->where('token', $token)
            ->where('is_active', true)
            ->whereHas('table', function ($query) {
                $query->where('is_active', true)
                    ->whereHas('restaurant', fn ($restaurant) => $restaurant->where('status', 'active'));
            })
            ->with(['table.restaurant'])
            ->first();
    }

    public function resolveOrFail(string $token): TableQrCode
    {
        $qr = $this->resolve($token);

        if (!$qr) {
            throw (new ModelNotFoundException)->setModel(TableQrCode::class, [$token]);
        }

        return $qr;
    }

    public function scanUrl(TableQrCode $qr): string
    {
        return url('/table/' . rawurlencode($qr->token));
    }

    private function looksLikeTokenCollision(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'table_qr_codes_token_unique');
    }
}
