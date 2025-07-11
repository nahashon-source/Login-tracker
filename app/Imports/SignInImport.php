<?php

namespace App\Imports;

use App\Models\SignIn;
use Maatwebsite\Excel\Concerns\ToModel;

class SignInImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new SignIn([
            //
        ]);
    }
}
