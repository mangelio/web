<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "post" = {"security_post_denormalize" = "is_granted('FILTER_CREATE', object)", "denormalization_context"={"groups"={"filter-create"}}}
 *      },
 *     itemOperations={
 *      "get_issues"={
 *          "method"="GET",
 *          "security"="is_granted('FILTER_VIEW')",
 *          "path"="/filters/{id}/issues",
 *          "normalization_context"={"groups"={"issue-read"}, "skip_null_values"=false},
 *      }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends BaseEntity
{
    use IdTrait;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedUntil;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastAccess;

    /**
     * @var bool|null
     *
     * @Groups({"filter-create"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMarked;

    /**
     * @var ConstructionSite
     *
     * @Groups({"filter-create"})
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="filters")
     */
    private $constructionSite;

    public function getAccessAllowedUntil(): ?DateTime
    {
        return $this->accessAllowedUntil;
    }

    public function setAccessAllowedUntil(?DateTime $accessAllowedUntil): void
    {
        $this->accessAllowedUntil = $accessAllowedUntil;
    }

    public function getLastAccess(): ?DateTime
    {
        return $this->lastAccess;
    }

    public function setLastAccess(?DateTime $lastAccess): void
    {
        $this->lastAccess = $lastAccess;
    }

    public function getIsMarked(): ?bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(?bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }
}
