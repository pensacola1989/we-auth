<?php namespace App\Services\Account;

use App\Services\Core\EntityRepository;
use App\Services\Exception\EntityNotFoundException;

/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 5:00 PM
 */
class UserRepository extends EntityRepository implements UserContract
{

    private $loginModel;

    /**
     * UserRepository constructor.
     * @param User $model
     * @param Login $login
     */
    public function __construct(User $model, Login $login)
    {
        $this->model = $model;
        $this->loginModel = $login;
    }

    /**
     * Fetch all users;
     * @return User
     */
    public function getUserAll()
    {
        return $this->getAll();
    }


    /**
     * Add a User
     * @param $userData
     * @return mixed
     */
    public function createUser(array $userData)
    {
        return $this->createModel($userData);
        // dd(\DB::getQueryLog());
    }

    /**
     * update a user's info by user id
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateUserInfo($id, $data)
    {
        return $this->updateModel($id, $data);
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['name'])) {
            $query = $query->where('name', $criteria['name']);
        }

        return $query;
    }

    protected function includeForQuery($query)
    {
        return $query;
    }

    public function getUserByMobile($mobile)
    {
        return $this->model->where('mobile', $mobile)->first();
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }
}
