<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Penugasan;
use App\Models\Reviewer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
 
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getRecord();
        
        if ($user->role === 'REVIEWER') {
            $reviewer = Reviewer::where('user_id', $user->id)->first();
            if ($reviewer) {
                $data['nama_lengkap'] = $reviewer->nama_lengkap;
                $data['nip'] = $reviewer->nip;
            }
        } elseif ($user->role === 'PESERTA') {
            $Penugasan = Penugasan::with('institusi')->where('user_id', $user->id)->first();
            if ($Penugasan) {
                $data['nama_pic'] = $Penugasan->nama_pic;
                $data['jabatan_pic'] = $Penugasan->jabatan_pic;
                $data['no_hp_pic'] = $Penugasan->no_hp_pic;
                if ($Penugasan->institusi) {
                    $data['nama_pt'] = $Penugasan->institusi->nama_institusi;
                    $data['jenis_pt'] = $Penugasan->institusi->jenis_institusi;
                }
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $userData = [
                'email' => $data['email'],
                'role' => $data['role'],
                'status' => $data['status'],
            ];
            
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $record->update($userData);

            if ($data['role'] === 'REVIEWER') {
                $reviewer = Reviewer::firstOrCreate(['user_id' => $record->id]);
                $reviewer->update([
                    'nama_lengkap' => $data['nama_lengkap'] ?? $reviewer->nama_lengkap,
                    'nip' => $data['nip'] ?? $reviewer->nip,
                ]);
            } elseif ($data['role'] === 'PESERTA') {
                $Penugasan = Penugasan::where('user_id', $record->id)->first();
                
                if ($Penugasan) {
                    $Penugasan->update([
                        'nama_pic' => $data['nama_pic'] ?? $Penugasan->nama_pic,
                        'jabatan_pic' => $data['jabatan_pic'] ?? $Penugasan->jabatan_pic,
                        'no_hp_pic' => $data['no_hp_pic'] ?? $Penugasan->no_hp_pic,
                    ]);
                    
                    if ($Penugasan->institusi) {
                        $Penugasan->institusi->update([
                            'nama_institusi' => $data['nama_pt'] ?? $Penugasan->institusi->nama_institusi,
                            'jenis_institusi' => $data['jenis_pt'] ?? $Penugasan->institusi->jenis_institusi,
                        ]);
                    }
                }
            }

            return $record->refresh();
        });
    }
}
