<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class Products extends BaseController
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $products = $this->productModel->findAll();

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    public function show($id = null)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Product not found'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product
        ]);
    }
}