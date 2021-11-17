<?php 

namespace App\Repositories;

interface BaseRepositoryInterface
{
    public function all();

    public function paginate(int $count, $orderBy, $order = 'DESC');

    public function create(array $data);

    public function find(int $id);
    
    public function update($model, array $data);

    public function delete($model);

    public function count();

    public function where($conditions);
    
    public function first();
    
}