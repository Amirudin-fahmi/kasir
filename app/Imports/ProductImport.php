<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImport implements ToModel, WithMultipleSheets, WithHeadingRow, SkipsEmptyRows
{

    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Product([
            'name' => $row['name'],
            'slug' => Product::generateUniqueSlug($row['name']),
            'category_id' => $row['category_id'],
            'stock' => $row['stock'],
            'price' => $row['price'],
            'is_active' => $row['is_active'],
            'image' => $row['image'],
            'barcode' => $row['barcode'],

        ]);
    }
}
