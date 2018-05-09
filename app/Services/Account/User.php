<?php

namespace App\Services\Account;

use App\Services\Core\EntityBase;
use App\Services\Place\Like;
use App\Services\Post\Post;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/26/2015
 * Time: 2:23 PM
 */
class User extends EntityBase implements JWTSubject,AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    use SoftDeletes;
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'users';
    /**
     * Not stored
     * @var array
     */
    protected $guarded = [];

    /**
     * automatically hash the password when store
     * @param $value
     */
//    public function setPasswordAttribute($value)
//    {
//        $this->attributes['password'] = bcrypt($value);
//    }

//    public function setEmailAttribute($value)
//    {
//        if (empty($value)) { // will check for empty string, null values, see php.net about it
//            $this->attributes['email'] = NULL;
//        } else {
//            $this->attributes['email'] = $value;
//        }
//    }

    /**
     * User posts model
     */
    public function Posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany logs
     */
    public function Logins()
    {
        return $this->hasMany(Login::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany likes
     */
    public function Likes()
    {
        return $this->hasMany(Like::class, 'user_id');
    }
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nick_name', 'phone', 'gender'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Eloquent model method
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
