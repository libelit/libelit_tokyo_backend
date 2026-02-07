<?php

namespace App\Traits;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasWallet
{
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'walletable');
    }

    public function primaryWallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable')->where('is_primary', true);
    }

    public function verifiedWallets(): MorphMany
    {
        return $this->wallets()->where('is_verified', true);
    }

    public function hasWallet(): bool
    {
        return $this->wallets()->exists();
    }

    public function hasVerifiedWallet(): bool
    {
        return $this->verifiedWallets()->exists();
    }

    public function getXrplAddress(): ?string
    {
        return $this->primaryWallet?->xrpl_address;
    }
}
