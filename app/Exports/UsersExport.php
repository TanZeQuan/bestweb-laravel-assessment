<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumns;

class UsersExport implements FromQuery, WithColumns
{
    public function query(): Builder
    {
        return User::query()
            ->select(['id', 'name', 'email', 'phone_number', 'status'])
            ->orderBy('id');
    }

    public function columns(): array
    {
        return [
            Number::make('ID', 'id'),
            Text::make('Name', 'name'),
            Text::make('Email', 'email'),
            Text::make('Phone Number', 'phone_number'),
            Text::make('Status', 'status'),
        ];
    }
}
