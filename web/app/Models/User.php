<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use App\Notifications\SendMail;
use App\Notifications\NewUserNotification;
use App\Notifications\OrderNotification;
use App\Notifications\CreditNotification;
use App\Notifications\ReadyForPickupOrder;
use App\Notifications\CodeNotification;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'email_verified_at','phone','avatar','password','status', 'address','token','reset_code','city_id','country_id','address', 'birth_date', 'gender', 'locale','card_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function identities()
    {
        return $this->hasMany('App\Models\SocialIdentity');
    }

    public function albums()
    {
        return $this->hasMany('App\Models\Album');
    }

    public function cart()
    {
        return $this->hasMany('App\Models\Cart');
    }

    public function couriers()
    {
        return $this->hasMany('App\Models\Courier');
    }

    public function courier()
    {
        return $this->hasOne(Courier::class);
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function partners()
    {
        return $this->hasMany('App\Models\Partner');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress(){
        return $this->hasOne(UserAddress::class)->where('default', 1);
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }

    public function cards()
    {
        return $this->hasMany(UserCard::class);
    }

    public function card()
    {
        return $this->hasOne(UserCard::class)->where('default', 1);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'user_packages')->withPivot('package_credit_points');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function appliedCoupons()
    {
        return $this->hasMany(CouponUser::class);
    }

    public function sendActivationCode()
    {
        $this->notify(new NewUserNotification());
    }

    public function sendOrderNotification($order)
    {
        $this->notify(new OrderNotification($order));
    }

    public function sendRememberCode($code)
    {
        $this->notify(new CodeNotification($code));
    }

    public function sendCreditNotification($package)
    {
        $this->notify(new CreditNotification($package));
    }

    public function sendReadyOrderNotification($order)
    {
        $this->notify(new ReadyForPickupOrder($order));
    }

    public function formatAddress()
    {
        $address = '';
        if ($this->city) {
            $address .= $this->city->country->country_name.' - '.$this->city->city_name;
        }

        if ($this->address) {
            $address .= ', '.$this->address;
        }

        return $address;
    }

    public function getGenderName($gender)
    {
        $genders = [
            0   => trans('app.female'),
            1   => trans('app.male'),
            2   => trans('app.another')
        ];
        $genderName = isset($genders[$gender]) ? $genders[$gender] : null;
        return $genderName;
    }

    public function getCreditPointsAttribute()
    {
        // initiate the credit points value
        $credit_points = 0;

        // get user purchased packages
        $packages = $this->packages;
        // add packages credit points to user balance
        $credit_points += $packages->pluck('pivot')->sum('package_credit_points');

        // get user applied point coupons
        $points_coupons = $this->appliedCoupons->where('value_type', 'points');
        // add coupons credit points to user balance
        $credit_points += $points_coupons->sum('value');

        // subtract used credit points form user balance
        $credit_points_payments = $this->payments->where('payment_method', 'credit_points');
        $used_credit_points = $credit_points_payments->sum('points_amount');
        $credit_points -= $used_credit_points;

        return $credit_points;
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'users.' . $this->id;
    }
}
