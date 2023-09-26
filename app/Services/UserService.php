<?php
namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById($id)
    {
        return $this->userRepository->findById($id);
    }


    public function getUserByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }


    public function createUser($userData)
    {
        
        $users = $this->userRepository->create($userData);        
        return $users;
    }

    public function googleLogin($code)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(url('/google/callback'));
        if ($code) {
            $token = $client->fetchAccessTokenWithAuthCode($code);
            if (!isset($token['error'])) {
                $client->setAccessToken($token['access_token']);
                $oauthService = new Google_Service_Oauth2($client);
                $userInfo = $oauthService->userinfo->get();

                // Here, you can access the user's information
                $email = $userInfo->email;
                $name = $userInfo->name;
                $phone_number = $userInfo?->phone_number;
                $parent = $this->userRepository->getUserByEmail($email);
                if($parent){
                    return $parent;
                }
                $parent = $this->createParent([
                    "email"=>$email,
                    "first_name"=>$name,
                    "phone_number"=>$phone_number
                ]);
                // Perform further actions, such as authentication or user creation
                return $parent;
            }
        }
        throw new \Exception("Failed to sign in with Google.");
    }
}

?>