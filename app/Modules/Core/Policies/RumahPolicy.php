<?php

namespace App\Modules\Core\Policies;

use App\Models\RumahWarga;
use App\Models\User;

class RumahPolicy
{
    public function view(User $user, RumahWarga $rumah): bool
    {
        if ($user->isVerifikator() || $user->isAdmin() || $user->isPimpinan()) {
            return true;
        }

        if ($user->isSurveyor()) {
            return $this->rumahDalamWilayahSurveyor($user, $rumah);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSurveyor();
    }

    public function update(User $user, RumahWarga $rumah): bool
    {
        if (!$user->isSurveyor()) return false;
        if (!$this->rumahDalamWilayahSurveyor($user, $rumah)) return false;
        if (!$rumah->bisaDiedit()) return false;

        return true;
    }

    public function delete(User $user, RumahWarga $rumah): bool
    {
        if ($user->isAdmin() && $rumah->status === RumahWarga::STATUS_DRAF) {
            return true;
        }
        return false;
    }

    public function nilai(User $user, RumahWarga $rumah): bool
    {
        return $user->isVerifikator();
    }

    public function lihatNikPenuh(User $user): bool
    {
        return $user->isVerifikator() || $user->isAdmin();
    }

    private function rumahDalamWilayahSurveyor(User $surveyor, RumahWarga $rumah): bool
    {
        $wilayahSurveyor = $surveyor->wilayah;
        if (!$wilayahSurveyor) return false;

        if ($rumah->wilayah_id === $wilayahSurveyor->id) return true;

        return str_starts_with($rumah->wilayah->path ?? '', $wilayahSurveyor->path . '/');
    }
}
