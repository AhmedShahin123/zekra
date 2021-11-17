<?php

namespace App\Services\Order;

use Auth;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Album\AlbumRepositoryInterface;
use App\Repositories\Image\ImageRepositoryInterface;
use App\Models\User;
use Carbon;

class OrderService implements OrderServiceInterface
{
    private $orderRepository;
    private $albumRepository;
    private $imageRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, AlbumRepositoryInterface $albumRepository, ImageRepositoryInterface $imageRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->albumRepository = $albumRepository;
        $this->imageRepository = $imageRepository;
    }

    // order related business functionality

    public function addCart(array $data)
    {
        $user = User::find($data['user_id']);
        //dd($user);
        if (empty($user)) {
            return null;
        }
        $data['user_id'] = $data['user_id'];
        $data['cart_total'] = $data['total'];
        $data['cart_total_fee'] = $data['fee'];
        $data['cart_total_tax'] = $data['tax'];
        $data['total_price'] = $data['total'];


        if (!$cart) {
            return null;
        }
        $data['cart_id'] = $cart->id;
        return $this->addOrder($data);
    }

    public function addOrder(array $data)
    {
        $user = User::find($data['user_id']);
        $mytime = Carbon\Carbon::now();
        //dd($user);
        if (empty($user)) {
            return null;
        }
        $data['user_id'] = $data['user_id'];
        $data['shipping_address'] = $data['shipping_address'];
        $data['shipping_phone'] = $data['shipping_phone'];
        $data['fee'] = $data['fee'];
        $data['tax'] = $data['tax'];
        $data['total'] = $data['total'];
        $data['album_count'] = $data['album_count'];
        $data['cart_id'] = $data['cart_id'];
        $data['progress_status'] = "Order Received";
        $data['delivery_status'] = "Ready for Pickup";
        $data['payment_status'] = "Not Paid";
        $data['progress_status_date'] = $mytime->toDateTimeString();
        $data['delivery_status_date'] = $mytime->toDateTimeString();
        $data['card_token'] = null;
        $data['stripe_transaction_date'] = null;

        $order = $this->orderRepository->create($data);


        $albumData['user_id'] = $user->id;
        $albumData['cover_id'] = $user->id;
        $albumData['order_id'] = $order->id;
        $albumData['album_name'] = $data['album_name'];
        $albumData['album_status'] = 0;

        $album = $this->albumRepository->create($albumData);

        $images = $data['images'];
        foreach ($images as $key => $image) {
            //dd($image);

            $file_data       = $image;  // your base64 encoded
            //generating unique file name;
            $file_name = str_random(10).'image_'.time().'.jpg';
            @list($type, $file_data) = explode(';', $file_data);
            @list(, $file_data)      = explode(',', $file_data);
            if ($file_data!="") {
                // storing image in storage/app/public Folder
                \Storage::put($file_name, base64_decode($file_data));
            }

            $imageData['album_id'] = $album->id;
            $imageData['image_path'] = "storage/app/public/";
            $imageData['image_name'] = $file_name;
            $imageData['image_size'] = "100";

            $image = $this->imageRepository->create($imageData);
        }


        if (!$order) {
            return null;
        }

        return $order;
    }

    public function userOrders($user_id)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            return null;
        }
        $userOrders = $this->orderRepository->where(['user_id' => $user->id])->latest()->get();
        //dd($city);
        //dd($city);
        if (!$userOrders) {
            return null;
        }
        return $userOrders;
    }
}
