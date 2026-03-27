<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\UserModel;

/**
 * AuthController — Đăng nhập / đăng xuất
 */
class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * GET /auth/login — Hiển thị form đăng nhập
     */
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', ['title' => 'Đăng nhập'], false);
    }

    /**
     * POST /auth/login — Xử lý đăng nhập
     */
    public function login(): void
    {
        $v = new Validator($_POST);
        $v->required('email', 'Email')
          ->email('email', 'Email')
          ->required('mat_khau', 'Mật khẩu');

        if ($v->fails()) {
            $this->view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => $v->errors(),
                'old'    => $_POST,
            ], false);
            return;
        }

        $user = $this->userModel->findBy('email', $this->input('email'));

        if (!$user || !password_verify($this->input('mat_khau'), $user['mat_khau'])) {
            $this->view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => ['email' => 'Email hoặc mật khẩu không đúng.'],
                'old'    => $_POST,
            ], false);
            return;
        }

        if (($user['trang_thai'] ?? '') === 'khoa') {
            $this->view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => ['email' => 'Tài khoản đã bị khóa.'],
                'old'    => $_POST,
            ], false);
            return;
        }

        Auth::login($user);
        $this->redirect('/dashboard');
    }

    /**
     * GET /auth/logout — Đăng xuất
     */
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/auth/login');
    }
}