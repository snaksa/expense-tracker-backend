<?php

namespace App\EventListener\User;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CreateListener
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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
    }
}
