<?php
namespace Application\Model;
use Zend\Db\TableGateway\TableGateway;

class UserTable
{
    protected $tableGateway;
    public function __construct(TableGateway $tableGateway)
    {

        $this->tableGateway = $tableGateway;
    }

    public function save(User $user)
    {
        $data = array(
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
        );
        $id = (int)$user->id;
        if($id == 0)
        {
            $this->tableGateway->insert($data);
        }
    }
}