<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Users\Services\UserService;
use Livewire\Attributes\On;
use Livewire\Component;

class UserForm extends Component
{
    public ?int $userId = null;
    public string $nama = '';
    public string $username = '';
    public string $email = '';
    public ?int $roleId = null;
    public bool $isAktif = true;

    public ?int $kabupatenId = null;
    public ?int $kecamatanId = null;
    public ?int $kelurahanId = null;
    public ?int $rwId = null;
    public ?int $rtId = null;

    public ?string $passwordSementara = null;
    public bool $showPasswordModal = false;

    protected function rules(): array
    {
        return [
            'nama'    => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username,' . $this->userId,
            'email'   => 'nullable|email|max:100',
            'roleId'  => 'required|exists:roles,id',
            'isAktif' => 'nullable|boolean',
        ];
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    #[On('edit-user')]
    public function editUser(int $id): void
    {
        $this->resetForm();
        $this->userId = $id;

        $user = User::find($id);
        if ($user) {
            $this->nama     = $user->nama;
            $this->username = $user->username;
            $this->email    = (string) ($user->email ?? '');
            $this->roleId   = $user->role_id;
            $this->isAktif  = (bool) $user->is_aktif;

            if ($user->wilayah_id) {
                $this->rtId = $user->wilayah_id;
                $rt = Wilayah::find($this->rtId);
                if ($rt) {
                    $this->rwId = $rt->parent_id;
                    $rw = Wilayah::find($this->rwId);
                    if ($rw) {
                        $this->kelurahanId = $rw->parent_id;
                        $kel = Wilayah::find($this->kelurahanId);
                        if ($kel) {
                            $this->kecamatanId = $kel->parent_id;
                            $kec = Wilayah::find($this->kecamatanId);
                            if ($kec) {
                                $this->kabupatenId = $kec->parent_id;
                            }
                        }
                    }
                }
            }
        }
    }

    private function resetForm(): void
    {
        $this->userId = null;
        $this->nama = '';
        $this->username = '';
        $this->email = '';
        $this->roleId = null;
        $this->isAktif = true;
        $this->kabupatenId = null;
        $this->kecamatanId = null;
        $this->kelurahanId = null;
        $this->rwId = null;
        $this->rtId = null;
        $this->passwordSementara = null;
        $this->showPasswordModal = false;
        $this->resetErrorBag();
    }

    public function updated(string $propertyName): void
    {
        if ($propertyName === 'kabupatenId') {
            $this->kecamatanId = null;
            $this->kelurahanId = null;
            $this->rwId = null;
            $this->rtId = null;
        } elseif ($propertyName === 'kecamatanId') {
            $this->kelurahanId = null;
            $this->rwId = null;
            $this->rtId = null;
        } elseif ($propertyName === 'kelurahanId') {
            $this->rwId = null;
            $this->rtId = null;
        } elseif ($propertyName === 'rwId') {
            $this->rtId = null;
        } elseif ($propertyName === 'roleId') {
            $this->roleId = $this->roleId ? (int) $this->roleId : null;
            $role = $this->roleId ? Role::find($this->roleId) : null;
            if ($role && $role->kode !== 'surveyor') {
                $this->kabupatenId = null;
                $this->kecamatanId = null;
                $this->kelurahanId = null;
                $this->rwId = null;
                $this->rtId = null;
            }
        }

        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $validated = $this->validate();

        $userService = app(UserService::class);
        $auditLogger = app(AuditLogger::class);

        $data = [
            'nama'     => $validated['nama'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'role_id'  => $validated['roleId'],
            'is_aktif' => filter_var($validated['isAktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        $role = Role::find((int) $validated['roleId']);
        if ($role && $role->kode === 'surveyor') {
            if (!$this->rtId) {
                $this->addError('rtId', 'Surveyor wajib memilih wilayah tugas hingga RT.');
                return;
            }
            $data['wilayah_id'] = $this->rtId;
        } else {
            $data['wilayah_id'] = null;
        }

        if ($this->userId) {
            $user = User::find($this->userId);
            $userService->updateUser($user, $data);
            $auditLogger->catat(AuditLog::AKSI['USER_UPDATE'], 'users', $user->id);
            session()->flash('success', 'User berhasil diperbarui.');
            return $this->redirect(route('users.index'), navigate: true);
        }

        $result = $userService->createUser($data);
        $this->passwordSementara = $result['password_plaintext'];
        $this->showPasswordModal = true;
        $auditLogger->catat(AuditLog::AKSI['USER_CREATE'], 'users', $result['user']->id);
        session()->flash('success', 'User berhasil dibuat.');
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        return $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        $roles = Role::orderBy('nama')->get();
        $kabupatenList = Wilayah::where('tingkat', Wilayah::TINGKAT_KABUPATEN_KOTA)->orderBy('nama')->get();
        $kecamatanList = collect();
        $kelurahanList = collect();
        $rwList = collect();
        $rtList = collect();

        if ($this->kabupatenId) {
            $kecamatanList = Wilayah::where('parent_id', $this->kabupatenId)->orderBy('nama')->get();
        }
        if ($this->kecamatanId) {
            $kelurahanList = Wilayah::where('parent_id', $this->kecamatanId)->orderBy('nama')->get();
        }
        if ($this->kelurahanId) {
            $rwList = Wilayah::where('parent_id', $this->kelurahanId)->orderBy('nama')->get();
        }
        if ($this->rwId) {
            $rtList = Wilayah::where('parent_id', $this->rwId)->orderBy('nama')->get();
        }

        $role = $this->roleId ? Role::find((int) $this->roleId) : null;
        $isSurveyor = $role && $role->kode === 'surveyor';

        return view('livewire.user-form', compact(
            'roles', 'kabupatenList', 'kecamatanList', 'kelurahanList', 'rwList', 'rtList', 'isSurveyor'
        ));
    }
}
