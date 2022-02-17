<?php
// +----------------------------------------------------------------------
// | LightPicture [ 图床 ]
// +----------------------------------------------------------------------
// | 企业团队图片资源管理系统
// +----------------------------------------------------------------------
// | Github: https://github.com/osuuu/LightPicture
// +----------------------------------------------------------------------
// | Copyright © http://picture.h234.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Team <admin@osuu.net>
// +----------------------------------------------------------------------
use think\facade\Route;


Route::get('/', function () {
	include('./error.html');
});
Route::get('index', 'Index/index');
/**
 * 登录注册
 */
Route::group('account', function () {
	Route::post('login', 'Account/login');
	Route::post('sendCode', 'Account/sendCode');
	Route::post('register', 'Account/register');
	Route::post('forget', 'Account/forget');
});

Route::group('user', function () {
	Route::get('info', 'User/info');
	Route::get('home', 'User/home');
	Route::get('storage', 'User/storage');
	Route::get('log', 'User/log');
	Route::put('resetPwd', 'User/resetPwd');
	Route::put('resetKey', 'User/resetKey');
	Route::put('update', 'User/update');
})->middleware(\app\middleware\TokenVerify::class);

Route::group('setup', function () {
	Route::get('index/:type', 'Setup/index')->middleware(\app\middleware\AuthVerify::class);
	Route::put('update', 'Setup/update')->middleware(\app\middleware\AuthVerify::class);
	Route::post('sendTest', 'Setup/sendTest')->middleware(\app\middleware\AuthVerify::class);
})->middleware(\app\middleware\TokenVerify::class);

Route::group('role', function () {
	Route::get('query', 'Role/index');
	Route::post('save', 'Role/save')->middleware(\app\middleware\AuthVerify::class);
	Route::put('update', 'Role/update')->middleware(\app\middleware\AuthVerify::class);
	Route::delete('delete', 'Role/delete')->middleware(\app\middleware\AuthVerify::class);
})->middleware(\app\middleware\TokenVerify::class);

Route::group('storage', function () {
	Route::get('query', 'Storage/index');
	Route::get('type', 'Storage/type');
	Route::put('update', 'Storage/update')->middleware(\app\middleware\AuthVerify::class);
	Route::post('save', 'Storage/save')->middleware(\app\middleware\AuthVerify::class);
	Route::delete('delete', 'Storage/delete')->middleware(\app\middleware\AuthVerify::class);
})->middleware(\app\middleware\TokenVerify::class);

Route::group('images', function () {
	Route::get('query', 'Images/index');
	Route::delete('delete', 'Images/delete');
})->middleware(\app\middleware\TokenVerify::class);

Route::group('member', function () {
	Route::get('query', 'Member/index');
	Route::put('update', 'Member/update')->middleware(\app\middleware\AuthVerify::class);
	Route::post('save', 'Member/save')->middleware(\app\middleware\AuthVerify::class);
	Route::delete('delete', 'Member/delete')->middleware(\app\middleware\AuthVerify::class);
})->middleware(\app\middleware\TokenVerify::class);

Route::group('updade', function () {
	Route::get('version', 'Updade/index')->middleware(\app\middleware\AuthVerify::class);
	Route::post('update', 'Updade/update')->middleware(\app\middleware\AuthVerify::class);
})->middleware(\app\middleware\TokenVerify::class);

Route::group('api', function () {
	Route::post('upload', 'Api/upload');
	Route::delete('delete', 'Api/delete');
});