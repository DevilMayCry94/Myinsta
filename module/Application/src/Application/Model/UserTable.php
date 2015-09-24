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
            'ava' => $user->ava,
            'idSocial' => $user->idSocial,
        );
        if($this->getidSocial($user->idSocial))
        {
            $this->tableGateway->update($data, array('idSocial' => $user->idSocial));
        } else {
            $id = (int)$user->id;
            if ($id == 0) {
                $this->tableGateway->insert($data);
            } else {
                if ($this->getUser($id)) {
                    $this->tableGateway->update($data, array('id' => $id));
                } else {
                    throw new \Exception('User ID does not exist');
                }
            }
        }
    }

    public function getUser($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getidSocial($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('idSocial' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        return $row;
    }

    public function existEmailSocial($email)
    {
        $email = (string) $email;
        $rowset = $this->tableGateway->select(array('email' => $email));
        $row = $rowset->current();
        if (!$row) {
            return true;
        }
        return false;
    }




}