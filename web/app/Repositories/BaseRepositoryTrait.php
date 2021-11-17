<?php
namespace App\Repositories;

trait BaseRepositoryTrait
{
    public function all()
    {
        return $this->model->all();
    }

    public function paginate(int $count, $orderBy, $order = 'DESC')
    {
        return $this->model->orderBy($orderBy, $order)->paginate($count);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function update($model, array $data)
    {
        return $model->update($data);
    }

    public function delete($model)
    {
        return $model->delete();
    }

    public function orderBy($column, $orderType = "desc")
    {
        return $this->model->orderBy($column, $orderType);
    }

    public function count()
    {
        return $this->model->count();
    }

    public function where($conditions)
    {
        return $this->model->where($conditions);
    }

    public function first()
    {
        return $this->model->first();
    }
}
