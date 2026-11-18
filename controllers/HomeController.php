<?php
// controllers/HomeController.php
class HomeController
{
    public function index()
    {
        // عرض الصفحة الرئيسية مباشرة
        require __DIR__ . '/../views/home.php';
    }
}