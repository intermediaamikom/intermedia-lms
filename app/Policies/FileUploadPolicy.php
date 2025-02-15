<?php
namespace App\Policies;

use App\Models\FileUpload;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileUploadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Member']);
    }

    public function view(User $user, FileUpload $fileUpload)
    {
        return $user->hasRole(['Super Admin', 'Admin']) || $user->id === $fileUpload->user_id;
    }

    public function create(User $user)
    {
        return $user->hasRole('Member');
    }

    public function update(User $user, FileUpload $fileUpload)
    {
        return $user->hasRole(['Super Admin', 'Admin']);
    }

    public function delete(User $user, FileUpload $fileUpload)
    {
        return $user->hasRole(['Super Admin', 'Admin']);
    }
}
