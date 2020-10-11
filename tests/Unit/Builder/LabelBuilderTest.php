<?php

namespace App\Tests\Unit\Builder;

use App\Builder\LabelBuilder;
use App\Entity\Label;
use App\Entity\User;
use App\GraphQL\Input\Label\LabelCreateRequest;
use App\GraphQL\Input\Label\LabelUpdateRequest;
use App\Repository\LabelRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class LabelBuilderTest extends TestCase
{
    public function test_label_builder_create()
    {
        $user = (new User())->setId(1);
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $service = new LabelBuilder($entityManagerMock, $authServiceMock);

        $label = $service->create()->build();

        $this->assertEquals(1, $label->getUserId());
    }

    public function test_label_builder_bind_create_request()
    {
        $user = (new User())->setId(1);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new LabelCreateRequest();
        $request->name = 'Name';
        $request->color = '#FF00FF';

        $service = new LabelBuilder($entityManagerMock, $authServiceMock);

        $label = $service->create()->bind($request)->build();

        $this->assertEquals(1, $label->getUserId());
        $this->assertEquals('Name', $label->getName());
        $this->assertEquals('#FF00FF', $label->getColor());
    }

    public function test_label_builder_bind_update_request()
    {
        $user = (new User())->setId(1);

        $labelRepositoryMock = $this->createMock(LabelRepository::class);
        $labelRepositoryMock->method('find')
            ->willReturn((new Label())->setUserId($user->getId()));

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock->method('getRepository')
            ->willReturn($labelRepositoryMock);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new LabelUpdateRequest();
        $request->id = 1;
        $request->name = 'Name';
        $request->color = '#FF00FF';

        $service = new LabelBuilder($entityManagerMock, $authServiceMock);

        $label = $service->create()->bind($request)->build();

        $this->assertEquals(1, $label->getUserId());
        $this->assertEquals('Name', $label->getName());
        $this->assertEquals('#FF00FF', $label->getColor());
    }
}
