<?php

namespace App\Services\Order;

interface OrderServiceInterface
{
    public function addCart(array $data);
    public function addOrder(array $data);
    public function userOrders($user_id);
}
