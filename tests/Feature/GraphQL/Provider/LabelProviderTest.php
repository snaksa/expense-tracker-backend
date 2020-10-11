<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\LabelFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Label;
use App\Entity\User;
use App\Repository\LabelRepository;
use App\Services\AuthorizationService;
use App\Tests\Feature\BaseTestCase;

class LabelProviderTest extends BaseTestCase
{
    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            LabelFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_retrieve_user_labels(): void
    {
        $labels = $this->filterFixtures(function ($entity) {
            return $entity instanceof Label
                && $entity->getUserId() === $this->user->getId();
        });

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('findUserLabels')->with($this->user)->willReturn($labels);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'labels',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('labels', $content);

        $expected = array_map(function (Label $label) {
            return ['id' => $label->getId()];
        }, $labels);

        $this->assertEquals($expected, $content['labels']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_user_labels_if_not_logged(): void
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'labels',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_retrieve_single_label(): void
    {
        $label = $this->fixtures->getReference('label_essentials');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('findOneById')->with($label->getId())->willReturn($label);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'label',
            ['id' => $label->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('label', $content);

        $expected = [
            'id' => $label->getId(),
            'name' => $label->getName(),
            'color' => $label->getColor(),
        ];

        $this->assertEquals($expected, $content['label']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_label_if_not_current_user(): void
    {
        $label = $this->fixtures->getReference('label_essentials_2');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('findOneById')->with($label->getId())->willReturn($label);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'label',
            ['id' => $label->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_label_if_not_logged(): void
    {
        $label = $this->fixtures->getReference('label_essentials');

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'label',
            ['id' => $label->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_label_if_does_not_exist(): void
    {
        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('findOneById')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'label',
            ['id' => -1],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Label not found!', $response);
    }

    /**
     * @test
     */
    public function can_create_label(): void
    {
        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('save');
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(3))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'createLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('createLabel', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['createLabel']);
    }

    /**
     * @test
     */
    public function can_not_create_label_without_name(): void
    {
        $inputParams = [
            'name' => '',
            'color' => 'red'
        ];

        $this->mutation(
            'createLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Name should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_label_without_color(): void
    {
        $inputParams = [
            'name' => 'test',
            'color' => ''
        ];

        $this->mutation(
            'createLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Color should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_label_if_not_logged(): void
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'createLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_update_label(): void
    {
        $label = $this->fixtures->getReference('label_essentials');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->exactly(2))->method('find')->with($label->getId())->willReturn($label);
        $labelRepository->expects($this->once())->method('save');
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $label->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateLabel',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('updateLabel', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['updateLabel']);
    }

    /**
     * @test
     */
    public function can_not_update_label_if_does_not_exist(): void
    {
        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $inputParams = [
            'id' => -1,
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Label not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_label_if_not_possession_of_current_user(): void
    {
        $label = $this->fixtures->getReference('label_essentials_2');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->exactly(2))->method('find')->with($label->getId())->willReturn($label);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $label->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_label_if_not_logged(): void
    {
        $label = $this->fixtures->getReference('label_essentials');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('find')->with($label->getId())->willReturn($label);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $label->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_delete_label(): void
    {
        $label = $this->fixtures->getReference('label_essentials');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('find')->with($label->getId())->willReturn($label);
        $labelRepository->expects($this->once())->method('findOneById')->with($label->getId())->willReturn($label);
        $labelRepository->expects($this->once())->method('remove');
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $label->getId()
        ];

        $this->mutation(
            'deleteLabel',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('deleteLabel', $content);

        $expected = [
            'id' => $label->getId(),
            'name' => $label->getName(),
            'color' => $label->getColor()
        ];

        $this->assertEquals($expected, $content['deleteLabel']);
    }

    /**
     * @test
     */
    public function can_not_delete_label_if_does_not_exists(): void
    {
        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $inputParams = [
            'id' => -1
        ];

        $this->mutation(
            'deleteLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Label not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_label_if_not_possession_of_current_user(): void
    {
        $label = $this->fixtures->getReference('label_essentials_2');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('find')->with($label->getId())->willReturn($label);
        $labelRepository->expects($this->once())->method('findOneById')->with($label->getId())->willReturn($label);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $label->getId()
        ];

        $this->mutation(
            'deleteLabel',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_label_if_not_logged(): void
    {
        $label = $this->fixtures->getReference('label_essentials');

        $labelRepository = $this->createMock(LabelRepository::class);
        $labelRepository->expects($this->once())->method('find')->with($label->getId())->willReturn($label);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $label->getId()
        ];

        $this->mutation(
            'deleteLabel',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }
}
