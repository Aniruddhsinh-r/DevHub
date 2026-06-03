<?php

function userLogin()
{
    visit('/login')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', '1290')
    ->press('@login-btn')
    ->assertRoute('home');
}
