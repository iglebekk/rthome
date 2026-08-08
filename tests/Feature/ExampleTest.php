<?php

test('the public entry point directs guests to sign in', function () {
    $this->get('/')->assertRedirectToRoute('login');
});
