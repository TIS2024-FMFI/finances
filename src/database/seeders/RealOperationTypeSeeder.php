<?php

namespace Database\Seeders;

use App\Models\OperationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RealOperationTypeSeeder extends Seeder
{
    /**
     * The supported operation types.
     * @var array
     */
    private array $types = [
        [ 'name' => 'Služba na faktúru', 'expense' => false, 'lending' => false, 'repayment' => false ],
        [ 'name' => 'Grant', 'expense' => false, 'lending' => false, 'repayment' => false ],
        [ 'name' => 'Pôžička', 'expense' => false, 'lending' => true, 'repayment' => false ],
        [ 'name' => 'Splatenie pôžičky', 'expense' => false, 'lending' => true, 'repayment' => true ],
        [ 'name' => 'Iný', 'expense' => false, 'lending' => false, 'repayment' => false ],

        [ 'name' => 'Nákup na faktúru', 'expense' => true, 'lending' => false, 'repayment' => false ],
        [ 'name' => 'Nákup cez Marquet', 'expense' => true, 'lending' => false, 'repayment' => false ],
        [ 'name' => 'Drobný nákup', 'expense' => true, 'lending' => false, 'repayment' => false ],
        [ 'name' => 'Pracovná cesta', 'expense' => true, 'lending' => false, 'repayment' => false ],
        [ 'name' => 'Pôžička', 'expense' => true, 'lending' => true, 'repayment' => false ],
        [ 'name' => 'Splatenie pôžičky', 'expense' => true, 'lending' => true, 'repayment' => true ],
        [ 'name' => 'Iný', 'expense' => true, 'lending' => false, 'repayment' => false ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OperationType::factory()->createMany($this->types);
    }
}
