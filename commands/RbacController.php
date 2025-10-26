<?php
namespace app\commands;

use yii\console\Controller;
use Yii;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAllPermissions();
        Yii::$app->db->createCommand()->delete('{{%auth_item_child}}')->execute();

        $modelPermissions = [
            'Book'   => ['view', 'create', 'update', 'delete'],
            'Author' => ['view', 'create', 'update', 'delete', 'subscribe'],
        ];

        $guestPermissions = ['viewBook', 'viewAuthor', 'subscribeAuthor'];
        $userPermissions  = ['createBook', 'updateBook', 'deleteBook', 'createAuthor', 'updateAuthor', 'deleteAuthor'];

        foreach ($modelPermissions as $entity => $actions) {
            foreach ($actions as $action) {
                $name                    = $action . $entity;
                $permission              = $auth->createPermission($name);
                $permission->description = ucfirst($action) . ' ' . strtolower($entity);
                $auth->add($permission);
            }
        }

        $guest = $auth->getRole('guest');
        if (!$guest) {
            $guest = $auth->createRole('guest');
            $auth->add($guest);
        }

        foreach ($guestPermissions as $permName) {
            $auth->addChild($guest, $auth->getPermission($permName));
        }


        $user = $auth->getRole('user');
        if (!$user) {
            $user = $auth->createRole('user');
            $auth->add($user);
        }
        $auth->addChild($user, $guest);

        foreach ($userPermissions as $permName) {
            $auth->addChild($user, $auth->getPermission($permName));
        }

        echo "RBAC initialized.\n";
    }
}
