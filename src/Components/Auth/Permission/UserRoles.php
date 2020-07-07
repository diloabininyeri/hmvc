<?php


namespace App\Components\Auth\Permission;

use App\Components\Database\Model;
use App\Components\Database\PDOAdaptor;
use App\Components\Exceptions\RoleNotFoundException;
use PDO;

/**
 * Class UserRoles
 * @package App\Components\Auth\Permission
 */
class UserRoles
{
    /**
     * @var Model
     */
    private Model $userModel;


    /**
     * UserRoles constructor.
     * @param Model $userModel
     */
    public function __construct(Model $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @return PDO
     */
    private function getPdoConnection(): PDO
    {
        return PDOAdaptor::connection($this->userModel->getConnection());
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $query = $this->getPdoConnection()->prepare('SELECT * FROM roles WHERE id IN(SELECT role_id FROM user_roles WHERE user_id=:user_id)');
        $query->execute(['user_id' => $this->userModel->id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param string $roleName
     * @return bool
     */
    public function has(string $roleName): bool
    {
        $query = $this->getPdoConnection()->prepare('SELECT id FROM user_roles WHERE  role_id IN (SELECT id FROM roles WHERE name=:name) AND user_id=:user_id');
        $query->execute(['name' => $roleName, 'user_id' => $this->userModel->id]);
        return !empty($query->fetchAll());
    }

    /**
     * @param string $roleName
     * @return bool
     */
    public function assignRole(string $roleName): bool
    {
        if (!$this->has($roleName)) {
            $role = $this->findByName($roleName);
            $query = $this->getPdoConnection()->prepare('INSERT INTO  user_roles SET user_id=:user_id,role_id=:role_id,created_at=:created_at,updated_at=:updated_at');
            $execute = $query->execute([':user_id' => $this->getUserId(), ':role_id' => $role->id, ':created_at' => now(), ':updated_at' => now()]);
            return $execute && $query->rowCount();
        }
        return false;
    }

    /**
     * @return int
     */
    private function getUserId(): int
    {
        return $this->userModel->getPrimaryKeyValue();
    }


    /**
     * @param string $roleName
     * @return object
     */
    private function findByName(string $roleName): object
    {
        $role = (new Role())->findByName($roleName);
        if ($role) {
            return $role;
        }
        throw new RoleNotFoundException("there is no such $roleName role");
    }

    /**
     * @param string $roleName
     * @return bool
     */
    public function delete(string $roleName): bool
    {
        $role=$this->findByName($roleName);
        $query = $this->getPdoConnection()->prepare('DELETE FROM user_roles WHERE role_id=:role_id and user_id=:user_id');
        $execute = $query->execute([':role_id' => $role->id, ':user_id' => $this->getUserId()]);
        return ($execute && $query->rowCount());
    }
}
