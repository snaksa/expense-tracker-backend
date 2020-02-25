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
                'color' => 'purple'
            ],
            [
                'name' => 'Clothes',
                'color' => 'blue'
            ],
            [
                'name' => 'Salary',
                'color' => 'yellow'
            ],
            [
                'name' => 'Fuel',
                'color' => 'black'
            ],
            [
                'name' => 'Electronics',
                'color' => 'green'
            ],
            [
                'name' => 'Education',
                'color' => 'red'
            ],
            [
                'name' => 'Rent',
                'color' => 'brown'
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
