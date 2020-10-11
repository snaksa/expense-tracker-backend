<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\Category;
use App\Entity\Label;
use App\Exception\UnauthorizedOperationException;
use App\GraphQL\Input\Label\LabelRequest;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class LabelBuilder extends BaseBuilder
{
    private Label $label;
    private AuthorizationService $authorizationService;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;

        parent::__construct($entityManager);
    }

    public function create(): self
    {
        $this->label = new Label();

        $userId = $this->authorizationService->getCurrentUser()->getId();
        if ($userId) {
            $this->label->setUserId($userId);
            $this->label->setUser($this->authorizationService->getCurrentUser());
        }

        return $this;
    }

    /**
     * @param LabelRequest $input
     * @return LabelBuilder
     * @throws EntityNotFoundException
     */
    public function bind(LabelRequest $input): self
    {
        if ($input->id !== null) {
            $this->setLabel($this->findEntity($input->id, Label::class));
        }

        if ($this->label->getUserId() !== $this->authorizationService->getCurrentUser()->getId()) {
            throw new UnauthorizedOperationException();
        }

        if ($input->name !== null) {
            $this->withName($input->name);
        }

        if ($input->color !== null) {
            $this->withColor($input->color);
        }

        return $this;
    }

    public function setLabel(Label $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->label->setName($name);

        return $this;
    }

    public function withColor(string $color): self
    {
        $this->label->setColor($color);

        return $this;
    }

    public function build(): Label
    {
        return $this->label;
    }
}
