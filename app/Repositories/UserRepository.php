<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getAll()
    {
        return User::paginate(10);
    }

    public function findById($id)
    {
        return User::find($id);
    }

    public function findByEmail($email)
    {
        return User::where('email',$email)->first();
    }


    public function getUserByEmail($email)
    {
        return User::where('email',$email);
    }

    public function create($userData)
    {
        return User::create($userData);
    }
}

?>