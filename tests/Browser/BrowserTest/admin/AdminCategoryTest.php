<?php
require_once __DIR__.'/../../Helpers/adminLogin.php';

test('Create category by admin', function () {
    adminLogin();

    visit('/admin/categories')
    ->fill('name','lkamar')
    ->click('Create Category')
    ->assertRoute('admin.categories')
    ->assertSee('lkamar');
    // ->click('@delete-category')
    // ->assertDontSee('category');
});
