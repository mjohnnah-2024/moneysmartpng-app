<?php

arch('models extend eloquent model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('controllers have controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('policies have policy suffix')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('middleware has correct namespace')
    ->expect('App\Http\Middleware')
    ->toBeClasses();

arch('models use has factory trait')
    ->expect('App\Models')
    ->toUse('Illuminate\Database\Eloquent\Factories\HasFactory');

arch('app does not use env helper outside config')
    ->expect('env')
    ->not->toBeUsedIn('App');

arch('controllers are not used by models')
    ->expect('App\Http\Controllers')
    ->not->toBeUsedIn('App\Models');
