<?php

/**
 * Legacy authentication controller.
 *
 * Authentication and user sessions are disabled in this version of the
 * project. This stub is kept only so that any old links to `?controller=auth`
 * do not break the application; everything is redirected to the public home.
 */
class AuthController extends Controller
{
    public function index()
    {
        $this->redirect('?controller=home&action=index');
    }

    public function __call($name, $arguments)
    {
        $this->redirect('?controller=home&action=index');
    }
}
