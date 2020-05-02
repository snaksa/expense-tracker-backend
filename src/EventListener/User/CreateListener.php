<?php

namespace App\EventListener\User;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
use App\Repository\WalletRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CreateListener
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(CategoryRepository $categoryRepository, WalletRepository $walletRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->walletRepository = $walletRepository;
    }

    public function postPersist(User $user, LifecycleEventArgs $args)
    {
        $categories = [
            [
                'name' => 'Food',
                'color' => '#f98f83'
            ],
            [
                'name' => 'Clothes',
                'color' => '#0099ff'
            ],
            [
                'name' => 'Salary',
                'color' => '#00ffff'
            ],
            [
                'name' => 'Fuel',
                'color' => '#996633'
            ],
            [
                'name' => 'Electronics',
                'color' => '#DE60D4'
            ],
            [
                'name' => 'Education',
                'color' => '#a6a6a6'
            ],
            [
                'name' => 'Rent',
                'color' => '#00ff00'
            ]
        ];

        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat['name']);
            $category->setColor($cat['color']);
            $category->setUser($user);
            $this->categoryRepository->save($category);
        }

        $wallets = [
            [
                'name' => 'Cash',
                'color' => '#f98f83',
            ],
            [
                'name' => 'Bank',
                'color' => '#a6a6a6',
            ],
        ];

        foreach ($wallets as $w) {
            $wallet = new Wallet();
            $wallet->setName($w['name']);
            $wallet->setUser($user);
            $wallet->setInitialAmount(0);
            $wallet->setColor($w['color']);
            $this->walletRepository->save($wallet);
        }
    }
}
