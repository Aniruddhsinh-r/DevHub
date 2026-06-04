<?php
require_once __DIR__.'/../Helpers/adminLogin.php';

test('Create category by admin', function () {
    adminLogin();

    visit('/admin/categories')
    ->fill('name','minati')
    ->click('Create Category')
    ->assertRoute('admin.categories')
    ->assertSee('minati');
    // ->click('@delete-category')
    // ->assertDontSee('category');
});

// test('delete category', function () {
//      adminLogin();

//     visit('/admin/categories?search=lkamar')
//     ->fill('name','alkamar')
// });
