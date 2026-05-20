<?php
// controllers/PeribadiController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../models/Peribadi.php';

class PeribadiController
{
    private Peribadi $model;
    private PDO $pdoStudent;
    private User $userModel;
    private string $errorMessage = '';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $pdoStudent = Database::pdoSybaseStudent();
        if (!$pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }

        $this->userModel = new User(Database::pdoMysql());

        $this->model = new Peribadi($pdoStudent, $this->userModel);
    }


    public function getCurrentUserDetailsInfo(): array
    {
        try{
            $matrik = trim((string)($_SESSION['f_stafID'] ?? ''));

            if ($matrik === '') {
                return $this->model->emptyProfile(
                    base_url('assets/images/no-image.jpg')
                );
            }

            $student = $this->model->getStudentByMatrik($matrik);

            if (!$student) {
                return $this->model->emptyProfile(
                    base_url('assets/images/no-image.jpg')
                );
            }

            $avatar = $this->model->getAvatar((string)($student['matrik'] ?? ''), base_url('assets/images/no-image.jpg'));
            return $this->model->formatStudent($student, $avatar);

        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

}

