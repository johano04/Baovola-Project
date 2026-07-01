<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class SeederController extends BaseController
{
    public function seed()
    {
        $productModel = new ProductModel();

        $products = [
            [
                'name' => 'T-shirt blanc',
                'description' => 'T-shirt blanc de qualité',
                'price' => 40000,
                'image_url' => '/images/tshirt.jpg'
            ],
            [
                'name' => 'Mug personnalisé',
                'description' => 'Mug personnalisé avec votre texte',
                'price' => 25000,
                'image_url' => '/images/mug.jpg'
            ],
            [
                'name' => 'Casquette',
                'description' => 'Casquette ajustable',
                'price' => 30000,
                'image_url' => '/images/cap.jpg'
            ],
            [
                'name' => 'Sandales',
                'description' => 'Sandales confortables',
                'price' => 35000,
                'image_url' => '/images/sandals.jpg'
            ],
            [
                'name' => 'Sac à dos',
                'description' => 'Sac à dos robuste',
                'price' => 55000,
                'image_url' => '/images/backpack.jpg'
            ],
            [
                'name' => 'Montre',
                'description' => 'Montre élégante',
                'price' => 75000,
                'image_url' => '/images/watch.jpg'
            ],
        ];

        foreach ($products as $product) {
            $productModel->insert($product);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Products seeded successfully'
        ]);
    }
}