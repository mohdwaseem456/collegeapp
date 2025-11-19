<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Src\Core\Router;
use Src\Core\Request;

$router = new Router();

// ----------------------
// AUTH ROUTES (PUBLIC)
// ----------------------
$router->post('/login', [
    'action' => 'Src\Controllers\AuthController@login'
]);

$router->post('/logout', [
    'action' => 'Src\Controllers\AuthController@logout'
]);

// ----------------------
// TEACHER ROUTES (auth = 1)
// ----------------------
$router->post('/student', [
    'action' => 'Src\Controllers\TeacherStudentController@createStudent',
    'auth'   => 1
]);

$router->delete('/student', [
    'action' => 'Src\Controllers\TeacherStudentController@deleteStudent',
    'auth'   => 1
]);

$router->post('/attendence', [
    'action' => 'Src\Controllers\TeacherStudentController@markAttendence',
    'auth'   => 1
]);

$router->post('/subject', [
    'action' => 'Src\Controllers\TeacherSubjectController@createSubject',
    'auth'   => 1
]);

$router->delete('/subject', [
    'action' => 'Src\Controllers\TeacherSubjectController@deleteSubject',
    'auth'   => 1
]);

$router->post('/assign', [
    'action' => 'Src\Controllers\TeacherSubjectController@assignStudent',
    'auth'   => 1
]);

$router->delete('/assign', [
    'action' => 'Src\Controllers\TeacherSubjectController@removeStudent',
    'auth'   => 1
]);

$router->get('/students', [
    'action' => 'Src\Controllers\TeacherSubjectController@getAllStudents',
    'auth'   => 1
]);

$router->get('/teacher', [
    'action' => 'Src\Controllers\TeacherSubjectController@getSubjectStudents',
    'auth'   => 1
]);

// ----------------------
// STUDENT ROUTES (auth = 2)
// ----------------------
$router->get('/my_subjects', [
    'action' => 'Src\Controllers\StudentController@getMySubjects',
    'auth'   => 2
]);

// ----------------------
// DISPATCH
// ----------------------
$uri    = Request::uri();
$method = Request::method();
$router->dispatch($uri, $method);
