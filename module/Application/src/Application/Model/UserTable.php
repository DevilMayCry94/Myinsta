<?php
namespace Application\Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

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
            'link' => $user->link,
            'codeActivation' => md5($user->email . time()),
        );
        if ($this->getidSocial($user->idSocial)) {
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

    public function getUser($id, $all = false)
    {
        $id = (int)$id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }

        return $row;
    }

    public function fetchAll()
    {
        $rowset = $this->tableGateway->select();
        return $rowset;
    }

    public function getidSocial($id)
    {
        $id = (int)$id;
        $rowset = $this->tableGateway->select(array('idSocial' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        var_dump($row);
        exit;
        return $row;
    }

    public function existEmailSocial($email)
    {
        $email = (string)$email;
        $rowset = $this->tableGateway->select(array('email' => $email));
        $row = $rowset->current();
        if (!$row) {
            return true;
        }
        return false;
    }

    public function sendMail($email)
    {
        $activation = md5($email . time());
        $textUrl = '<a target="_blank" href="http://dokuen-lx.nixsolutions.com/user/confirmreg?key=' . $activation . '">Активация</a>';

        $body = "Hello! Please confirm your account!<br><br> click in this link " . $textUrl;

        $message = new Message();
        $message->setSubject('Confirm your account');
        $message->addTo($email);
        $message->addFrom('admin@kuendo-lx.nixsolutions.com');
        $message->setBody($body);

        $options = new SmtpOptions();
        $options->setHost('10.10.0.114');
        $options->setPort('2525');
        $transport = new Smtp($options);

        $headers = array(
            'EXTERNAL' => 1,
            'PROJECT' => 'Myinsta',
            'EMAILS' => 'kuendo@nixsolutions.com'
        );
        foreach ($headers as $key => $value) {
            $message->getHeaders()->addHeaderLine($key, $value);
        }

        $transport->send($message);
    }

    public function activation()
    {
        if (!empty($_GET['key']) && isset($_GET['key'])) {
            $rowset = $this->tableGateway->select(array('codeActivation' => $_GET['key']));
            $row = $rowset->current();
            if ($row) {
                $query = $this->tableGateway->select(array('codeActivation' => $_GET['key'], 'isActive' => 0));
                $isNotActive = $query->current();
                if ($isNotActive) {
                    $this->tableGateway->update(['isActive' => 1], array('codeActivation' => $_GET['key']));
                    $_SESSION['user'] = $isNotActive->name;
                    $_SESSION['userEmail'] = $isNotActive->email;
                } else {
                    header('Location: /user/forgetPassword');
                }
            }
        }
    }

    public function getBy($get, array $by)
    {
        $rowset = $this->tableGateway->select($by);
        $row = $rowset->current();
        if ($row) {
            return $row->$get;
        } else {
            throw new \Exception('User ID does not exist');
        }
    }

    /**
     * @param $str
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function searchPeople($str)
    {
        $rowset = $this->tableGateway->select(function (Select $select) use ($str) {
            $select->where->like('name', '%' . $str . '%');
        }
        );
        return $rowset;
    }

    public function news(Sql $sql, $type)
    {
        switch($type)
        {
            case 'all-news' :
                $result = $this->allNews($sql);
                break;
            case 'following-news' :
                $result = $this->followingNews($sql);
                break;
            case 'my-news' :
                $result = $this->myNews($sql);
                break;
            default :
                break;
        }
        return $result;
    }

    public function allNews(Sql $sql)
    {
        $select = $sql->select();
        $select->from(array('p' => 'post'))
            ->join(['u' => 'user'],'p.idUser = u.id');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rows = $statement->execute();
        if($rows) {
            foreach ($rows as $r) {
                $result[] = array(
                    'id_post' => $r['id_post'],
                    'idUser' => $r['idUser'],
                    'name' => $r['name'],
                    'ava' => $r['ava'],
                    'urlImg' => $r['urlImg']
                );
            }

            return $result;
        }
        return false;
    }

    public function myNews(Sql $sql)
    {
        $select = $sql->select();
        $select->from('post');
        $select->where(['idUser' => $this->getBy('id', ['email' => $_SESSION['userEmail']])]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rows = $statement->execute();
        if($rows) {
            foreach ($rows as $r) {
                $result[] = array(
                    'id_post'   => $r['id_post'],
                    'idUser'   => $r['idUser'],
                    'name'      => $_SESSION['user'],
                    'ava'       => $this->getBy('ava', ['email' => $_SESSION['userEmail']]),
                    'urlImg'    => $r['urlImg']
                );
            }

            return $result;
        }
        return false;
    }

    public function followingNews(Sql $sql)
    {
        $select = $sql->select();
        $select->from(array('p' => 'post'))// base table
        ->join(array('u' => 'user'), 'p.idUser = u.id')
            ->join(array('f' => 'follow'), 'u.id = f.idFollower');
        $select->where(['f.idUser' => $this->getBy('id', ['email' => $_SESSION['userEmail']])]);
        $select->order('p.id_post DESC');
        $select->limit(12);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rows = $statement->execute();
        if($rows) {
            foreach ($rows as $r) {
                $result[] = array(
                    'id_post' => $r['id_post'],
                    'idUser' => $r['idFollower'],
                    'name' => $r['name'],
                    'ava' => $r['ava'],
                    'urlImg' => $r['urlImg']
                );
            }

            return $result;
        }
    }

    public function suggestion(Sql $sql)
    {
        $select = $sql->select();
        $select->from(array('u' => 'user'))
            ->join(array('f' => 'follow'), 'u.id = f.idUser');
        $select->columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(f.idUser)')));
        $select->group('f.idFollower');
        $select->where->notEqualTo('f.idUser', $this->getBy('id',['email' => $_SESSION['userEmail']]));
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        foreach ($result as $r) {
            print_r($r);
        }die;
        if($result) {
            foreach ($result as $r) {
                $data[] = $r;
            }
            return $data;
        } else {
            return false;
        }
    }


}