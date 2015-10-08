<?php
namespace Application\Controller;

use Application\Model\ActionUser;
use Application\Model\Post;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class AjaxController extends AbstractActionController
{
    public function indexAction()
    {
        $data = $this->getComment();
        return new JsonModel($data);

    }

    public function SearchAction()
    {
        $usertable = $this->getServiceLocator()->get('UserTable');
        $obj = $usertable->searchPeople($_POST['search']);
        foreach($obj as $o)
        {
            $data[] = array('id' => $o->id, 'name' => $o->name, 'ava' => $o->ava);
        }
        $result = new JsonModel($data);
        return $result;
    }

    public function myCommentAction()
    {
        $this->saveComment();
        $post = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $post->getPostbysrc($_POST['src']);
        $user = $usertable->getUser($img->idUser);
        $data = array(
            'idUser' => $user->id,
            'name' => $user->name,
            'comment' => $_POST['textcomment'],
            'ava' => $user->ava
        );
        return new JsonModel($data);
    }

    public function loadCommentAction()
    {
        $post = $this->getServiceLocator()->get('PostTable');
        $img = $post->getPostbysrc($_POST['src']);
        $idPost = $img->id_post;
        $sql = $this->getServiceLocator()->get('Sql');
        $select = $sql->select();
        $select->from(array('a' => 'action'))  // base table
        ->join(array('u' => 'user'),     // join table with alias
            'a.idUser = u.id');
        $select->where->equalTo('a.idPost', $idPost);
        $select->where->isNotNull('a.comment');
        $select->where->greaterThan('a.idAction',$_POST['lastid']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rows = $statement->execute();
        if($rows) {
            foreach($rows as $r) {
                $data[] = $r;
            }
            return new JsonModel($data);
        } else return false;
    }

    public function followAction()
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getBy('id',['email'=>$_SESSION['userEmail']]);
        if($_POST['btnValue'] == 'Follow')
        {
            $insert = $sql->insert();
            $insert->into('follow');
            $insert->values(array('idUser' => $user, 'idFollower' => $_POST['idFollower']));
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
            return new JsonModel(['value'=>'Following']);
        } else {
            $delete = $sql->delete();
            $delete->from('follow');
            $delete->where(array('idUser' => $user, 'idFollower' => $_POST['idFollower']));
            $statement = $sql->prepareStatementForSqlObject($delete);
            $statement->execute();
            return new JsonModel(['value' => 'Follow']);
        }
    }

    public function loadMoreAction()
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $idUser = $userTable->getBy('id', ['link' => $_POST['linkUser']]);
        $select = $sql->select();
        $select->from('post');
        $select->where(['idUser' => $idUser]);
        $AllRecords = $sql->prepareStatementForSqlObject($select)->execute();
        $select->order('id_post DESC');
        $select->limit(9);
        $select->offset((int)$_POST['count']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rows = $statement->execute();
        foreach($rows as $r)
        {
            $data[] = $r;
        }
        if((int)$_POST['count'] + 9 >= $AllRecords->count())
        {
            $data[] = 1;
        } else {
            $data[] = 0;
        }
        return new JsonModel($data);
    }


    public function saveComment()
    {
        $postTable = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $postTable->getPostbysrc($_POST['src']);
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        $data = array(
            'idPost'    => $img->id_post,
            'idUser'    => $usertable->getBy('id', ['email' => $_SESSION['userEmail']]),
            'comment'   => $_POST['textcomment']
        );
        $action = new ActionUser();
        $action->exchangeArray($data);
        $actionTable->save($action);

    }

    public function getComment()
    {
        $post = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $post->getPostbysrc($_POST['src']);
        $user = $usertable->getUser($img->idUser);
        $data = array(
            'idImg'             => $_POST['idImg'],
            'src'               => $_POST['src'],
            'own_comment'       => $img->comment,
            'inf_user'          => $user,
        );
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        $idPost = $img->id_post;
        if($actionTable->getAction($idPost)) {
            $sql = $this->getServiceLocator()->get('Sql');
            $select = $sql->select();
            $select->from(array('a' => 'action'))// base table
            ->join(array('u' => 'user'),     // join table with alias
                'a.idUser = u.id');
            $select->where(['a.idPost' => $idPost,
                new \Zend\Db\Sql\Predicate\IsNotNull('a.comment')]);
            $statement = $sql->prepareStatementForSqlObject($select);
            $rows = $statement->execute();
            if($rows->count()) {
                foreach ($rows as $r) {
                    $comment[] = $r;
                }
                $data['comment'] = $comment;
            }
            $data['countLike'] = $actionTable->countLike($idPost);
            $data['countComment'] = $actionTable->countComment($idPost);
        }
        return $data;
    }



    //CROP IMG

    public function cropfileAction()
    {
        $imgUrl = BASE_PATH . $_POST['imgUrl'];
// original sizes
        $imgInitW = $_POST['imgInitW'];
        $imgInitH = $_POST['imgInitH'];
// resized sizes
        $imgW = $_POST['imgW'];
        $imgH = $_POST['imgH'];
// offsets
        $imgY1 = $_POST['imgY1'];
        $imgX1 = $_POST['imgX1'];
// crop box
        $cropW = $_POST['cropW'];
        $cropH = $_POST['cropH'];
// rotation angle
        $angle = $_POST['rotation'];

        $jpeg_quality = 100;

        $filename = "croppedImg_".rand();
        $output_filename = BASE_PATH . "/img/" . $filename;

// uncomment line below to save the cropped image in the same location as the original image.
//$output_filename = dirname($imgUrl). "/croppedImg_".rand();
        $what = getimagesize($imgUrl);

        switch(strtolower($what['mime']))
        {
            case 'image/png':
                //$img_r = imagecreatefrompng($imgUrl);
                $source_image = imagecreatefrompng($imgUrl);
                $type = '.png';
                break;
            case 'image/jpeg':
                //$img_r = imagecreatefromjpeg($imgUrl);
                $source_image = imagecreatefromjpeg($imgUrl);
                error_log("jpg");
                $type = '.jpeg';
                break;
            case 'image/gif':
                //$img_r = imagecreatefromgif($imgUrl);
                $source_image = imagecreatefromgif($imgUrl);
                $type = '.gif';
                break;
            default: die('image type not supported');
        }


//Check write Access to Directory

        if(!is_writable(dirname($output_filename))){
            $response = Array(
                "status" => 'error',
                "message" => 'Can`t write cropped File'
            );
        }else{

            // resize the original image to size of editor
            $resizedImage = imagecreatetruecolor($imgW, $imgH);
            imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
            // rotate the rezized image
            $rotated_image = imagerotate($resizedImage, -$angle, 0);
            // find new width & height of rotated image
            $rotated_width = imagesx($rotated_image);
            $rotated_height = imagesy($rotated_image);
            // diff between rotated & original sizes
            $dx = $rotated_width - $imgW;
            $dy = $rotated_height - $imgH;
            // crop rotated image to fit into original rezized rectangle
            $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
            imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
            imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
            // crop image into selected area
            $final_image = imagecreatetruecolor($cropW, $cropH);
            imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
            imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
            // finally output png image
            //imagepng($final_image, $output_filename.$type, $png_quality);
            imagejpeg($final_image, $output_filename.$type, $jpeg_quality);
            $response = Array(
                "status" => 'success',
                "url" => '/img/'.$filename.$type
            );
        }
        print json_encode($response);
        die;
    }

    public function imgsaveAction()
    {
        $imagePath = BASE_PATH . "/temp/";

        $allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
        $temp = explode(".", $_FILES["img"]["name"]);
        $extension = end($temp);

        //Check write Access to Directory

        if(!is_writable($imagePath)){
            $response = Array(
                "status" => 'error',
                "message" => 'Can`t upload File; no write Access'
            );
            print json_encode($response);
            return;
        }

        if ( in_array($extension, $allowedExts))
        {
            if ($_FILES["img"]["error"] > 0)
            {
                $response = array(
                    "status" => 'error',
                    "message" => 'ERROR Return Code: '. $_FILES["img"]["error"],
                );
            }
            else
            {

                $filename = $_FILES["img"]["tmp_name"];
                list($width, $height) = getimagesize( $filename );

                move_uploaded_file($filename,  $imagePath . $_FILES["img"]["name"]);

                $response = array(
                    "status" => 'success',
                    "url" => '/temp/'.$_FILES["img"]["name"],
                    "width" => $width,
                    "height" => $height
                );


            }
        }
        else
        {
            $response = array(
                "status" => 'error',
                "message" => 'something went wrong, most likely file is to large for upload. check upload_max_filesize, post_max_size and memory_limit in you php.ini',
            );
        }
        print json_encode($response);
        die;
    }

    public function addpostAction()
    {
        $post = array(
            'urlImg'  => $_POST['src_img'],
            'comment' => $_POST['comment'],
        );
        $this->savePost($post);
        return new JsonModel(['status'=>'success']);
    }

    public function savePost($data)
    {
        $iduser = $this->getServiceLocator()->get('UserTable');
        $data['idUser'] = $iduser->getBy('id',['email' => $_SESSION['userEmail']]);
        $post = new Post();
        $post->exchangeArray($data);
        $userTable = $this->getServiceLocator()->get('PostTable');
        $userTable->save($post);
        return true;

    }

    public function changeAvaAction()
    {
        $iduser = $this->getServiceLocator()->get('UserTable')->getBy('id',['email' => $_SESSION['userEmail']]);
        $sql = $this->getServiceLocator()->get('Sql');
        $update = $sql->update();
        $update->table('user');
        $img = explode('/',$_POST['src_ava']);
        $update->set(['ava' => $img[2]]);
        $update->where->equalTo('id', $iduser);
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
        return new JsonModel(['status' => 'success']);
    }

    public function newsLikeAction()
    {
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $where = array("idPost" => $_POST['idPost'],
            'idUser' => $userTable->getBy('id', ['email' => $_SESSION['userEmail']]));
        $action = new ActionUser();
        if($_POST['isLike'] == 'false') {
            $data = array(
                'idPost' => $_POST['idPost'],
                'idUser' => $userTable->getBy('id', ['email' => $_SESSION['userEmail']]),
                'like' => 1
            );
        } else {
            $data = array(
                'idPost' => $_POST['idPost'],
                'idUser' => $userTable->getBy('id', ['email' => $_SESSION['userEmail']]),
                'like' => 0
            );
        }
        if($actionTable->actionUser($where)){
            $actionTable->update($data,$where);
        }else {
            $action = new ActionUser();
            $action->exchangeArray($data);
            $actionTable->save($action);
        }

        return new JsonModel(['status' => 'success']);
    }


}