<?php

namespace App\Models;


use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'project_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'image',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    public static function boot() {
        parent::boot();
        
        static::created(function($user) {
            $invites = ProjectInvite::where('email', $user->email)->where('status', 0)->get();

            foreach($invites as $invite) {
                ProjectUser::create([
                    'project_id' => $invite->project_id,
                    'user_id' => $user->id,
                    'user_type' => $invite->role
                ]);
                
                $invite->user_id = $user->id;
                $invite->status = 0;
                $invite->save();
            }
        });
    }

    public function projectPage()
    {
        return $this->hasOne('App\Models\ProjecPageUser', 'user_id', 'id');
    }
}
